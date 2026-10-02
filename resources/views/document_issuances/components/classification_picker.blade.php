{{--
    Illness (by body system) and services rendered, picked from the clinic's own lists. These picks are what the
    ACCOMPLISHMENT REPORT counts. $illnessSystems / $serviceGroups come from the view composer in AppServiceProvider;
    $requestDocument is only there when an existing consultation is being edited.
--}}
@php
    $classifiedDocument = $requestDocument ?? null;

    $pickedIllnessIds = collect(old('illness_ids', $classifiedDocument ? $classifiedDocument->illnesses->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)->all();
    $pickedOtherText = old('illness_other', $classifiedDocument
        ? $classifiedDocument->illnesses->filter(fn ($illness) => filled($illness->pivot->other_text))
            ->mapWithKeys(fn ($illness) => [$illness->id => $illness->pivot->other_text])->all()
        : []);
    $pickedServiceIds = collect(old('service_ids', $classifiedDocument ? $classifiedDocument->services->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)->all();

    // A line the clinic switched off is not offered any more, unless this consultation already has it picked.
    $offered = fn ($line, array $picked) => $line->is_active || in_array($line->id, $picked, true);
@endphp

<div class="grid grid-cols-4 gap-2 py-2" id="classification_section">
    <div class="col-span-1">
        <label class="block font-bold">Illness / diagnosis</label>
        <label class="block text-xs">Pick from the clinic list. These are counted in the Accomplishment Report.</label>
    </div>
    <div class="col-span-3">
        {{-- Browsers send nothing for an empty checklist, so this marker tells the server the picks were on the form. --}}
        <input type="hidden" name="classification_submitted" value="1">

        <input type="search" id="illness_filter" class="form-control form-control-sm mb-2" autocomplete="off"
               placeholder="Search illness (e.g. cough, hypertension)">
        <div id="illness_filter_empty" class="text-muted small mb-2" style="display:none;">No illness on the list matches that search.</div>

        @foreach($illnessSystems as $system)
            @php
                $systemIllnesses = $system->illnesses->filter(fn ($illness) => $offered($illness, $pickedIllnessIds));
                $systemPicked = $systemIllnesses->whereIn('id', $pickedIllnessIds)->count();
            @endphp
            <details class="border rounded mb-2 illness-system" {{ $systemPicked > 0 ? 'open' : '' }}>
                <summary class="px-3 py-2 fw-semibold" style="cursor:pointer;">
                    {{ $system->name }}
                    <span class="badge bg-primary ms-2 illness-system-count" style="{{ $systemPicked > 0 ? '' : 'display:none;' }}">{{ $systemPicked }}</span>
                </summary>
                <div class="px-3 pb-2">
                    @foreach($systemIllnesses->groupBy(fn ($illness) => $illness->group_label ?? '') as $groupLabel => $illnesses)
                        @if($groupLabel !== '')
                            <div class="small fw-semibold text-muted mt-2">{{ $groupLabel }}</div>
                        @endif
                        <div class="d-flex flex-wrap">
                            @foreach($illnesses as $illness)
                                <div class="form-check me-4 illness-item" data-label="{{ strtolower($illness->name . ' ' . $groupLabel . ' ' . $system->name) }}">
                                    <input type="checkbox" name="illness_ids[]" value="{{ $illness->id }}" id="illness_{{ $illness->id }}" class="form-check-input illness-checkbox" {{ in_array($illness->id, $pickedIllnessIds, true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="illness_{{ $illness->id }}">{{ $illness->name }}</label>
                                    @if($illness->is_other)
                                        <input type="text" name="illness_other[{{ $illness->id }}]" value="{{ $pickedOtherText[$illness->id] ?? '' }}" maxlength="150"
                                               class="form-control form-control-sm d-inline-block ms-2" style="width:220px;" placeholder="Specify {{ strtolower($system->name) }} illness">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-4 gap-2 py-2" id="services_section">
    <div class="col-span-1">
        <label class="block font-bold">Services rendered</label>
        <label class="block text-xs">Tick what was done. Lines marked auto are also counted from the visit's own records.</label>
    </div>
    <div class="col-span-3">
        @foreach($serviceGroups as $categoryLabel => $services)
            <div class="small fw-semibold text-muted mt-2 mb-1">{{ $categoryLabel }}</div>
            <div class="d-flex flex-wrap">
                @foreach($services->filter(fn ($service) => $offered($service, $pickedServiceIds)) as $service)
                    <div class="form-check me-4 mb-1" style="min-width: 260px;">
                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" id="service_{{ $service->id }}" class="form-check-input" {{ in_array($service->id, $pickedServiceIds, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="service_{{ $service->id }}">
                            {{ $service->name }}
                            @if($service->auto_rule)
                                <span class="badge bg-light text-muted border ms-1" title="Also counted automatically from the visit's records">auto</span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        var section = document.getElementById('classification_section');
        if (!section) { return; }

        var filter = document.getElementById('illness_filter');
        var emptyNote = document.getElementById('illness_filter_empty');

        function refreshCounts() {
            section.querySelectorAll('.illness-system').forEach(function (system) {
                var count = system.querySelectorAll('.illness-checkbox:checked').length;
                var badge = system.querySelector('.illness-system-count');
                badge.textContent = count;
                badge.style.display = count > 0 ? '' : 'none';
            });
        }

        function applyFilter() {
            var term = (filter.value || '').toLowerCase().trim();
            var anyShown = false;

            section.querySelectorAll('.illness-system').forEach(function (system) {
                var shown = 0;
                system.querySelectorAll('.illness-item').forEach(function (item) {
                    var match = term === '' || item.getAttribute('data-label').indexOf(term) !== -1;
                    item.style.display = match ? '' : 'none';
                    if (match) { shown++; }
                });
                system.style.display = shown > 0 ? '' : 'none';
                if (term !== '' && shown > 0) { system.open = true; }
                if (shown > 0) { anyShown = true; }
            });

            emptyNote.style.display = anyShown ? 'none' : '';
        }

        section.addEventListener('change', function (event) {
            if (event.target.classList.contains('illness-checkbox')) { refreshCounts(); }
        });
        filter.addEventListener('input', applyFilter);
        // Enter in the search box must not submit the whole consultation.
        filter.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); }
        });
    })();
</script>
