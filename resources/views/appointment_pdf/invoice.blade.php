<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{ getAppName() }}</title>
    <style>
        @font-face {
            font-family: 'Poppins';
            src: url('/theme/fonts/Poppins-Regular.ttf') format(truetype);
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Inter', 'Segoe UI', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #2d3748;
            background: #ffffff;
            padding: 10px;
        }

        .document {
            max-width: 190mm;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 30px 40px 25px;
            position: relative;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .clinic-info h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 2px;
            letter-spacing: -0.5px;
        }

        .clinic-info .tagline {
            font-size: 13px;
            color: #718096;
            font-weight: 400;
        }

        .document-type {
            text-align: right;
        }

        .document-type h2 {
            font-size: 16px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .document-type .date {
            font-size: 12px;
            color: #a0aec0;
        }

        .content {
            padding: 30px 40px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 35px;
        }

        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            position: relative;
            transition: all 0.2s ease;
        }

        .detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 12px 12px 0 0;
        }

        .detail-card h3 {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-card h3::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .detail-row {
            margin-bottom: 12px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-size: 10px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 10px;
            color: #2d3748;
            font-weight: 500;
            word-break: break-word;
        }

        .appointment-section {
            background: #f7fafc;
            border-radius: 5px;
            padding: 6px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .appointment-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .appointment-title h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .appointment-title .subtitle {
            font-size: 12px;
            color: #718096;
        }

        .appointment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .appointment-item {
            background: #ffffff;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .appointment-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .appointment-item .icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .appointment-item .label {
            font-size: 11px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .appointment-item .value {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
        }

        .description-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            border-left: 4px solid #48bb78;
        }

        .description-section h3 {
            font-size: 15px;
            font-weight: 600;
            color: #38a169;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description-section h3::before {
            content: '📝';
            font-size: 16px;
        }

        .description-section p {
            color: #4a5568;
            line-height: 1.6;
            font-size: 13px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 40px;
            text-align: center;
        }

        .footer .divider {
            width: 100px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e0, transparent);
            margin: 0 auto 15px;
        }

        .footer-text {
            font-size: 11px;
            color: #718096;
            font-weight: 400;
        }

        @media print {
            body {
                padding: 0;
                background: #ffffff;
            }

            .document {
                box-shadow: none;
                /* max-width: 100%; */
            }
        }
    </style>
</head>

<body>
    <div class="document">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo">
                        <img src="{{ getAppLogo() }}" alt="Clinic Logo" />
                    </div>
                    <div class="clinic-info">
                        <h1>{{ getAppName() }}</h1>
                        <div class="tagline">Prioritizing your wellness at NORSU Clinic.</div>
                    </div>
                </div>
                <div class="document-type">
                    <h2>Visit Record</h2>
                    <div class="date">{{ \Carbon\Carbon::now()->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Details Grid -->
            <div class="details-grid">
                <div class="detail-card">
                    <h3>Attending Physician</h3>
                    <div class="detail-row">
                        <div class="detail-label">Doctor Name</div>
                        <div class="detail-value">{{ $datas->doctor->user->full_name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">{{ $datas->doctor->user->email }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Specialty</div>
                        <div class="detail-value">{{ $datas->services->name }}</div>
                    </div>
                </div>

                <div class="detail-card">
                    <h3>Patient Information</h3>
                    <div class="detail-row">
                        <div class="detail-label">Patient Name</div>
                        <div class="detail-value">{{ $datas->patient->user->full_name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">{{ $datas->patient->user->email }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Visit ID</div>
                        <div class="detail-value">#{{ str_pad($datas->id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>
            </div>

            <!-- Appointment Section -->
            <div class="appointment-section">
                <div class="appointment-title">
                    <h3>Appointment Schedule</h3>
                    <div class="subtitle">Visit date and time details</div>
                </div>
                <div class="appointment-grid">
                    <div class="appointment-item">
                        <div class="icon">📅</div>
                        <div class="label">Visit Date</div>
                        <div class="value">{{\Carbon\Carbon::parse($datas->date)->format('M d, Y')}}</div>
                    </div>
                    <div class="appointment-item">
                        <div class="icon">🕐</div>
                        <div class="label">Time Slot</div>
                        <div class="value">
                            {{ $datas->from_time }} {{ $datas->from_time_type }} - {{ $datas->to_time }} {{
								$datas->to_time_type }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if ($datas->description)
            <div class="description-section">
                <h3>Clinical Notes</h3>
                <p>{!! nl2br($datas->description) !!}</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="divider"></div>
            <div class="footer-text">
                This document was generated electronically on {{ \Carbon\Carbon::now()->format('F j, Y
					\a\t g:i A') }}
            </div>
        </div>
    </div>
</body>

</html>
