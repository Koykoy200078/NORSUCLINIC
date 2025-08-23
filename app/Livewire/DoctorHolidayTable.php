<?php

namespace App\Livewire;

use App\Models\DoctorHoliday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorHolidayTable extends LivewireTableComponent
{
    protected $model = DoctorHoliday::class;

    public bool $showButtonOnHeader = true;

    protected string $tableName = 'holidays';

    public string $buttonComponent = 'doctor_holiday.components.add_button';

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['doctor_holiday.components.filter', []];

    protected $listeners = ['refresh' => '$refresh', 'resetPage', 'changeDateFilter'];

    public string $dateFilter = '';

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);
    }

    /**
     * @var string[]
     */
    public function changeDateFilter($date)
    {
        $this->dateFilter = $date;
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function placeholder()
    {
        return view('livewire.doctor_holiday_skeleton');
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.visit.doctor'), 'doctor.doctorUser.first_name')
                ->view('doctor_holiday.components.doctor')
                ->sortable()
                ->searchable(
                    function (Builder $query, $direction) {
                        return $query->whereHas('doctor.doctorUser', function (Builder $q) use ($direction) {
                            $q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
                        });
                    }
                ),
            Column::make(__('messages.visit.doctor'), 'doctor.doctorUser.email')
                ->hideIf('doctor.doctorUser.email')
                ->searchable(),
            Column::make(__('messages.web.reason'), 'name')->view('doctor_holiday.components.reason')
                ->sortable(),
            Column::make(__('messages.holiday.holiday_date'), 'date')->view('doctor_holiday.components.holiday_date')
                ->sortable(),
            Column::make(__('messages.common.action'), 'id')->view('doctor_holiday.components.action'),
        ];
    }

    public function builder(): Builder
    {
        $query = DoctorHoliday::with('doctor.doctorUser')->select('doctor_holidays.*');

        // If user is a doctor, only show their own holidays
        $user = getLoginUser();
        if ($user && $user->hasRole('doctor')) {
            // Check if the user has a doctor relationship
            if ($user->doctor) {
                $query->where('doctor_id', $user->doctor->id);
            } else {
                // If doctor relationship doesn't exist, return no results
                $query->whereRaw('1 = 0');
            }
        }
        // For admin/staff users, show all holidays (no additional filtering)

        // Only apply a date range filter when an explicit dateFilter is provided.
        // If dateFilter is empty, return all holidays.
        if ($this->dateFilter !== '') {
            // Expecting date range like "d/m/Y - d/m/Y"
            if (str_contains($this->dateFilter, ' - ')) {
                $timeEntryDate = explode(' - ', $this->dateFilter);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($timeEntryDate[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($timeEntryDate[1]))->format('Y-m-d');
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // If a single date is provided, filter that exact date
                try {
                    $singleDate = Carbon::createFromFormat('d/m/Y', trim($this->dateFilter))->format('Y-m-d');
                    $query->whereDate('date', $singleDate);
                } catch (\Exception $e) {
                    // invalid format — skip filtering
                }
            }
        }

        return $query;
    }

    public function resetPagination()
    {
        $this->resetPage('holidaysPage');
    }
}
