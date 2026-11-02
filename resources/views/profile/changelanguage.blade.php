<div class="modal fade" id="changeLanguageModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{ __('messages.user.change_language') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id'=>'changeLanguageForm'])->open() }}
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="editLanguageValidationErrorsBox"></div>
                    <div>
                            @php
                                $user = Auth::user()
                            @endphp
                            {{ html()->label()->for('Language')->text(__('messages.user.language').':')->attributes(['class' => 'form-label']) }}
                            {{ html()->select('language', \App\Models\User::LANGUAGES, isset($user) ? $user->language : null)->attributes(['class' => 'form-control form-select', 'required', 'data-control' => 'select2', 'id' => 'selectLanguage', 'data-dropdown-parent'=>'#changeLanguageModal']) }}
                    </div>
                </div>
                <div class="modal-footer pt-0">
                {{ html()->button(__('messages.common.save'), 'button')->attributes(['class' => 'btn btn-primary m-0', 'id' => 'languageChangeBtn']) }}
                {{ html()->button(__('messages.common.discard'), 'button')->attributes(['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss' => 'modal']) }}
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>
