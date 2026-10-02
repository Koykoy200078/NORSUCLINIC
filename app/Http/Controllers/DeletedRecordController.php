<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Services\DeletedRecordRestorer;
use Illuminate\Http\RedirectResponse;
use Laracasts\Flash\Flash;

/**
 * Settings > Deleted records (administrator): the consultations, certificates, excuse slips and lab requests that
 * were deleted from a screen. They are only hidden (deleted_at), so the administrator can see who deleted them and
 * bring them back. R3-M6.
 */
class DeletedRecordController extends Controller
{
    public function index()
    {
        $documents = DocumentIssuance::onlyTrashed()->orderByDesc('deleted_at')->orderByDesc('id')->paginate(25, ['*'], 'documents');
        $labRequests = LabRequest::onlyTrashed()->orderByDesc('deleted_at')->orderByDesc('id')->paginate(25, ['*'], 'lab_requests');

        return view('setting.deleted-records', [
            'sectionName' => 'deleted-records',
            'documents' => $documents,
            'labRequests' => $labRequests,
            'documentDeletions' => $this->deletions('document_deleted', 'RequestDocuments', $documents->pluck('id')->all()),
            'labDeletions' => $this->deletions('lab_request_deleted', 'LabRequest', $labRequests->pluck('id')->all()),
        ]);
    }

    public function restore(DeletedRecordRestorer $restorer, string $type, int $id): RedirectResponse
    {
        try {
            $message = $type === 'lab-request' ? $restorer->restoreLabRequest($id) : $restorer->restoreDocument($id);
            Flash::success($message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Flash::error('That record is not in the deleted list any more.');
        } catch (\Throwable $e) {
            report($e);
            Flash::error($this->userFacingErrorMessage($e, 'The record could not be restored.'));
        }

        return redirect()->route('deleted-records.index');
    }

    /**
     * Who deleted each record and when, from the audit snapshot written at deletion time.
     *
     * @param  array<int, int>  $ids
     * @return \Illuminate\Support\Collection<int, ActivityLog>  keyed by record id (the latest deletion)
     */
    private function deletions(string $action, string $subjectType, array $ids)
    {
        if ($ids === []) {
            return collect();
        }

        return ActivityLog::where('action', $action)
            ->where('subject_type', $subjectType)
            ->whereIn('subject_id', $ids)
            ->orderBy('id')
            ->get()
            ->keyBy('subject_id');
    }
}
