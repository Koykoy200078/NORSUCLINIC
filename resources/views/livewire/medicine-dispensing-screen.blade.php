<div>
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button
                class="nav-link {{ $tab === 'verify-prescriptions' ? 'active' : '' }}"
                wire:click="setTab('verify-prescriptions')"
                type="button"
                role="tab">
                <i class="fas fa-clipboard-check me-1"></i> Verify Prescription
            </button>
        </li>
        <li class="nav-item">
            <button
                class="nav-link {{ $tab === 'dispense-history' ? 'active' : '' }}"
                wire:click="setTab('dispense-history')"
                type="button"
                role="tab">
                <i class="fas fa-history me-1"></i> Dispense History
            </button>
        </li>
        <li class="nav-item">
            <button
                class="nav-link {{ $tab === 'stock-out' ? 'active' : '' }}"
                wire:click="setTab('stock-out')"
                type="button"
                role="tab">
                <i class="fas fa-arrow-circle-up me-1"></i> Stock Out
            </button>
        </li>
    </ul>

    @if ($tab === 'verify-prescriptions')
    <livewire:prescription-verification-table key="verify-prescriptions" :lazy="false" />
    @elseif ($tab === 'dispense-history')
    <livewire:medicine-dispense-table key="dispense-history" :lazy="false" />
    @elseif ($tab === 'stock-out')
    <livewire:stock-out-table key="stock-out" :lazy="false" />
    @endif
</div>