<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('messages.common.prescription_report') }}</title>
    <style>
        body {
            background: #e0e0e0;
            font-family: serif, Georgia, 'Times New Roman', Times, serif;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
            margin: 0;
        }

        .prescription {
            background: white;
            padding: 25px 30px;
            width: 490px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .rx-logo {
            font-weight: 900;
            font-size: 140px;
            line-height: 1;
            margin-top: 0;
            user-select: none;
            color: black;
            text-align: center;
            display: table-cell;
            vertical-align: top;
            width: 40%;
        }

        .patient-details {
            display: table-cell;
            vertical-align: top;
            width: 60%;
            padding-left: 20px;
        }

        .patient-info {
            margin-bottom: 30px;
            font-size: 14px;
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .patient-row {
            margin-bottom: 8px;
            display: flex;
            align-items: baseline;
        }

        .patient-row label {
            display: inline-block;
            width: 80px;
            font-weight: normal;
            font-size: 14px;
        }

        .patient-row .underline {
            border-bottom: 1px solid black;
            max-width: 260px;
            height: 14px;
            position: relative;
            margin-left: 15px;
        }

        .patient-data {
            position: absolute;
            bottom: 2px;
            left: 0;
            font-size: 12px;
            background: white;
            padding-right: 8px;
            display: inline-block;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .address-row {
            margin-bottom: 12px;
            display: flex;
            align-items: baseline;
        }

        .address-row label {
            display: inline-block;
            width: 80px;
            font-weight: normal;
            font-size: 14px;
            flex-shrink: 0;
        }

        .address-container {
            width: 260px;
            margin-left: 15px;
        }

        .address-line {
            border-bottom: 1px solid black;
            height: 18px;
            position: relative;
            margin-bottom: 0px;
        }

        .address-data {
            position: absolute;
            bottom: 2px;
            left: 0;
            font-size: 12px;
            background: white;
            padding-right: 8px;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .divider {
            border-top: 3px double black;
            margin: 20px 0 25px 0;
        }

        .prescription-label {
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 25px;
        }

        .prescription-content {
            min-height: 160px;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .medicine-item {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .medicine-name {
            font-weight: bold;
        }

        .problem-section,
        .advice-section {
            margin: 20px 0;
            font-size: 14px;
        }

        .problem-section h4,
        .advice-section h4 {
            margin: 0 0 8px 0;
            font-size: 15px;
            font-weight: bold;
        }

        .bottom-divider {
            border-top: 3px double black;
            margin: 40px 0 25px 0;
        }

        .signature-section {
            display: flex;
            flex-direction: row;
            align-items: baseline;
            font-size: 16px;
            margin-top: 30px;
            width: 100%;
        }

        .signature-item {
            display: inline-flex;
            align-items: baseline;
            white-space: nowrap;
        }

        .signature-item label {
            margin-right: 15px;
            font-weight: normal;
        }

        .signature-item .sig-line {
            border-bottom: 1px solid black;
            width: 120px;
            height: 20px;
            position: relative;
        }

        .signature-value {
            position: absolute;
            bottom: 2px;
            left: 0;
            font-size: 14px;
            background: white;
            padding-right: 10px;
        }
    </style>
</head>

<body>
    <section class="prescription" aria-label="Prescription form">
        <div class="patient-info">
            <div class="rx-logo" aria-hidden="true">Rx</div>
            <div class="patient-details">
                <div class="patient-row">
                    <label>Patient name</label>
                    <div class="underline">
                        <span class="patient-data">{{ $patientInfo['name'] }}</span>
                    </div>
                </div>
                <div class="address-row">
                    <label>Address</label>
                    <div class="address-container">
                        @php
                        $address = $patientInfo['address']['address1'];
                        $maxLength = 35; // Approximate characters per line
                        $addressLines = [];

                        if (strlen($address) <= $maxLength) {
                            $addressLines[]=$address;
                            } else {
                            // Split address into multiple lines
                            $words=explode(' ', $address);
                                $currentLine = '';
                                
                                foreach ($words as $word) {
                                    if (strlen($currentLine . ' ' . $word) <= $maxLength) {
                                        $currentLine .= ($currentLine ? ' ' : '') . $word;
                                    } else {
                                        if ($currentLine) {
                                            $addressLines[] = $currentLine;
                                            $currentLine = $word;
                                        } else {
                                            $addressLines[] = $word;
                                        }
                                    }
                                }
                                
                                if ($currentLine) {
                                    $addressLines[] = $currentLine;
                                }
                            }
                        @endphp
                        
                        @foreach ($addressLines as $line)
                        <div class="address-line">
                            <span class="address-data">{{ $line }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if($patientInfo["age"])
                            <div class="patient-row">
                            <label>Age</label>
                            <div class="underline">
                                <span class="patient-data">{{ $patientInfo['age'] }}</span>
                            </div>
                    </div>
                    @endif
                    <div class="patient-row">
                        <label>Date</label>
                        <div class="underline">
                            <span class="patient-data">{{ $patientInfo['date'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="prescription-label">Prescription:</div>

            <div class="prescription-content">
                <!-- Empty space for handwritten prescriptions -->
                @if (isset($prescriptionContent[' problem']))
                            <div style="margin-bottom: 15px">
                            <strong>Problem:</strong><br />
                            {{ $prescriptionContent['problem'] }}
                    </div>
                    @endif @if (isset($prescriptionContent['medications']))
                    <div style="margin-bottom: 15px">
                        <strong>Medications:</strong><br />
                        @foreach ($prescriptionContent['medications'] as $medication) {{ $medication['name'] }} -
                        {{ $medication['dosage'] }} {{ $medication['timing'] }} for {{ $medication['duration']
					}}<br />
                        @endforeach
                    </div>
                    @endif @if (isset($prescriptionContent['tests']))
                    <div style="margin-bottom: 15px">
                        <strong>Tests:</strong><br />
                        {{ $prescriptionContent['tests'] }}
                    </div>
                    @endif @if (isset($prescriptionContent['advice']))
                    <div style="margin-bottom: 15px">
                        <strong>Advice:</strong><br />
                        {{ $prescriptionContent['advice'] }}
                    </div>
                    @endif
                </div>

                <div class="bottom-divider"></div>

                <div class="signature-section">
                    <div class="signature-item">
                        <label>Date:</label>
                        <div class="sig-line">
                            <span class="signature-value">{{ $signatureInfo['date'] }}</span>
                        </div>
                    </div>
                    <div class="signature-item" style="margin-left: 50px">
                        <label>Signature</label>
                        <div class="sig-line">
                            <span class="signature-value">{{ $signatureInfo['doctor_name'] }}</span>
                        </div>
                    </div>
                </div>
    </section>
</body>

</html>
