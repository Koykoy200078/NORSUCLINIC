{{--
    The list screen drawn by App\Livewire\Tables\TableComponent. The markup (and its CSS classes) is the one the old table
    library drew, kept so the screens look the same: search box and header buttons, striped table, sortable headers,
    "Show 10 25 50" and the page links.
--}}
@php
    $tableName = $this->getTableName();
    $tableAttributes = new \Illuminate\View\ComponentAttributeBag($this->getTableAttributes());
    $paginationLinks = $rows->lastPage() > 1;
@endphp

<div>
    <div wire:key="{{ $tableName }}-wrapper">
        <div id="datatable-{{ $this->getId() }}">
            <div wire:offline.class.remove="d-none" class="d-none">
                <div class="alert alert-danger d-flex align-items-center">
                    @include('livewire.tables.icons.x-circle')
                    <span class="d-inline-block ml-2">@lang('You are not connected to the internet.')</span>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-5">
                @if ($this->searchIsEnabled())
                <div class="mb-3 mb-sm-0">
                    <form class="d-flex position-relative">
                        <div class="position-relative d-flex width-320">
                            <span class="position-absolute d-flex align-items-center top-0 bottom-0 left-0 text-gray-600 ms-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input class="form-control search-box ps-8" wire:model.live.debounce.500ms="search" type="search"
                                placeholder="{{ __('messages.common.search') }}" aria-label="Search">
                        </div>
                    </form>
                </div>
                @endif
                <div>
                    @if ($this->showFilterOnHeader == true)
                        @include($this->FilterComponent[0], [
                            'filterHeads' => [
                                $this->FilterComponent[1],
                                isset($this->FilterComponent[2]) ? $this->FilterComponent[2] : '',
                            ],
                        ])
                    @endif
                    @if ($this->showButtonOnHeader)
                        @include($this->buttonComponent)
                    @endif
                </div>
            </div>

            <div wire:key="{{ $tableName }}-twrap" class="table-responsive">
                <table wire:key="{{ $tableName }}-table"
                    {{ $tableAttributes->class(['table table-striped' => $tableAttributes['default'] ?? true])->except('default') }}>
                    <thead wire:key="{{ $tableName }}-thead">
                        <tr>
                            @foreach ($columns as $index => $column)
                                @continue($column->isHidden())
                                @php
                                    $th = $this->thAttributes($column);
                                    $sortsByField = $column->isSortable();
                                    $direction = $column->hasField() ? $this->getSort($column->getColumnSelectName()) : $this->getSort($column->getSlug());
                                @endphp
                                <th scope="col" wire:key="{{ $tableName }}-header-col-{{ $column->getSlug() }}" {{ $th->except('default') }}>
                                    @if ($sortsByField || $column->hasSortCallback())
                                        <div class="d-flex align-items-center"
                                            wire:click="sortBy('{{ $sortsByField ? $column->getColumnSelectName() : $column->getSlug() }}')">
                                            <span>{{ $column->getTitle() }}</span>

                                            <span class="relative d-flex align-items-center">
                                                @if ($direction === 'asc')
                                                    @include('livewire.tables.icons.chevron-up')
                                                @elseif ($direction === 'desc')
                                                    @include('livewire.tables.icons.chevron-down')
                                                @else
                                                    @include('livewire.tables.icons.chevron-up-down')
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        {{ $column->getTitle() }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody wire:key="{{ $tableName }}-tbody" id="{{ $tableName }}-tbody">
                        @forelse ($rows as $rowIndex => $row)
                            @php($rowKey = $row->{$this->getPrimaryKey()})
                            <tr rowpk='{{ $rowKey }}' wire:loading.class.delay="opacity-50 dark:bg-gray-900 dark:opacity-60"
                                id="{{ $tableName }}-row-{{ $rowKey }}" wire:key="{{ $tableName }}-tablerow-tr-{{ $rowKey }}"
                                class="bg-light">
                                @foreach ($columns as $colIndex => $column)
                                    @continue($column->isHidden())
                                    <td wire:key="{{ $tableName }}-table-td-{{ $rowKey }}-{{ $column->getSlug() }}"
                                        {{ $this->tdAttributes($column, $row, $colIndex, $rowIndex)->except('default') }}>
                                        {{ $column->renderContents($row) }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr wire:key="empty-message-{{ $this->getId() }}">
                                <td colspan="{{ $this->getColspanCount() }}">
                                    <div class="text-center">
                                        {{ $this->getEmptyMessage() }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <div class="d-flex align-items-center flex-xxl-row flex-column mb-5 mt-3">
                    <div class="mb-xxl-0 mb-xl-5 mb-3 d-flex align-items-center justify-content-sm-start justify-content-center">
                        <span class="me-3 text-gray-600 fs-4 fs-xl-6">@lang('Show')</span>
                        <select wire:model.live="perPage" id="perPage" class="form-select w-auto data-sorting pl-1 pr-5 py-2 border-0">
                            @foreach ($this->getPerPageAccepted() as $item)
                                <option value="{{ $item }}" wire:key="per-page-{{ $item }}-{{ $tableName }}">
                                    {{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($paginationLinks)
                        <div class="row flex-md-row-reverse flex-column-reverse mx-0 w-100">
                            <div class="col-12 col-xxl-9 col-lg-8 overflow-auto pagination-center ms-auto">
                                {{ $rows->links('livewire.tables.pagination') }}
                            </div>
                            <div class="col-12 col-xxl-3 col-lg-4 text-center text-lg-end text-gray-600 d-flex align-items-center justify-content-md-start justify-content-center mb-md-0 mb-3 flex-wrap ps-0">
                                <div class="fs-4 fs-xl-6 ms-lg-3">
                                    <span>@lang('Showing')</span>
                                    <span class="fw-bold">{{ $rows->count() ? $rows->firstItem() : 0 }}</span>
                                    <span>@lang('to')</span>
                                    <span class="fw-bold">{{ $rows->count() ? $rows->lastItem() : 0 }}</span>
                                    <span>@lang('of')</span>
                                    <span class="fw-bold">{{ $rows->total() }}</span>
                                    <span>@lang('results')</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12 ms-3 text-gray-600 fs-4">
                                @lang('Showing')
                                <strong>{{ $rows->count() }}</strong>
                                @lang('results')
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
