<div
    x-data="{
        tab: '{{ request('tab', 'medicines') }}',
        setTab(t) {
            this.tab = t;
            const url = new URL(window.location);
            url.searchParams.set('tab', t);
            history.replaceState({}, '', url);
        }
    }">
    {{-- ============================================================
         MEDICINE SCREEN — All medicine management in one tabbed screen
         Tab switching uses Alpine x-show so child Livewire components
         are NEVER destroyed, avoiding the #[Lazy] race-condition error.
         No @entangle — parent NEVER re-renders on tab change.
    ============================================================ --}}

    {{-- Hidden URL helpers expected by JS in child views --}}
    {{ Form::hidden('medicineUrl',
        isRole('staff') ? route('staff.medicines.index') :
        (isRole('doctor') ? route('doctors.medicines.index') : route('medicines.index')),
        ['id' => 'indexMedicineUrl']) }}
    {{ Form::hidden('medicines-show-modal', url('medicines-show-modal'), ['id' => 'medicinesShowModal']) }}
    {{ Form::hidden('medicine-language', getCurrentLoginUserLanguageName(), ['id' => 'medicineLanguage']) }}
    {{ Form::hidden('medicine', __('messages.medicine.medicine'), ['id' => 'Medicine']) }}
    {{ Form::hidden('medicineLang', __('messages.delete.medicine'), ['id' => 'medicineLang']) }}
    {{ Form::hidden('categoryCreateUrl', route('categories.store'), ['id' => 'indexCategoryCreateUrl']) }}
    {{ Form::hidden('categoriesUrl', url('categories'), ['id' => 'indexCategoriesUrl']) }}
    {{ Form::hidden('category', __('messages.charge.charge_category'), ['id' => 'Category']) }}
    {{ Form::hidden('genericUrl',
        isRole('clinic_admin') ? route('generics.index') :
        (isRole('staff') ? route('staff.generics.index') :
        (isRole('doctor') ? route('doctors.generics.index') : route('generics.index'))),
        ['id' => 'indexGenericUrl']) }}
    {{ Form::hidden('medicine_generic',
        __('messages.medicine.medicine') . ' ' . __('messages.medicine.generic'),
        ['id' => 'medicineGeneric']) }}

    {{-- ================================================================
         MAIN TABS — Alpine handles active state, no Livewire re-render
    ================================================================ --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'medicines' }"
                @click="setTab('medicines')" type="button" role="tab">
                <i class="fas fa-pills me-1"></i> {{ __('messages.medicine.medicines') }}
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'stock-in' }"
                @click="setTab('stock-in')" type="button" role="tab">
                <i class="fas fa-arrow-circle-down me-1"></i> Stock In
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'stock-out' }"
                @click="setTab('stock-out')" type="button" role="tab">
                <i class="fas fa-arrow-circle-up me-1"></i> Stock Out
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'categories' }"
                @click="setTab('categories')" type="button" role="tab">
                <i class="fas fa-tags me-1"></i> {{ __('messages.medicine_categories') }}
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'generics' }"
                @click="setTab('generics')" type="button" role="tab">
                <i class="fas fa-dna me-1"></i> {{ __('messages.medicine.medicine_generics') }}
            </button>
        </li>
        @if (!isRole('doctor'))
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'history' }"
                @click="setTab('history')" type="button" role="tab">
                <i class="fas fa-history me-1"></i> Dispense History
            </button>
        </li>
        @endif
    </ul>

    {{-- ================================================================
         TAB CONTENT — x-show keeps ALL components mounted (never destroyed)
         This is CRITICAL: prevents #[Lazy] race-condition errors.
    ================================================================ --}}

    {{-- TAB: MEDICINES (Medicine List) --}}
    <div x-show="tab === 'medicines'">
        <livewire:medicine-table key="med-list" :lazy="false" />
    </div>

    {{-- TAB: STOCK IN --}}
    <div x-show="tab === 'stock-in'" x-cloak>
        <livewire:stock-in-table key="stock-in" :lazy="false" />
    </div>

    {{-- TAB: STOCK OUT --}}
    <div x-show="tab === 'stock-out'" x-cloak>
        <livewire:stock-out-table key="stock-out" :lazy="false" />
    </div>

    {{-- TAB: CATEGORIES --}}
    <div x-show="tab === 'categories'" x-cloak>
        <livewire:medicine-category-table key="cats" :lazy="false" />
    </div>

    {{-- TAB: GENERICS --}}
    <div x-show="tab === 'generics'" x-cloak>
        <livewire:medicine-generic-table key="gens" :lazy="false" />
    </div>

    {{-- TAB: DISPENSE HISTORY --}}
    @if (!isRole('doctor'))
    <div x-show="tab === 'history'" x-cloak>
        <livewire:medicine-dispense-table key="hist" :lazy="false" />
    </div>
    @endif

</div>