<div
    x-data="{
        tab: '{{ request('tab', 'medicines') }}',
        validTabs: ['medicines', 'stock-in'],
        init() {
            if (!this.validTabs.includes(this.tab)) {
                this.setTab('medicines');
            }
        },
        setTab(t) {
            this.tab = t;
            const url = new URL(window.location);
            url.searchParams.set('tab', t);
            history.replaceState({}, '', url);
        }
    }">
    {{-- ============================================================
            MEDICINE SCREEN — Inventory tracking tabs only
         Tab switching uses Alpine x-show so child Livewire components
         are NEVER destroyed, avoiding the #[Lazy] race-condition error.
         No @entangle — parent NEVER re-renders on tab change.
    ============================================================ --}}

    {{-- Hidden URL helpers expected by JS in child views --}}
    {{ html()->hidden('medicineUrl', getRouteByRole('medicines.index'))->attributes(['id' => 'indexMedicineUrl']) }}
    {{ html()->hidden('medicines-show-modal', url('medicines-show-modal'))->attributes(['id' => 'medicinesShowModal']) }}
    {{ html()->hidden('medicine-language', getCurrentLoginUserLanguageName())->attributes(['id' => 'medicineLanguage']) }}
    {{ html()->hidden('medicine', __('messages.medicine.medicine'))->attributes(['id' => 'Medicine']) }}
    {{ html()->hidden('medicineLang', __('messages.delete.medicine'))->attributes(['id' => 'medicineLang']) }}

    {{-- ================================================================
         MAIN TABS — Alpine handles active state, no Livewire re-render
    ================================================================ --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item position-relative">
            <button class="nav-link" :class="{ active: tab === 'medicines' }"
                @click="setTab('medicines')" type="button" role="tab">
                <i class="fas fa-pills me-1"></i> {{ __('messages.medicine.medicines') }}
            </button>
        </li>
        <li class="nav-item position-relative">
            <button class="nav-link" :class="{ active: tab === 'stock-in' }"
                @click="setTab('stock-in')" type="button" role="tab">
                <i class="fas fa-arrow-circle-down me-1"></i> Stock In
            </button>
        </li>
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

</div>