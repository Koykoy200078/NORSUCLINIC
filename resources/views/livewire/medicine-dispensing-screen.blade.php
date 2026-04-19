<div
    x-data="{
        tab: '{{ request('tab', 'dispense-history') }}',
        setTab(t) {
            this.tab = t;
            const url = new URL(window.location);
            url.searchParams.set('tab', t);
            history.replaceState({}, '', url);
        }
    }">
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'dispense-history' }"
                @click="setTab('dispense-history')" type="button" role="tab">
                <i class="fas fa-history me-1"></i> Dispense History
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ active: tab === 'stock-out' }"
                @click="setTab('stock-out')" type="button" role="tab">
                <i class="fas fa-arrow-circle-up me-1"></i> Stock Out
            </button>
        </li>
    </ul>

    <div x-show="tab === 'dispense-history'">
        <livewire:medicine-dispense-table key="dispense-history" :lazy="false" />
    </div>

    <div x-show="tab === 'stock-out'" x-cloak>
        <livewire:stock-out-table key="stock-out" :lazy="false" />
    </div>
</div>