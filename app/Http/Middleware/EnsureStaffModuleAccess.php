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
    public function handle(Request $request, Closure $next, string $module): Response
    {
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
        $queryModule = normalizeStaffModuleKey((string) $request->query('module'));
        if ($queryModule === 'consultations' || $queryModule === 'certificates') {
            return $queryModule;
        }

        $documentType = (string) ($request->input('document_type') ?: $request->query('document_type'));
        if ($documentType !== '') {
            return $this->documentTypeToModule($documentType);
        }

        $routeDocument = $request->route('document_issuance');
        if ($routeDocument instanceof DocumentIssuance) {
            return $this->documentTypeToModule((string) $routeDocument->document_type);
        }

        if (is_numeric($routeDocument)) {
            $document = DocumentIssuance::query()->select(['id', 'document_type'])->find((int) $routeDocument);
            if ($document) {
                return $this->documentTypeToModule((string) $document->document_type);
            }
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

        if ($status === 'low_stock' || $tab === 'inventory' || $tab === 'logs') {
            return 'notifications';
        }

        return 'reports';
    }
}
