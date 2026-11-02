@extends('layouts.app')
@section('title')
    {{ __('messages.cms.cms') }}
@endsection
@section('content')
    <div class="container-fluid">
        @include('flash::message')
        <div class="d-flex flex-column">
            <div class="row">
                <div class="col-12">
                    @include('layouts.errors')
                </div>
            </div>
            {{html()->hidden('term_conditionData', $cmsData['terms_conditions'])->attributes(['id'=>'cmsTermConditionData'])}}
            {{html()->hidden('privacy_policyData', $cmsData['terms_conditions'])->attributes(['id'=>'cmsPrivacyPolicyData'])}}
            <div class="card">
                <div class="card-body p-12">
                    {{ html()->form('POST', route('cms.update'))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id' => 'addCMSForm'])->open() }}
                        @include('fronts.cms.fields')
                    {{ html()->closeModelForm() }}
                </div>
            </div>
        </div>
    </div>
@endsection

