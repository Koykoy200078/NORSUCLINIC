<div class="row">
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('First Name')->text(__('messages.doctor.first_name').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('first_name', $user->first_name)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.first_name'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Last Name')->text(__('messages.doctor.last_name').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('last_name', $user->last_name)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.last_name'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Email')->text(__('messages.user.email').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->email('email', $user->email)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.web.email')]) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('employee_id')->text(__('Employee ID').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('employee_id', $user->employee_id)->attributes(['class' => 'form-control', 'placeholder' => __('Employee ID'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Contact')->text(__('messages.user.contact_number').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('contact', $user->contact)->forgetAttribute('id')->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' =>  __('messages.patient.contact_no')]) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('DOB')->text(__('messages.doctor.dob').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('dob', $user->dob)->attributes(['class' => 'form-control doctor-dob', 'placeholder' => __('messages.doctor.dob'), 'id'=>'dob']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Specialization')->text(__('messages.doctor.specialization').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->select('specializations[]', $data['specializations'], old('specializations', $data['doctorSpecializations']))->multiple()->forgetAttribute('id')->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2"]) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Experience')->text(__('messages.doctor.experience').':')->attributes(['class' => 'form-label']) }}
        {{ html()->number('experience', $doctor->experience)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.experience'), 'step'=>'any', 'min'=>'0']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('prc_license_number')->text(__('PRC/Medical License Number').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('prc_license_number', $doctor->prc_license_number)->attributes(['class' => 'form-control', 'placeholder' => __('PRC/Medical License Number'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('ptr_number')->text(__('PTR Number').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('ptr_number', $doctor->ptr_number)->attributes(['class' => 'form-control', 'placeholder' => __('Professional Tax Receipt Number'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('s2_license_number')->text(__('S2 License Number').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('s2_license_number', $doctor->s2_license_number)->attributes(['class' => 'form-control', 'placeholder' => __('S2 License Number (if applicable)')]) }}
    </div>
    <div class="col-md-12 mb-5">
        {{ html()->label()->for('consultation_hours')->text(__('Consultation Hours').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->textarea('consultation_hours')->text(html()->value('consultation_hours', $doctor->consultation_hours))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => __('e.g. Mon-Wed 8:00AM-12:00PM, Thu-Fri 1:00PM-5:00PM'), 'required']) }}
    </div>
    <div class="col-md-6 mb-5">
        <label class="form-label required">
            {{__('messages.doctor.select_gender')}}
            :
        </label>
        <span class="is-valid">
            <div class="mt-2">
                <input class="form-check-input" type="radio" name="gender" value="1" {{ !empty($user->gender) && $user->gender === 1 ? 'checked' : '' }}>
                <label class="form-label mr-3">{{__('messages.doctor.male')}}</label>
                <input class="form-check-input ms-2" type="radio" name="gender" value="2" {{ !empty($user->gender) && $user->gender === 2 ? 'checked' : ''}}>
                <label class="form-label mr-3">{{__('messages.doctor.female')}}</label>
            </div>
        </span>
    </div>
    <div class="col-md-6 mb-5">
        <label class="form-label">{{ __('messages.patient.blood_type').':' }}</label>
        {{ html()->select('blood_type', $bloodGroup, $user->blood_type)->placeholder(__('messages.doctor.select_blood_type'))->forgetAttribute('id')->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2"]) }}
    </div>
    <div class="col-md-6 mb-5 d-none">
        <div class="mb-3" io-image-input="true">
            <label for="exampleInputImage" class="form-label">{{__('messages.doctor.profile')}}:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="exampleInputImage" style="background-image: url({{ !empty($user->profile_image) ? $user->profile_image : asset('web/media/avatars/male.png') }})">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{__('messages.user.edit_profile')}}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" id="profilePicture" name="profile" class="image-upload d-none profile-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-5 d-none">
        <label class="form-label">{{__('messages.doctor.status')}}:</label>
        <div class="col-lg-8">
            <div class="form-check form-check-solid form-switch">
                <input name="status" class="form-check-input checkBoxClass"
                    type="checkbox" {{$user->status == 1 ? 'checked' : ''}}>
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
    </div>
</div>

<div class="row gx-10 mb-5">
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Address 1')->text(__('messages.doctor.address1').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('address1', isset($user->address->address1) ? $user->address->address1 : '')->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.doctor.address1')]) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Permanent Address')->text(__('messages.doctor.address2').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('address2', isset($user->address->address2) ? $user->address->address2 : '')->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' =>  __('messages.doctor.address2')]) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('Country')->text(__('messages.doctor.country').':')->attributes(['class' => 'form-label']) }}
        {{ html()->select('country_id', $countries, isset($user->address->country_id) ? $user->address->country_id:null)->placeholder(__('messages.common.select_country'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=>'editDoctorCountryId']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('State')->text(__('messages.doctor.province').':')->attributes(['class' => 'form-label']) }}
        {{ html()->select('state_id', (isset($state) && $state!=null) ? $state:[], isset($user->address->state_id) ? $user->address->state_id:null)->placeholder(__('messages.common.province'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=> 'editDoctorStateId']) }}
    </div>
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('City')->text(__('City/Municipality').':')->attributes(['class' => 'form-label']) }}
        {{ html()->select('city_id', (isset($cities) && $cities!=null) ? $cities:[], isset($user->address->city_id) ? $user->address->city_id:null)->placeholder(__('City/Municipality'))->attributes(['class' => 'io-select2 form-select', 'data-control'=>"select2", 'id'=> 'editDoctorCityId']) }}
    </div>
    <div class="col-md-6">
        {{ html()->label()->for('Postal Code')->text(__('messages.doctor.postal_code').':')->attributes(['class' => 'form-label']) }}
        {{ html()->text('postal_code', isset($user->address->postal_code) ? $user->address->postal_code : '')->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.doctor.postal_code')]) }}
    </div>
</div>
<div>
    <div class="fw-bolder fs-3 rotate collapsible mb-4">{{ __('messages.doctor.qualification_information') }}
    </div>
    <a class="btn btn-primary float-end mb-4" id="addQualification">{{__('messages.doctor.add_qualification')}}</a>
</div>
<input type="hidden" name="deletedQualifications" value="" id="deletedQualifications">
<div class="row showQualification w-100">
    <div class="col-md-4 mb-5">
        {{ html()->label()->for('Degree')->text(__('messages.doctor.degree').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('degree', null)->attributes(['class' => 'form-control degree', 'placeholder' => __('messages.doctor.degree'), 'id'=>'degree']) }}
    </div>
    <div class="col-md-4 mb-5">
        {{ html()->label()->for('university')->text(__('messages.doctor.university').':')->attributes(['class' => 'form-label required']) }}
        {{ html()->text('university', null)->attributes(['class' => 'form-control university', 'placeholder' => __('messages.doctor.university'), 'id'=>'university']) }}
    </div>
    <div class="col-md-4 mb-5">
        <label class="form-label required">{{__('messages.doctor.year')}}:</label>
        {{ html()->select('year', $years, !empty($qualifications->year) ? $qualifications->year : null)->placeholder(__('messages.doctor.select_year'))->attributes(['class' => 'io-select2 form-select year', 'data-control'=>"select2", 'id'=> 'year']) }}
    </div>
    <div class="mb-5 col-md-4">
        <button type="button" class="btn btn-primary me-3"
            id="saveQualification">{{__('messages.common.save')}}</button>
        <button type="button" class="btn btn-secondary"
            id="cancelQualification">{{__('messages.common.discard')}}</button>
    </div>
</div><br>
<div class="table-responsive-sm w-100 mt-4">
    <table class="table table-row-dashed table-row-gray-300 gy-7 align-middle" id="doctorQualificationTbl">
        <thead>
            <tr class="fw-bolder fs-6 text-gray-800">
                <th>{{Str::upper(__('messages.doctor.sr_no'))}}</th>
                <th>{{ Str::upper(__('messages.doctor.degree'))}}</th>
                <th>{{ Str::upper(__('messages.doctor.collage_university'))}}</th>
                <th>{{ Str::upper(__('messages.doctor.year'))}}</th>
                <th class="text-center">{{ Str::upper(__('messages.common.action'))}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($qualifications as $index => $qualification)
            <tr>
                <td id="qualificationId" data-value="{{ $index+1 }}">{{$index+1}}</td>
                <td id="degreeTd">{{$qualification->degree}}</td>
                <td id="universityTd">{{$qualification->university}}</td>
                <td id="yearTd">{{$qualification->year}}</td>
                <td class="text-center whitespace-nowrap">
                    <div class="d-flex justify-content-center">
                        <a data-id="{{$index+1}}" data-primary-id="{{$qualification->id}}" title="{{ __('messages.common.edit') }}"
                            class="btn edit-btn-qualification btn-icon px-1 fs-3 text-primary" data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('messages.common.edit') }}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a data-id="{{$qualification->id}}" title="{{ __('messages.common.delete') }}" class="delete-btn-qualification btn btn-icon px-1 fs-3 text-danger" data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('messages.common.delete') }}">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex mt-4">
    <button type="submit" class="btn btn-primary">{{__('messages.common.save')}}</button>&nbsp;&nbsp;&nbsp;
    <a href="{{ 
                   getRouteByRole('doctors.index')
               }}" type="reset" id="ResetForm"
        class="btn btn-secondary">{{__('messages.common.discard')}}</a>
</div>