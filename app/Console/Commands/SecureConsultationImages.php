<?php

namespace App\Console\Commands;

use App\Models\DocumentIssuance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-off migration for consultation photos uploaded before they were moved to private
 * storage. Legacy files live under public/uploads/consultation_images/<Patient_Name>/<time>/
 * with the uploader's original file name, so anyone on the network could download them
 * without logging in (and a non-image upload could even be executed by the web server).
 *
 * For every consultation this command:
 *  - moves each legacy picture to the private "consultation_images" disk under a random name
 *    and rewrites the record (also fixing the old double JSON encoding);
 *  - moves anything that is NOT a real picture to storage/app/consultation_images/_quarantine
 *    (outside the web root) and drops it from the record;
 *  - finally reports files still left under public/uploads/consultation_images that no record
 *    references, so they can be reviewed and removed by hand.
 *
 * Safe to run more than once. Use --dry-run first to see what would change.
 */
class SecureConsultationImages extends Command
{
    protected $signature = 'consultation-images:secure {--dry-run : Only report what would be moved}';

    protected $description = 'Move legacy consultation images out of public/uploads into private storage';

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('consultation_images');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $moved = 0;
        $quarantined = 0;
        $missing = 0;
        $recordsUpdated = 0;
        $referencedLegacyFiles = [];

        DocumentIssuance::query()
            ->whereNotNull('consultation_images')
            ->orderBy('id')
            ->chunkById(100, function ($documents) use ($dryRun, $disk, $finfo, &$moved, &$quarantined, &$missing, &$recordsUpdated, &$referencedLegacyFiles) {
                foreach ($documents as $document) {
                    $rawValue = $document->getRawOriginal('consultation_images');
                    $images = $document->consultationImageList();
                    $changed = is_string($document->consultation_images); // double-encoded row
                    $kept = [];

                    foreach ($images as $image) {
                        if (($image['disk'] ?? null) === 'consultation_images') {
                            $kept[] = $image;
                            continue;
                        }

                        $legacyPath = $document->consultationImageAbsolutePath($image);
                        if ($legacyPath === null) {
                            // File already gone (or path escapes the folder): keep the entry so
                            // the history page still shows "Missing: <name>".
                            $missing++;
                            $kept[] = $image;
                            continue;
                        }

                        $referencedLegacyFiles[$legacyPath] = true;
                        $mimeType = $finfo->file($legacyPath) ?: '';
                        $changed = true;

                        if (! isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
                            $quarantined++;
                            $this->warn("Document #{$document->id}: quarantining non-image file ({$mimeType}) {$image['path']}");

                            if (! $dryRun) {
                                $target = '_quarantine/' . $document->id . '/' . Str::random(40) . '.bin';
                                if (! $disk->put($target, file_get_contents($legacyPath))) {
                                    $this->error("Document #{$document->id}: could not quarantine {$image['path']}; left in place.");
                                    $kept[] = $image;
                                    continue;
                                }
                                @unlink($legacyPath);
                            }

                            continue;
                        }

                        $target = $document->id . '/' . Str::random(40) . '.' . self::ALLOWED_MIME_TYPES[$mimeType];

                        if (! $dryRun) {
                            // Copy first; only delete the public copy once the private one exists.
                            if (! $disk->put($target, file_get_contents($legacyPath))) {
                                $this->error("Document #{$document->id}: could not copy {$image['path']}; left in place.");
                                $kept[] = $image;
                                continue;
                            }
                            @unlink($legacyPath);
                        }

                        $moved++;

                        $kept[] = array_merge($image, [
                            'disk' => 'consultation_images',
                            'path' => $target,
                        ]);
                    }

                    if ($changed) {
                        $recordsUpdated++;

                        if (! $dryRun) {
                            // Assign the array directly; the model's "array" cast encodes it once.
                            $document->consultation_images = ! empty($kept) ? array_values($kept) : null;
                            $document->saveQuietly();
                        } elseif ($rawValue !== null) {
                            $this->line("Document #{$document->id}: would rewrite image list (" . count($kept) . ' kept)');
                        }
                    }
                }
            });

        $this->reportUnreferencedLegacyFiles($referencedLegacyFiles, $dryRun);

        $this->info(($dryRun ? '[dry run] ' : '') . "Images moved to private storage: {$moved}");
        $this->info(($dryRun ? '[dry run] ' : '') . "Non-image files quarantined: {$quarantined}");
        $this->info("Entries whose file was already missing: {$missing}");
        $this->info(($dryRun ? '[dry run] ' : '') . "Consultation records rewritten: {$recordsUpdated}");

        return self::SUCCESS;
    }

    private function reportUnreferencedLegacyFiles(array $referencedLegacyFiles, bool $dryRun): void
    {
        $legacyRoot = public_path('uploads/consultation_images');
        if (! is_dir($legacyRoot)) {
            return;
        }

        $leftovers = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($legacyRoot, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $realPath = $file->getRealPath();
                // In a real run the referenced files were already moved away.
                if ($dryRun && isset($referencedLegacyFiles[$realPath])) {
                    continue;
                }
                $leftovers[] = $realPath;
            }
        }

        if (empty($leftovers)) {
            return;
        }

        $this->warn(count($leftovers) . ' file(s) under public/uploads/consultation_images are not referenced by any consultation.');
        $this->warn('They are still reachable from the web server; review and delete them (or move them out of public/):');
        foreach (array_slice($leftovers, 0, 50) as $leftover) {
            $this->line('  ' . $leftover);
        }
        if (count($leftovers) > 50) {
            $this->line('  ... and ' . (count($leftovers) - 50) . ' more');
        }
    }
}
