@php(app()->instance('inline_validation_errors_shown', true))
@if ($errors->any())
    <div class="alert alert-danger">
        <div>
            <div class="d-flex">
                <span><i class="fa-solid fa-face-frown me-5"></i></span>
                <span class="">{{$errors->first()}}</span>
            </div>
        </div>
    </div>
@endif
