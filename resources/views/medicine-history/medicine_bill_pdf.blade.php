<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="icon" href="{{ asset('web/img/hms-saas-favicon.ico') }}" type="image/png">
    <title>{{ __('messages.medicine_bills.medicine_bill') }}</title>
    <link href="{{ asset('assets/css/bill-pdf.css') }}" rel="stylesheet" type="text/css" />
    <style>
        * {
            font-family: DejaVu Sans, Arial, "Helvetica", Arial, "Liberation Sans", sans-serif;
        }
    </style>
</head>

<body>
    <table width="100%">
        <tr>
            <td class="header-left">
                <div class="main-heading">{{ __('messages.medicine_bills.medicine_bill') }}</div>
            </td>
            <td class="header-right">
                <div class="logo">
                    <img width="100px" src="{{ asset('assets/image/norsu_logo.png') }}" alt="">
                </div>
                <div class="hospital-name">{{ $data['clinic_name'] }}</div>
                <div class="hospital-name font-color-gray">{{ $data['address_one'] }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="address">
                    <tr>
                        <td colspan="2">
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_id') }}:</span>
                            #{{ $medicineBill->history_number }}
                            <br>
                            <span class="font-weight-bold patient-detail-heading">{{ __('messages.medicine_bills.bill_date') }}:</span>
                            {{ \Carbon\Carbon::parse($medicineBill->bill_date)->format('jS M,Y g:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"
                            class="font-weight-bold patient-detail-heading">{{ __('messages.patient.details') }}</td>
                    </tr>
                    <tr>
                        <td class="patient-details">
                            <table class="patient-detail-one">
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.doctor_appointment.patient') }}:</td>
                                    <td>{{ $medicineBill->patient->user->full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">{{ __('auth.email') }}:</td>
                                    <td>{{ $medicineBill->patient->user->email }}</td>
                                </tr>
                                @if (!empty($medicineBill->patient->user->contact))
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.medicine_bills.cell_no') }}:</td>
                                    <td>{{ $medicineBill->patient->user->contact }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.user.gender') }}:</td>
                                    <td>{{
        $medicineBill->patient->user->gender == 1
            ? __('messages.staff.male')
            : ($medicineBill->patient->user->gender == 2
                ? __('messages.staff.female')
                : __('messages.common.n/a'))
    }}</td>
                                </tr>
                                @if (!empty($medicineBill->patient->user->dob))
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.doctor.dob') }}:</td>
                                    <td>{{ \DateTime::createFromFormat('Y-m-d',  $medicineBill->patient->user->dob)->format('jS M, Y g:i A') }}</td>
                                </tr>
                                @endif
                                @if (!empty($medicineBill->doctor))
                                <tr>
                                    <td class="font-weight-bold">{{ __('messages.doctor.doctor') }}:</td>
                                    <td>{{ optional(optional($medicineBill->doctor)->user)->full_name ?? __('messages.common.n/a') }}</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    <tr>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.medicine_bills.item_name') }}</th>
                            <th class="number-align">{{ __('messages.medicine.quantity') }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($medicineBill->saleMedicine) && !empty($medicineBill->saleMedicine))
                        @foreach ($medicineBill->saleMedicine as $index => $saleMedicine)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $saleMedicine->medicine->name }}
                            </td>
                            <td class="number-align">{{ $saleMedicine->sale_quantity }}</td>

                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>