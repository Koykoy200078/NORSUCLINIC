<div class="d-flex float-start">
    <div class="ms-3">
        <select class="form-select form-select-solid" id="dispenseSourceFilter" wire:model.live="sourceFilter">
            <option value="">All sources</option>
            @foreach ($filterHeads[0] as $source)
                <option wire:key="dispense-source-{{ $source }}" value="{{ $source }}">{{ $source }}</option>
            @endforeach
        </select>
    </div>
</div>
