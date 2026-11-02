@extends('layouts.app')
@section('title')
    {{ __('messages.medicine_categories') }}
@endsection
@section('css')
    {{--    <link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">--}}
@endsection
@section('content')
    @include('flash::message')
    <div class="container-fluid">
        @if(isRole('clinic_admin'))
            @can('manage_medicines')<div class="mb-3 text-end"><a href="{{ route('inventory-merge.index', ['kind' => 'category']) }}" class="btn btn-outline-primary">Merge duplicates</a></div>@endcan
        @endif
        {{html()->hidden('categoryCreateUrl', route('categories.store'))->attributes(['id'=>'indexCategoryCreateUrl'])}}
        {{html()->hidden('categoriesUrl', url('categories'))->attributes(['id'=>'indexCategoriesUrl'])}}
        {{ html()->hidden('category', __('messages.charge.charge_category'))->attributes(['id' => 'Category']) }}

        <div class="d-flex flex-column">
            <livewire:medicine-category-table/>
        </div>
        @include('categories.modal')
        @unless(isRole('doctor'))
        @include('categories.edit_modal')
        @endunless
        @include('categories.templates.templates')
        {{-- @include('partials.page.templates.templates') --}}
    </div>
@endsection
{{--    assets/js/category/category.js --}}
