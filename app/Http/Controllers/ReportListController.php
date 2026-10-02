<?php

namespace App\Http\Controllers;

use App\Models\Illness;
use App\Models\IllnessSystem;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laracasts\Flash\Flash;

/**
 * Settings > Report lists: the illnesses (by body system) and services that nurses pick on a consultation and that
 * the ACCOMPLISHMENT REPORT counts. The administrator can rename a line, move it under another group heading, add a
 * new one, or switch one off. A line is never deleted: consultations already point at it, and a switched-off line
 * keeps its history in the report but is no longer offered on the form.
 */
class ReportListController extends Controller
{
    public function index()
    {
        return view('setting.report-lists', [
            'sectionName' => 'report-lists',
            'systems' => IllnessSystem::ordered()->with('illnesses')->get(),
            'serviceGroups' => ServiceType::ordered()->get()->groupBy('category'),
        ]);
    }

    public function storeIllness(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'illness_system_id' => 'required|integer|exists:illness_systems,id',
            'group_label' => 'nullable|string|max:120',
            'name' => ['required', 'string', 'max:150', Rule::unique('illnesses', 'name')->where('illness_system_id', $request->input('illness_system_id'))],
        ]);

        Illness::create($data + [
            'is_other' => false,
            'is_active' => true,
            // Before the system's "Others" line, which stays last.
            'sort_order' => ((int) Illness::where('illness_system_id', $data['illness_system_id'])->where('is_other', false)->max('sort_order')) + 1,
        ]);

        Flash::success('Illness added to the list.');

        return back();
    }

    public function updateIllness(Request $request, Illness $illness): RedirectResponse
    {
        $data = $request->validate([
            'group_label' => 'nullable|string|max:120',
            'name' => ['required', 'string', 'max:150', Rule::unique('illnesses', 'name')->where('illness_system_id', $illness->illness_system_id)->ignore($illness->id)],
        ]);

        $illness->update([
            // The "Others" line keeps its name: the report and the form recognise it by that.
            'name' => $illness->is_other ? $illness->name : $data['name'],
            'group_label' => filled($data['group_label'] ?? null) ? trim($data['group_label']) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        Flash::success('Illness saved.');

        return back();
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(ServiceType::CATEGORY_LABELS))],
            'name' => ['required', 'string', 'max:150', Rule::unique('service_types', 'name')->where('category', $request->input('category'))],
        ]);

        ServiceType::create($data + [
            'auto_rule' => null,
            'is_other' => false,
            'is_active' => true,
            'sort_order' => ((int) ServiceType::where('category', $data['category'])->where('is_other', false)->max('sort_order')) + 1,
        ]);

        Flash::success('Service added to the list.');

        return back();
    }

    public function updateService(Request $request, ServiceType $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('service_types', 'name')->where('category', $service->category)->ignore($service->id)],
        ]);

        $service->update([
            // A service the system recognises by itself (vital signs, medicine given...) keeps its name and rule.
            'name' => ($service->is_other || $service->auto_rule) ? $service->name : $data['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        Flash::success('Service saved.');

        return back();
    }
}
