<div class="row">
    <div class="col-lg-4 mb-5">
        <div class="mb-3" io-image-input="true">
            <label for="about_image_1" class="form-label">{{__('messages.cms.about_image'). " "}}1:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="about_image_1" style="background-image: url('{{ !empty($cmsData['about_image_1']) ? $cmsData['about_image_1'] : asset('web/media/avatars/male.png') }}')">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{ __('messages.common.change_image') }}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" id="about_image_1_input" name="about_image_1" class="image-upload d-none about-image-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
            <div class="form-text">{{ __('messages.doctor.allowed_img') }}</div>
        </div>
    </div>

    <div class="col-lg-4 mb-5">
        <div class="mb-3" io-image-input="true">
            <label for="about_image_2" class="form-label">{{__('messages.cms.about_image'). " "}}2:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="about_image_2" style="background-image: url('{{ !empty($cmsData['about_image_2']) ? $cmsData['about_image_2'] : asset('web/media/avatars/male.png') }}')">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{ __('messages.common.change_image') }}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" id="about_image_2_input" name="about_image_2" class="image-upload d-none about-image-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
            <div class="form-text">{{ __('messages.doctor.allowed_img') }}</div>
        </div>
    </div>

    <div class="col-lg-4 mb-7">
        <div class="mb-3" io-image-input="true">
            <label for="about_image_3" class="form-label">{{__('messages.cms.about_image'). " "}}3:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="about_image_3" style="background-image: url('{{ !empty($cmsData['about_image_3']) ? $cmsData['about_image_3'] : asset('web/media/avatars/male.png') }}')">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{ __('messages.common.change_image') }}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" id="about_image_3_input" name="about_image_3" class="image-upload d-none about-image-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
            <div class="form-text">{{ __('messages.doctor.allowed_img') }}</div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="mb-5">
            {{ html()->label()->for('about_title')->text(__('messages.web.about_title').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('about_title', $cmsData['about_title'])->attributes(['class' => 'form-control', 'placeholder' => __('messages.web.about_title'), 'required', 'id' => 'aboutTitleId']) }}
        </div>
    </div>
    <div class="col-lg-12">
        <div class="mb-5">
            {{ html()->label()->for('about_experience')->text(__('messages.web.about_experience').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('about_experience', $cmsData['about_experience'])->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.experience'), 'required', 'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")', 'id' => 'aboutExperienceId']) }}
        </div>
    </div>
    <div class="col-lg-12">
        <div class="mb-5">
            {{ html()->label()->for('about_short_description')->text(__('messages.web.about_short_description').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->textarea('about_short_description')->text(html()->value('about_short_description', $cmsData['about_short_description']))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.web.about_short_description'), 'id' => 'cmsShortDescription', 'required', 'rows'=> 5]) }}
        </div>
    </div>
    <div class="col-lg-12">
        <div class="mb-5">
            {{ html()->label()->for('term_condition')->text(__('messages.cms.terms_conditions').':')->attributes(['class' => 'form-label required']) }}
            <div id="cmsTermConditionId" class="editor-height" style="height: 200px"></div>
            {{ html()->hidden('terms_conditions', null)->attributes(['id' => 'termData']) }}
        </div>
    </div>
    <div class="col-lg-12">
        <div class="mb-5">
            {{ html()->label()->for('privacy_policy')->text(__('messages.cms.privacy_policy').':')->attributes(['class' => 'form-label required']) }}
            <div id="cmsPrivacyPolicyId" class="editor-height" style="height: 200px"></div>
            {{ html()->hidden('privacy_policy', null)->attributes(['id' => 'privacyData']) }}
        </div>
    </div>
    <div class="d-flex">
        {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary']) }}
    </div>
</div>