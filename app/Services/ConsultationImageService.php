<?php

namespace App\Services;

use App\Models\DocumentIssuance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConsultationImageService
{
    /** Paths written by this request, removed if its database transaction rolls back. */
    private array $imagesStoredThisRequest = [];

    /**
     * Handle image uploads and removals for consultation form updates
     */
    public function update(DocumentIssuance $requestDocument, array $data): void
    {
        $existingImages = $requestDocument->consultationImageList();
        $removedImages = [];

        // Handle removed images (indices refer to the list rendered on the edit page)
        if (! empty($data['removed_images'])) {
            $removedIndices = json_decode((string) $data['removed_images'], true);

            if (is_array($removedIndices)) {
                foreach ($removedIndices as $index) {
                    if (is_numeric($index) && isset($existingImages[(int) $index])) {
                        $removedImages[] = $existingImages[(int) $index];
                        unset($existingImages[(int) $index]);
                    }
                }

                $existingImages = array_values($existingImages);
            }
        }

        foreach ($this->store($requestDocument) as $uploadedImage) {
            $existingImages[] = $uploadedImage;
        }

        // Assign the array directly: the model's "array" cast does the JSON encoding.
        $requestDocument->consultation_images = ! empty($existingImages) ? $existingImages : null;
        $requestDocument->save();

        // Only delete removed files once the update is committed, so a rollback never leaves
        // the record pointing at files that no longer exist.
        if (! empty($removedImages)) {
            DB::afterCommit(function () use ($requestDocument, $removedImages) {
                foreach ($removedImages as $removedImage) {
                    $requestDocument->deleteConsultationImageFile($removedImage);
                }
            });
        }
    }

    /**
     * Store the uploaded consultation_images[] files (already validated as real pictures of
     * at most 5MB) on the private consultation_images disk under a random file name.
     *
     * @return array<int, array{disk: string, path: string, name: string, size: int, uploaded_at: string}>
     */
    public function store(DocumentIssuance $requestDocument): array
    {
        $files = request()->file('consultation_images');
        if (empty($files)) {
            return [];
        }

        $files = is_array($files) ? $files : [$files];
        $storedImages = [];

        foreach ($files as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $fileSize = (int) $image->getSize();
            $storedPath = $image->store((string) $requestDocument->id, 'consultation_images');

            if ($storedPath === false) {
                throw new \RuntimeException('A consultation image could not be saved. Please try again.');
            }

            $this->imagesStoredThisRequest[] = $storedPath;

            $storedImages[] = [
                'disk' => 'consultation_images',
                'path' => $storedPath,
                'name' => $this->sanitizeImageDisplayName($image->getClientOriginalName()),
                'size' => $fileSize,
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        return $storedImages;
    }

    /**
     * The original file name is only kept as a label; it is never used as a path.
     */
    private function sanitizeImageDisplayName(?string $clientName): string
    {
        $name = basename(str_replace('\\', '/', (string) $clientName));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?? '';
        $name = trim($name);

        return $name === '' ? 'image' : mb_substr($name, 0, 150);
    }

    /**
     * Remove files stored during a request whose transaction was rolled back.
     */
    public function discardStoredFiles(): void
    {
        foreach ($this->imagesStoredThisRequest as $storedPath) {
            Storage::disk('consultation_images')->delete($storedPath);
        }

        $this->imagesStoredThisRequest = [];
    }
}
