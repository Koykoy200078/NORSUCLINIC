<div class="row gx-10 mb-5">
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('food_allergies')->text(__('messages.prescription.food_allergies').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('food_allergies', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.food_allergies')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('tendency_bleed')->text(__('messages.prescription.tendency_bleed').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('tendency_bleed', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.tendency_bleed')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('heart_disease')->text(__('messages.prescription.heart_disease').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('heart_disease', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.heart_disease')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('high_blood_pressure')->text(__('messages.prescription.high_blood_pressure').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('high_blood_pressure', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.high_blood_pressure')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('diabetic')->text(__('messages.prescription.diabetic').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('diabetic', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.diabetic')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('surgery')->text(__('messages.prescription.surgery').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('surgery', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.surgery')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('accident')->text(__('messages.prescription.accident').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('accident', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.accident')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('others')->text(__('messages.prescription.others').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('others', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.others')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('medical_history')->text(__('messages.prescription.medical_history').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('medical_history', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.medical_history')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('current_medication')->text(__('messages.prescription.current_medication').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('current_medication', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.current_medication')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('female_pregnancy')->text(__('messages.prescription.female_pregnancy').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('female_pregnancy', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.female_pregnancy')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('breast_feeding')->text(__('messages.prescription.breast_feeding').(':'))->attributes(['class' => 'form-label mb-3']) }}
            {{ html()->text('breast_feeding', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.breast_feeding')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('plus_rate')->text(__('messages.prescription.plus_rate').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('plus_rate', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.plus_rate')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('temperature')->text(__('messages.prescription.temperature').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('temperature', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.temperature')]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group mb-5">
            {{ html()->label()->for('problem_description')->text(__('messages.prescription.problem_description').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('problem_description')->text(html()->value('problem_description', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.problem_description'), 'rows' => 5]) }}
        </div>
    </div>
</div>
