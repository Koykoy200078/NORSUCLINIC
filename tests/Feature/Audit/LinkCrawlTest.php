<?php

namespace Tests\Feature\Audit;

use App\Models\ConsultationMedicine;
use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\DocumentIssuance;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * LINK AUDIT - "does every link / button I am shown actually open for me?"
 *
 * Signs in as guest, administrator, doctor and Staff (Nurse) - the last two also with each clinical permission taken
 * away in Manage User roles (and the doctor with manage_doctors added) -
 * opens the landing page and every no-parameter page of the user's own panel, loads the lazy Livewire tables
 * the way the browser does, then follows every <a href> it finds (two levels deep, so show / edit pages reached from
 * the list rows are covered) as the SAME user. A link that answers 403 / 404 / 405 / 419, or a page that crashes (5xx),
 * is a defect: a button that is shown but cannot be used, or a link that is hard-wired to another role's URL.
 *
 * It is slow (about 5-8 minutes), so it is skipped unless asked for:
 *
 *     PowerShell:  $env:LINK_CRAWL = '1'; vendor/bin/phpunit --filter LinkCrawlTest; Remove-Item Env:LINK_CRAWL
 *     bash:        LINK_CRAWL=1 vendor/bin/phpunit --filter LinkCrawlTest
 *
 * Optional: CRAWL_OUT=<file.json> also writes the full per-user report (pages visited, external links, ...).
 *
 * What it cannot see: links created by JavaScript after load (JsRender templates, wire:click handlers), POST-only
 * buttons, rows that no fixture creates (add a fixture below when a new screen gets row-level links), and pages that
 * need a parameter nobody links to. Run it after touching any menu, list, or "back / cancel / view / edit" link.
 */
class LinkCrawlTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** Pages followed per user - a safety stop, reported when it is reached. */
    private const PAGE_CAP = 700;

    private int $requests = 0;

    public function test_every_link_shown_to_every_kind_of_user_opens(): void
    {
        if (! getenv('LINK_CRAWL')) {
            $this->markTestSkipped('Slow audit - run it with LINK_CRAWL=1 (see the class comment).');
        }

        set_time_limit(0);
        ini_set('memory_limit', '3G');

        // The REAL seed set, so settings / CMS / illness lists exist like in production.
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $actors = $this->buildActorsAndRows();

        $report = [];
        $problems = [];
        // What the administrator can change in Manage User roles: the permissions of the Staff (Nurse) and Doctor roles.
        // Each actor is crawled with the role set up as its label says; the originals are put back at the end.
        $originals = [];
        foreach (['staff', 'doctor'] as $roleName) {
            $originals[$roleName] = \App\Models\Role::findByName($roleName)->permissions()->pluck('name')->all();
        }
        foreach ($actors as $label => $user) {
            $roleName = $user?->hasRole('staff') ? 'staff' : ($user?->hasRole('doctor') ? 'doctor' : null);
            if ($roleName) {
                $permissions = $originals[$roleName];
                if (preg_match('/^(?:staff|doctor) without (.+)$/', $label, $match)) {
                    $permissions = array_values(array_diff($permissions, [$match[1]]));
                } elseif ($label === 'doctor with manage_doctors') {
                    $permissions[] = 'manage_doctors';
                }
                \App\Models\Role::findByName($roleName)->syncPermissions($permissions);
            }
            $result = $this->crawl($user?->fresh());
            $report[$label] = $result;

            foreach ($result['broken'] as $b) {
                $problems[] = "[{$label}] {$b['status']} {$b['url']}  <-  {$b['from']}" . ($b['text'] !== '' ? "  (\"{$b['text']}\")" : '');
            }
            foreach ($result['crashed'] as $c) {
                $problems[] = "[{$label}] CRASH {$c['status']} {$c['url']}  <-  {$c['from']}  {$c['error']}";
            }
            foreach ($result['lazy_failed'] as $url) {
                $problems[] = "[{$label}] a lazy table failed to load on {$url}";
            }
            if ($result['capped']) {
                $problems[] = "[{$label}] stopped at the " . self::PAGE_CAP . '-page safety cap: raise PAGE_CAP or the audit is incomplete';
            }
        }

        foreach ($originals as $roleName => $permissions) {
            \App\Models\Role::findByName($roleName)->syncPermissions($permissions);
        }
        if ($out = getenv('CRAWL_OUT')) {
            file_put_contents($out, json_encode(['requests' => $this->requests, 'actors' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        $this->assertSame([], $problems, "Links that do not open for the user they are shown to:\n" . implode("\n", $problems));
    }

    // ---- fixtures ----------------------------------------------------------------------------------------------

    /** @return array<string, ?User> */
    private function buildActorsAndRows(): array
    {
        $admin = $this->makeAdmin(['email' => 'crawl.admin@test.local']);
        $doctor = $this->makeDoctor(['email' => 'crawl.doctor@test.local']);
        $patient = $this->makePatient(['email' => 'crawl.patient@test.local']);
        $queued = $this->makePatient(['email' => 'crawl.patient2@test.local']);

        // One row of everything that has a list with show / edit links, so those links exist to be followed.
        $medicine = $this->makeMedicine('Crawlmed');
        $this->stockIn($medicine, 100, now()->addYear()->toDateString());

        foreach (['consultation_form', 'medical_certificate', 'excuse_slip'] as $type) {
            DocumentIssuance::create([
                'document_type' => $type, 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
                'name' => 'Crawl Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
                'requested_at' => now()->toDateString(), 'consult_mode' => 'physical', 'informant' => 'Student',
                'complaints' => 'cough', 'assessment' => 'URTI', 'plan' => 'rest',
            ]);
        }

        // A consultation with a medicine line, so the Dispense History has a "Consultation" row (and its View link).
        ConsultationMedicine::create([
            'request_document_id' => DocumentIssuance::where('document_type', 'consultation_form')->value('id'),
            'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 1, 'used_for' => 'plan',
        ]);

        $prescription = Prescription::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => 'pending', 'is_active' => 1]);
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id,
            'model_type' => Prescription::class, 'model_id' => $prescription->id, 'bill_date' => now(),
        ]);
        DispenseRecordItem::create(['dispense_id' => $record->id, 'medicine_id' => $medicine->id, 'quantity' => 1]);

        DB::table('lab_requests')->insert([
            'request_number' => '000001', 'document_creator_id' => $doctor->id, 'patient_user_id' => $patient->user_id,
            'patient_name' => 'Crawl Patient', 'requested_at' => now()->toDateString(), 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        PatientQueue::create(['patient_id' => $queued->id, 'added_by' => $admin->id, 'status' => PatientQueue::STATUS_WAITING, 'scheduled_at' => now()]);

        // A second queued patient whose consultation form is already recorded: the queue screens then offer "View Form" /
        // "Has form" (the first one gets "Record form"), so both kinds of consultation buttons are on a page to be followed.
        $queuedWithForm = $this->makePatient(['email' => 'crawl.patient3@test.local']);
        DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $queuedWithForm->user_id,
            'name' => 'Crawl Queued', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(), 'consult_mode' => 'physical', 'complaints' => 'fever',
        ]);
        PatientQueue::create(['patient_id' => $queuedWithForm->id, 'added_by' => $admin->id, 'status' => PatientQueue::STATUS_WAITING, 'scheduled_at' => now()]);
        PatientQueue::syncAttachment((int) $queuedWithForm->user_id);

        $actors = ['guest' => null, 'admin' => $admin, 'doctor' => $doctor];
        // The doctor as the administrator can leave the role: the default, one more permission, each default one removed.
        $actors['doctor with manage_doctors'] = $this->makeDoctor();
        foreach (['manage_patients', 'manage_request_documents', 'manage_medicines', 'manage_specialties'] as $permission) {
            $actors['doctor without '.$permission] = $this->makeDoctor();
        }

        $actors['staff default'] = $this->makeNurse();
        // An account from before Phase 3 still has the hidden designation / station / shift columns: they change nothing.
        $actors['staff with a legacy profile'] = $this->makeStaff();
        foreach (\App\Support\ModuleAccess::CLINICAL_PERMISSIONS as $permission) {
            $actors['staff without '.$permission] = $this->makeNurse();
        }

        return $actors;
    }

    // ---- the crawl ---------------------------------------------------------------------------------------------

    /** @return array{pages:int, broken:array, crashed:array, external:array, lazy_failed:array, capped:bool} */
    private function crawl(?User $user): array
    {
        $seen = [];
        $texts = [];
        $queue = [];
        foreach ($this->seedsFor($user) as $seed) {
            $queue[] = [$seed, 0, '(start page)'];
        }

        $result = ['pages' => 0, 'broken' => [], 'crashed' => [], 'external' => [], 'lazy_failed' => [], 'capped' => false];

        while ($queue) {
            if (count($seen) >= self::PAGE_CAP) {
                $result['capped'] = true;
                break;
            }

            [$url, $depth, $from] = array_shift($queue);
            if (isset($seen[$url])) {
                if ($from !== '(start page)' && in_array($seen[$url], [403, 404, 405, 419], true)) {
                    $result['broken'][] = ['url' => $url, 'from' => $from, 'status' => $seen[$url], 'final' => $url, 'text' => $texts[$url] ?? ''];
                }
                continue;
            }
            if (str_starts_with($url, 'EXTERNAL:')) {
                $result['external'][$url . ' <- ' . $from] = true;
                continue;
            }
            if ($this->skip($url)) {
                continue;
            }
            $r = $this->fetch($user, $url);
            $seen[$url] = $r['status'];
            $result['pages']++;
            if (str_contains($r['body'], 'LAZY-FAILED')) {
                $result['lazy_failed'][] = $url;
            }

            if ($r['status'] >= 500) {
                $result['crashed'][] = ['url' => $url, 'from' => $from, 'status' => $r['status'], 'error' => (string) $r['error']];
                continue;
            }

            // A start page that a user's policy refuses is the policy working; a LINK to a refused page is the defect.
            $isStartPage = $from === '(start page)';
            if (! $isStartPage && in_array($r['status'], [403, 404, 405, 419], true)) {
                $result['broken'][] = ['url' => $url, 'from' => $from, 'status' => $r['status'], 'final' => $r['final'], 'text' => $texts[$url] ?? ''];
                continue;
            }

            if ($r['status'] === 200 && $r['body'] !== '' && $depth < 2) {
                $base = parse_url($r['final'], PHP_URL_PATH) ?: '/';
                foreach ($this->links($r['body'], $base) as $link => $text) {
                    $texts[$link] ??= $text;
                    if (! isset($seen[$link]) || in_array($seen[$link], [403, 404, 405, 419], true)) {
                        $queue[] = [$link, $depth + 1, $url];
                    }
                }
            }

            if (($this->requests % 200) === 0) {
                gc_collect_cycles();
            }
        }

        $result['external'] = array_keys($result['external']);

        return $result;
    }

    /** Start pages: the landing page, the user's dashboard, and every parameter-free GET page of the user's own panel. */
    private function seedsFor(?User $user): array
    {
        $seeds = ['/'];
        $prefixes = [];

        if ($user) {
            if ($user->hasRole('clinic_admin')) {
                $prefixes = ['admin/'];
            } elseif ($user->hasRole('doctor')) {
                $prefixes = ['doctors/'];
            } elseif ($user->hasRole('staff') || $user->hasRole('nurse')) {
                $prefixes = ['staff/'];
            }
            $dashboard = getDashboardRouteName($user);
            $seeds[] = $dashboard ? (string) parse_url(route($dashboard), PHP_URL_PATH) : '/';
        } else {
            $seeds[] = '/login';
        }

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! in_array('GET', $route->methods(), true) || str_contains($uri, '{')
                || str_starts_with($uri, 'api/') || str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum')) {
                continue;
            }

            $own = false;
            foreach ($prefixes as $prefix) {
                $own = $own || str_starts_with($uri, $prefix);
            }
            $isPublic = ! preg_match('#^(admin|staff|doctors|patients)/#', $uri);

            if ($own || (! $user && $isPublic)) {
                $seeds[] = '/' . ltrim($uri, '/');
            }
        }

        return array_values(array_unique(array_filter($seeds)));
    }

    /** Things that are not pages to judge: assets, and links that act when opened (sign-out, impersonation, ...). */
    private function skip(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        if (preg_match('#\.(css|js|map|png|jpe?g|gif|svg|ico|webp|woff2?|ttf|eot|otf|zip|sql)$#i', $path)) {
            return true;
        }
        if (preg_match('#^/(css|js|images|img|fonts|assets|vendor|storage|build|webfonts|mix-manifest|favicon)#i', $path)) {
            return true;
        }

        return (bool) preg_match('#(logout|impersonate|update-dark-mode|change-language|csrf-token|backups?(/|$))#i', $path);
    }

    private function normalise(string $href, string $basePath): ?string
    {
        $href = trim(html_entity_decode($href));
        // client-side template placeholders ({{:url}} of JsRender) are not links
        if ($href === '' || $href[0] === '#' || preg_match('#^(javascript|mailto|tel|data|blob):#i', $href) || str_contains($href, '{{') || str_contains($href, '<%')) {
            return null;
        }

        $parts = parse_url($href);
        if ($parts === false) {
            return null;
        }
        if (isset($parts['host'])) {
            $ownHosts = [parse_url((string) config('app.url'), PHP_URL_HOST), 'localhost', '127.0.0.1'];
            if (! in_array($parts['host'], $ownHosts, true)) {
                return 'EXTERNAL:' . $href;
            }
        }

        $path = ($parts['path'] ?? '') === '' ? '/' : $parts['path'];
        if ($path[0] !== '/') {
            $path = rtrim(dirname($basePath), '/') . '/' . $path;
        }
        $query = isset($parts['query']) ? '?' . preg_replace('/(^|&)_token=[^&]*/', '', $parts['query']) : '';

        return $path . ($query === '?' ? '' : $query);
    }

    /** @return array{status:int, final:string, hops:int, body:string, error:?string} */
    private function fetch(?User $user, string $url): array
    {
        set_time_limit(0);
        $this->requests++;
        $hops = 0;
        $current = $url;

        while (true) {
            if ($user) {
                $this->actingAs($user);
            } else {
                Auth::guard()->forgetUser();
            }

            $response = $this->get($current);
            $status = $response->getStatusCode();

            $error = null;
            if ($status >= 500) {
                $exception = $response->baseResponse->exception ?? null;
                $error = $exception ? get_class($exception) . ': ' . mb_substr($exception->getMessage(), 0, 220) : null;
            }

            if ($status >= 300 && $status < 400 && $hops < 4) {
                $next = $this->normalise((string) $response->headers->get('Location'), parse_url($current, PHP_URL_PATH) ?: '/');
                if (! $next || str_starts_with($next, 'EXTERNAL:')) {
                    break;
                }
                $current = $next;
                $hops++;
                continue;
            }

            $isHtml = str_contains((string) $response->headers->get('Content-Type'), 'text/html');
            $body = ($isHtml && $status === 200) ? (string) $response->getContent() : '';
            if ($body !== '') {
                // lazy Livewire tables are only a placeholder in the page: load them like the browser does
                $body .= $this->lazyHtml($body, 0);
            }

            return ['status' => $status, 'final' => $current, 'hops' => $hops, 'body' => $body, 'error' => $error];
        }

        return ['status' => $status, 'final' => $current, 'hops' => $hops, 'body' => '', 'error' => 'redirect chain too long'];
    }

    /** Replays the browser's `__lazyLoad` call for every lazy Livewire placeholder in $html; returns the rendered HTML. */
    private function lazyHtml(string $html, int $depth): string
    {
        if ($depth > 2 || ! preg_match_all('/<[^<>]*__lazyLoad[^<>]*>/s', $html, $tags)) {
            return '';
        }

        $extra = '';
        foreach ($tags[0] as $tag) {
            if (! preg_match('/wire:snapshot="([^"]*)"/', $tag, $snapshot)
                || ! preg_match("/__lazyLoad\\((?:&#039;|')([^&']*)(?:&#039;|')\\)/", $tag, $param)) {
                continue;
            }

            set_time_limit(0);
            $this->requests++;
            $response = $this->postJson('/livewire/update', [
                'components' => [[
                    'snapshot' => html_entity_decode($snapshot[1]),
                    'updates' => (object) [],
                    'calls' => [['path' => '', 'method' => '__lazyLoad', 'params' => [$param[1]]]],
                ]],
            ], ['X-Livewire' => 'true']);

            if ($response->getStatusCode() !== 200) {
                $extra .= '<!-- LAZY-FAILED status ' . $response->getStatusCode() . ' -->';
                continue;
            }

            foreach (($response->json('components') ?? []) as $component) {
                $rendered = (string) ($component['effects']['html'] ?? '');
                $extra .= $rendered . $this->lazyHtml($rendered, $depth + 1);
            }
        }

        return $extra;
    }

    /** @return array<string,string> normalised link => visible text (or title) of its first anchor */
    private function links(string $html, string $basePath): array
    {
        $out = [];
        if (preg_match_all('/<a\b([^>]*?)\bhref\s*=\s*(["\'])(.*?)\2([^>]*)>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $link = $this->normalise($m[3], $basePath);
                if ($link === null || isset($out[$link])) {
                    continue;
                }

                $text = trim(preg_replace('/\s+/', ' ', strip_tags($m[5])));
                if ($text === '' && preg_match('/title\s*=\s*"([^"]*)"/i', $m[1] . $m[4], $title)) {
                    $text = 'title:' . $title[1];
                }
                $out[$link] = mb_substr($text, 0, 60);
            }
        }

        return $out;
    }
}
