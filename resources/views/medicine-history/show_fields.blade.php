<div class="d-flex align-items-center pb-10">
    <img alt="Logo" src="{{ asset(getAppLogo()) }}" height="100px" width="100px">
</div>
<div class="m-0">
    <div class="fs-3 text-gray-800 mb-8"> #{{ $medicineBill->history_number }}</div>
    <div class="row g-5 mb-11">
        <div class="col-sm-3">
            <div class="pb-2 fs-5 text-gray-600">{{ __('Patient Name').':' }}</div>
            <div class="fs-5 text-gray-800">{{ $medicineBill->patient->patientUser->full_name }}</div>
        </div>
        <div class="col-sm-3">
            <div class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine_bills.bill_date').':' }}</div>
            <div class="fs-5 text-gray-800">{{ Carbon\Carbon::parse($medicineBill->bill_date)->format('jS M, Y g:i A') }}</div>
        </div>
        {{-- <div class="col-sm-3">
            <div class="pb-2 fs-5 text-gray-600">{{ __('messages.bill.admission_id').':' }}
    </div>
    <div class="fs-5 text-gray-800">{{ $medicineBill->patientAdmission->patient_admission_id }}</div>
</div> --}}
<div class="col-sm-3">
    <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('auth.email').':' }}</div>
    <div class="fs-5 text-gray-800">{{ $medicineBill->patient->patientUser->email }}</div>
</div>
<div class="col-sm-3">
    <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('messages.user.gender').':' }}</div>
    <div class="fs-5 text-gray-800">
        {{ ($medicineBill->patient->patientUser->gender == 1) ? __('messages.staff.male') : (($medicineBill->patient->patientUser->gender == 2) ? __('messages.staff.female') : __('messages.common.n/a')) }}
    </div>
</div>
<!-- <div class="col-sm-3">
    <div class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine_bills.payment_status').':' }}</div>
    <div class="fs-5 text-gray-800">{{ App\Models\MedicineBill::PAYMENT_STATUS_ARRAY[$medicineBill->payment_status] }}</div>
</div> -->
</div>
<div class="row g-5 mb-11">
    <div class="col-sm-3">
        <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('messages.medicine_bills.cell_no').':' }}</div>
        <div class="fs-5 text-gray-800">{{ !empty($medicineBill->patient->patientUser->phone) ? $medicineBill->patient->patientUser->phone : __('messages.common.n/a') }}</div>
    </div>

    <div class="col-sm-3">
        <div class="pb-2 fs-5 text-gray-600">{{ __('Patient ').' '.__('messages.doctor.dob').':' }}</div>
        <div class="fs-5 text-gray-800">{{ (!empty($medicineBill->patient->patientUser->dob)) ? \Carbon\Carbon::parse($medicineBill->patient->patientUser->dob)->translatedFormat('jS M, Y') : __('messages.common.n/a') }}</div>
    </div>

    <div class="col-sm-3">
        <div class="pb-2 fs-5 text-gray-600">{{ __('messages.web.created_at').':' }}</div>
        <div class="fs-5 text-gray-800">{{ $medicineBill->created_at->diffForHumans() }}</div>
    </div>
    <div class="col-sm-3">
        <div class="pb-2 fs-5 text-gray-600">{{ __('messages.patient.last_updated').':' }}</div>
        <div class="fs-5 text-gray-800">{{ $medicineBill->created_at->diffForHumans() }}</div>
    </div>
</div>
<div class="flex-grow-1 table-responsive">
    <table class="table border-bottom-2 ">
        <thead>
            <tr class="border-bottom fs-6 fw-bolder text-muted">
                <th class="min-w-175px pb-2">{{ __('messages.medicine_bills.item_name') }}</th>
                <th class="min-w-70px text-end pb-2">{{ __('messages.medicine.quantity') }}</th>
                <th class="min-w-70px text-end pb-2 d-none">{{ __('messages.medicine_bills.price') }}</th>
                <th class="min-w-80px text-end pb-2 d-none">{{ __('messages.purchase_medicine.tax') }}</th>
                <th class="min-w-80px text-end pb-2 d-none">{{ __('messages.purchase_medicine.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicineBill->saleMedicine as $index => $saleMedicine)
            <tr class="text-gray-700 fs-5 text-end">
                <td class="d-flex align-items-center pt-6 text-gray-700">{{ $saleMedicine->medicine->name }}</td>
                <td class="pt-6 text-gray-700">{{ $saleMedicine->sale_quantity }}</td>
                <td class="pt-6 text-gray-700 d-none">
                    {{ getCurrencyFormat(getCurrencyCode(),$saleMedicine->sale_price ) }}
                </td>
                <td class="pt-6 text-dark fw-boldest d-none">
                    {{ $saleMedicine->tax.'%' }}
                </td>
                <td class="pt-6 text-dark fw-boldest d-none">
                    {{ getCurrencyFormat(getCurrencyCode(),$saleMedicine->sale_price * $saleMedicine->sale_quantity) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>