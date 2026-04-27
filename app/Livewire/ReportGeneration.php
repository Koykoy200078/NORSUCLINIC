<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PatientQueue;
use App\Models\UsedMedicine;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ReportGeneration extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $tab = 'logs';
    public $search = '';
    public $user_type = 'all';
    public $action = 'all';
    public $status = 'all';
    public $date_from = '';
    public $date_to = '';

    protected $queryString = [
        'tab' => ['except' => 'logs'],
        'search' => ['except' => ''],
        'user_type' => ['except' => 'all'],
        'action' => ['except' => 'all'],
        'status' => ['except' => 'all'],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount()
    {
        // Use request values if present
        $this->tab = request('tab', 'logs');
        $this->search = request('search', '');
        $this->user_type = request('user_type', 'all');
        $this->action = request('action', 'all');
        $this->status = request('status', 'all');
        $this->date_from = request('date_from', '');
        $this->date_to = request('date_to', '');
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'user_type', 'action', 'status', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $data = [];
        $actions = ActivityLog::select('action')->distinct()->whereNotIn('action', ['created_patient'])->pluck('action')->toArray();

        switch ($this->tab) {
            case 'logs':
                $query = ActivityLog::query()->with('user')->orderBy('created_at', 'desc');
                if ($this->user_type !== 'all') {
                    $query->where('user_type', $this->user_type);
                }
                if ($this->action !== 'all') {
                    $query->where('action', $this->action);
                }
                if ($this->date_from) $query->where('date', '>=', $this->date_from);
                if ($this->date_to) $query->where('date', '<=', $this->date_to);
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('patient_name', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%")
                            ->orWhere('user_name', 'like', "%{$this->search}%");
                    });
                }
                $data['activityLogs'] = $query->paginate(20);
                break;

            case 'visits':
                $query = DocumentIssuance::query()->with(['creator', 'consultationMedicines.medicine'])
                    ->where('document_type', 'consultation_form')
                    ->orderBy('created_at', 'desc');
                if ($this->date_from) $query->whereDate('created_at', '>=', $this->date_from);
                if ($this->date_to) $query->whereDate('created_at', '<=', $this->date_to);
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('complaints', 'like', "%{$this->search}%")
                            ->orWhere('assessment', 'like', "%{$this->search}%");
                    });
                }
                $data['reports'] = $query->paginate(20);
                break;

            case 'inventory':
                $query = Medicine::query()->with(['category', 'generic', 'batches'])->orderBy('name', 'asc');
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhereHas('category', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                            ->orWhereHas('generic', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"));
                    });
                }
                if ($this->status === 'low_stock') {
                    $query->whereRaw('available_quantity <= minimum_stock_alert');
                }
                $data['reports'] = $query->paginate(20);
                break;

            case 'dispensing':
                $query = UsedMedicine::query()->with(['medicine'])
                    ->orderBy('created_at', 'desc');
                if ($this->date_from) $query->whereDate('created_at', '>=', $this->date_from);
                if ($this->date_to) $query->whereDate('created_at', '<=', $this->date_to);
                if ($this->search) {
                    $query->whereHas('medicine', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                }
                $data['reports'] = $query->paginate(20);
                break;

            case 'appointments':
                $query = PatientQueue::query()->with(['patient.user', 'addedBy'])
                    ->whereNotNull('scheduled_at')
                    ->orderBy('scheduled_at', 'asc');
                if ($this->date_from) $query->whereDate('scheduled_at', '>=', $this->date_from);
                if ($this->date_to) $query->whereDate('scheduled_at', '<=', $this->date_to);
                if ($this->search) {
                    $query->whereHas('patient.user', fn($q) => $q->where('first_name', 'like', "%{$this->search}%")->orWhere('last_name', 'like', "%{$this->search}%"));
                }
                $data['reports'] = $query->paginate(20);
                break;

            case 'global_search':
                if ($this->search) {
                    $data['patients'] = Patient::with('user')->whereHas('user', fn($q) => $q->where('first_name', 'like', "%{$this->search}%")->orWhere('last_name', 'like', "%{$this->search}%"))
                        ->orWhere('patient_unique_id', 'like', "%{$this->search}%")
                        ->limit(10)->get();
                    $data['prescriptions'] = Prescription::with('patient.user')->whereHas('patient.user', fn($q) => $q->where('first_name', 'like', "%{$this->search}%")->orWhere('last_name', 'like', "%{$this->search}%"))->limit(10)->get();
                    $data['inventory'] = Medicine::where('name', 'like', "%{$this->search}%")->limit(10)->get();
                    $data['global_reports'] = ActivityLog::where('patient_name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")->limit(10)->get();
                }
                break;
        }

        return view('livewire.report-generation', array_merge($data, [
            'actions' => $actions
        ]));
    }
}
