<?php

namespace App\Http\Controllers;

use App\Http\Requests\MergePatientRequest;
use App\Models\ActivityLog;
use App\Models\Patient;
use App\Repositories\PatientMergeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PatientMergeController extends Controller
{
    public function __construct(private PatientMergeRepository $merges)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['source_id' => ['nullable', 'integer', 'min:1'], 'target_id' => ['nullable', 'integer', 'min:1'],
            'archive_id' => ['nullable', 'integer', 'min:1']]);
        $ready = PatientMergeRepository::ready();
        $records = Patient::with('user')->whereHas('user', fn ($q) => $q->where('type', \App\Models\User::PATIENT)->where('status', 1))->orderBy('id')->get();
        $candidates = $this->merges->candidates();
        $preview = null;
        $previewError = null;
        if ($ready && ! empty($data['source_id']) && ! empty($data['target_id'])) {
            try {
                $preview = $this->merges->preview((int) $data['source_id'], (int) $data['target_id'], $request->user());
            } catch (ValidationException $error) {
                if ($request->expectsJson()) {
                    throw $error;
                }
                $previewError = collect($error->errors())->flatten()->first();
            }
        }
        $archive = null;
        $archiveAddresses = collect();
        $archiveMedia = collect();
        if ($ready && ! empty($data['archive_id'])) {
            $archive = Patient::withTrashed()->whereNotNull('merged_into_id')->with(['user' => fn ($q) => $q->withTrashed()])->findOrFail($data['archive_id']);
            $archiveAddresses = \App\Models\Address::with(['barangay', 'city', 'state'])->where('merged_from_patient_id', $archive->id)->get();
            $archiveMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('custom_properties->merged_from_patient_id', $archive->id)->get();
        }
        $history = ActivityLog::where('action', 'patient_merged')->latest('id')->limit(20)->get();

        return view('patients.merge', compact('ready', 'records', 'candidates', 'preview', 'previewError', 'history', 'archive', 'archiveAddresses', 'archiveMedia'));
    }

    public function store(MergePatientRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        try {
            $this->merges->merge((int) $data['source_id'], (int) $data['target_id'], $data['preview_token'], $request->user());
        } catch (\RuntimeException $error) {
            Log::warning('Patient merge rolled back', ['error_type' => get_class($error)]);
            throw ValidationException::withMessages(['preview_token' => 'The merge could not be completed. No records were changed. Preview it again.']);
        }
        $redirect = route('patient-merge.index', ['archive_id' => $data['source_id']]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => $redirect]);
        }

        return redirect($redirect)->with('success', 'Patient history moved. The duplicate was archived; review its preserved details below.');
    }
}
