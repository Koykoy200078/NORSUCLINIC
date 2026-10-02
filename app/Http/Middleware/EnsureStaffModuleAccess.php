<?php

namespace App\Http\Middleware;

use App\Models\DocumentIssuance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffModuleAccess
{
    /**
     * Enforce module-level access for staff/nurse users based on
     * staff designation + assigned station.
     */
    public function handle(Request $request, Closure $next, string ...$moduleParameters): Response
    {
        // "staff.module:consultations,certificates" reaches us as TWO parameters (Laravel splits middleware
        // parameters at the comma). With a single `string $module` the second one was silently dropped, so only the
        // first module was ever checked and staff who have just the second one were refused.
        $module = implode(',', $moduleParameters);

        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthenticated.');
        }

        if (! ($user->hasRole('staff') || $user->hasRole('nurse'))) {
            return $next($request);
        }

        if ($module === 'document_issuances') {
            $resolvedModule = $this->resolveDocumentIssuanceModule($request);

            abort_unless(
                canStaffAccessModule($resolvedModule, $user),
                403,
                'You are not allowed to access this request document module for your designation/station assignment.'
            );

            return $next($request);
        }

        if ($module === 'activity_logs') {
            $resolvedModule = $this->resolveActivityLogModule($request);

            abort_unless(
                canStaffAccessModule($resolvedModule, $user),
                403,
                'You are not allowed to access this activity-log view for your designation/station assignment.'
            );

            return $next($request);
        }

        $modules = array_values(array_filter(array_map('trim', explode(',', $module))));

        if (empty($modules)) {
            return $next($request);
        }

        foreach ($modules as $candidateModule) {
            if (canStaffAccessModule($candidateModule, $user)) {
                return $next($request);
            }
        }

        abort(403, 'You are not allowed to access this module for your designation/station assignment.');
    }

    private function resolveDocumentIssuanceModule(Request $request): string
    {
        // A request about an existing document (show / edit / update / destroy / export-pdf /
        // images) is authorised ONLY against the document's stored type. The ?module= query
        // and the document_type input are ignored here, otherwise e.g. a front-desk account
        // could open or edit a consultation by adding ?module=certificates.
        $routeDocument = $request->route('document_issuance');

        if ($routeDocument !== null) {
            if (! $routeDocument instanceof DocumentIssuance) {
                $routeDocument = is_numeric($routeDocument)
                    ? DocumentIssuance::query()->select(['id', 'document_type'])->find((int) $routeDocument)
                    : null;
            }

            // Unknown id: fall back to the most restrictive module; route model binding
            // answers 404 afterwards for users who are allowed that far.
            return $routeDocument
                ? $this->documentTypeToModule((string) $routeDocument->document_type)
                : 'consultations';
        }

        // Index / create / store / search: the module being opened or the type being created.
        $documentType = (string) ($request->input('document_type') ?: $request->query('document_type'));
        if ($documentType !== '') {
            return $this->documentTypeToModule($documentType);
        }

        $queryModule = normalizeStaffModuleKey((string) $request->query('module'));
        if ($queryModule === 'consultations' || $queryModule === 'certificates') {
            return $queryModule;
        }

        return 'consultations';
    }

    private function documentTypeToModule(string $documentType): string
    {
        return $documentType === 'consultation_form' ? 'consultations' : 'certificates';
    }

    private function resolveActivityLogModule(Request $request): string
    {
        $tab = (string) $request->query('tab');
        $status = (string) $request->query('status');
        $routeName = (string) $request->route()?->getName();

        // A single activity-log entry is raw log content, whatever the query string says.
        if (str_ends_with($routeName, 'activity-logs.show')) {
            return activityLogModuleForTab('logs');
        }

        // The CSV export falls back to the raw log when no tab is given (see ActivityLogController::export).
        if (str_ends_with($routeName, 'activity-logs.export') && $tab === '') {
            $tab = 'logs';
        }

        if ($status === 'low_stock') {
            return activityLogModuleForTab('inventory');
        }

        // The page itself (no tab) opens on a tab the user is allowed to see; ReportGeneration falls back
        // to the first permitted tab, so "reports" is enough to open it.
        return activityLogModuleForTab($tab);
    }
}
