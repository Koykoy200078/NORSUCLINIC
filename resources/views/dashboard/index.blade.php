@extends('layouts.app')
@section('title')
{{ __('messages.dashboard') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-xl-12">
                <livewire:dashboard />
            </div>

            <div class="col-xl-12">
                <livewire:admin-dashBoard-table />
            </div>
        </div>
    </div>
</div>
@include('dashboard.templates.templates')
<input type="hidden" id="hasDefaultPasswordFlag" value="{{ ($hasDefaultPassword ?? false) ? 1 : 0 }}">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var hasDefaultPasswordFlag = document.getElementById('hasDefaultPasswordFlag');
        var hasDefaultPassword = hasDefaultPasswordFlag && hasDefaultPasswordFlag.value === '1';
        if (hasDefaultPassword) {
            setTimeout(function() {
                var btn = document.getElementById('changePassword');
                if (btn) {
                    btn.click();
                } else if (typeof $ !== 'undefined') {
                    $('#changePassword').click();
                }
            }, 1000);
        }
    });
</script>
@endsection