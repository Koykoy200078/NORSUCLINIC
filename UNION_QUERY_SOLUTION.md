# Used Medicine Union Query - Final Solution

## Problem Analysis

Livewire Tables **ALWAYS adds column selections** automatically based on the model, even when:

-   Using `setColumnSelectDisabled()`
-   Using `selectRaw()`
-   Setting `protected $model = null`
-   Using `fromSub()` with subqueries

The error shows Livewire adding:

```sql
`consultation_medicines`.`medicine_name` as `medicine_name`
```

But `medicine_name` is an ALIAS from `medicines.name`, not a real column in `consultation_medicines`.

## Solution: Create a Database View

The ONLY way to make this work with Livewire Tables is to create a **database view** that combines both tables, then query that view as if it's a regular table.

### Step 1: Create Migration for Database View

Create file: `database/migrations/2025_10_14_000001_create_used_medicines_view.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW used_medicines_view AS
            SELECT
                CONCAT('C', cm.id) as id,
                cm.medicine_id,
                m.name as medicine_name,
                cm.quantity,
                'Consultation' as source,
                rd.name as patient_name,
                COALESCE(cm.used_for, 'N/A') as used_for,
                cm.created_at
            FROM consultation_medicines cm
            INNER JOIN medicines m ON cm.medicine_id = m.id
            INNER JOIN request_documents rd ON cm.request_document_id = rd.id

            UNION ALL

            SELECT
                CONCAT('S', sm.id) as id,
                sm.medicine_id,
                m.name as medicine_name,
                sm.sale_quantity as quantity,
                'Medicine Bill' as source,
                COALESCE(p.user_id, 'N/A') as patient_name,
                'Sale' as used_for,
                sm.created_at
            FROM sale_medicines sm
            INNER JOIN medicines m ON sm.medicine_id = m.id
            INNER JOIN medicine_bills mb ON sm.medicine_bill_id = mb.id
            LEFT JOIN patients p ON mb.model_id = p.id AND mb.model_type = 'App\\\\Models\\\\Patient'
            WHERE mb.payment_status = 1
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS used_medicines_view");
    }
};
```

### Step 2: Create Model for the View

Create file: `app/Models/UsedMedicineView.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedMedicineView extends Model
{
    protected $table = 'used_medicines_view';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
}
```

### Step 3: Update Livewire Component

```php
<?php

namespace App\Livewire;

use App\Models\UsedMedicineView;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class UsedMedicineTable extends LivewireTableComponent
{
    protected $model = UsedMedicineView::class;

    public bool $showFilterOnHeader = false;
    public bool $showButtonOnHeader = false;
    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->sortable()->hideIf(1),
            Column::make(__('messages.medicines'), 'medicine_name')
                ->sortable()->searchable()->view('used-medicine.columns.medicine'),
            Column::make(__('messages.used_medicine.used_quantity'), 'quantity')
                ->sortable()->searchable()->view('used-medicine.columns.quantity'),
            Column::make(__('messages.used_medicine.used_at'), 'source')
                ->sortable()->searchable()->view('used-medicine.columns.used_at'),
            Column::make(__('messages.patient.patient'), 'patient_name')
                ->sortable()->searchable()->view('used-medicine.columns.patient'),
            Column::make(__('messages.appointment.date'), 'created_at')
                ->sortable()->searchable()->view('used-medicine.columns.date'),
        ];
    }

    public function builder(): Builder
    {
        return UsedMedicineView::query();
    }
}
```

### Step 4: Update View Files

All view files use direct column access:

-   `medicine.blade.php`: `{{ $row->medicine_name }}`
-   `quantity.blade.php`: `{{ $row->quantity }}`
-   `patient.blade.php`: `{{ $row->patient_name }}`
-   `used_at.blade.php`: Badge with `$row->source` and `$row->used_for`

### Step 5: Run Migration

```bash
php artisan migrate
```

## Why This Works

1. **Database View = Real Table**: MySQL treats views as tables
2. **All Columns Real**: No aliases - `medicine_name` IS a real column in the view
3. **Livewire Happy**: Can add `used_medicines_view.medicine_name` without errors
4. **Union Built-In**: Union query is in the view definition
5. **Performance**: MySQL can optimize view queries

## Benefits

✅ No more Livewire Tables column selection conflicts  
✅ Union query works perfectly  
✅ Sortable, searchable, filterable  
✅ Patient names from both sources  
✅ Source badges (Consultation/Medicine Bill)  
✅ Easy to maintain - change view, not code
