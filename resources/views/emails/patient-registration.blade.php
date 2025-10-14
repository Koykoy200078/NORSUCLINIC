<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NORSU Clinic</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #007bff;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .logo img {
            max-height: 60px;
            width: auto;
            margin-right: 10px;
            vertical-align: middle;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
        }

        .content {
            padding: 20px 0;
        }

        .welcome-message {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .patient-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #007bff;
            font-weight: 600;
        }

        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #eee;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }

        .contact-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset(getAppLogo()) }}" alt="{{ getSettingValue('clinic_name') ?: 'NORSU CLINIC' }} Logo" style="max-height: 60px; width: auto; margin-right: 10px; vertical-align: middle;">
                {{ getSettingValue('clinic_name') ?: 'NORSU CLINIC' }}
            </div>
            <div class="subtitle">
                @if(isset($slider) && $slider && $slider->title)
                {{ $slider->title }}
                @else
                Negros Oriental State University Clinic
                @endif
            </div>
        </div>

        <div class="content">
            <div class="welcome-message">
                🎉 Welcome to {{ getSettingValue('clinic_name') ?: 'NORSU Clinic' }}!
            </div>

            @if(isset($slider) && $slider && $slider->short_description)
            <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 15px 0; font-style: italic; color: #1976d2; text-align: center;">
                "{{ $slider->short_description }}"
            </div>
            @endif

            <p>Dear <strong>{{ $patientName }}</strong>,</p>

            <p>Congratulations! You have been successfully registered with {{ getSettingValue('clinic_name') ?: 'NORSU Clinic' }}. We're excited to have you as part of our healthcare community.</p>

            <div class="patient-info">
                <h3 style="margin-top: 0; color: #007bff;">📋 Your Registration Details</h3>
                <div class="info-row">
                    <span class="info-label" style="margin-right: 5px;">👤 Patient Name: </span>
                    <span class="info-value">{{ $patientName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label" style="margin-right: 5px;">🆔 Patient ID: </span>
                    <span class="info-value">{{ $patientId }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label" style="margin-right: 5px;">📧 Email Address: </span>
                    <span class="info-value">{{ $email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label" style="margin-right: 5px;">📅 Registration Date: </span>
                    <span class="info-value">{{ $registrationDate }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label" style="margin-right: 5px;">✅ Email Status: </span>
                    <span class="info-value">Verified</span>
                </div>
            </div>

            <div class="contact-info">
                <h3 style="margin-top: 0; color: #007bff;">📞 Contact Information</h3>
                <p><strong>📍 Address:</strong> {{ getSettingValue('address_one') }}</p>
                @if(getSettingValue('address_two'))
                <p><strong>📍 Address 2:</strong> {{ getSettingValue('address_two') }}</p>
                @endif
                <p><strong>☎️ Phone:</strong> {{ getSettingValue('landline_no') }}</p>
                <p><strong>📱 Mobile:</strong> {{ getSettingValue('contact_no') }}</p>
                @if(getSettingValue('email'))
                <p><strong>📧 Email:</strong> {{ getSettingValue('email') }}</p>
                @endif
            </div>

            <h3 style="color: #007bff;">🩺 What's Next?</h3>
            <ul style="line-height: 1.8;">
                <li><strong>📄 Access Records:</strong> View your medical history and documents</li>
                <li><strong>💊 Prescription Management:</strong> Track your medications and prescriptions</li>
                <li><strong>📞 Contact Support:</strong> Reach out to our medical staff for assistance</li>
            </ul>

            <p style="margin-top: 25px;">Your account is now active and ready to use. Please keep your Patient ID <strong>({{ $patientId }})</strong> for future reference when visiting the clinic or booking appointments.</p>

            <p>If you have any questions or need assistance, please don't hesitate to contact our clinic staff.</p>

            <p style="margin-top: 25px;">
                <strong>Welcome to {{ getSettingValue('clinic_name') ?: 'NORSU Clinic' }} family!</strong><br>
                @if(isset($slider) && $slider && $slider->short_description)
                <em>{{ $slider->short_description }} 🏥💙</em>
                @else
                <em>Your health is our priority. 🏥💙</em>
                @endif
            </p>
        </div>

        <div class="footer">
            <p><strong>{{ getSettingValue('clinic_name') ?: 'NORSU Clinic' }}</strong> - Negros Oriental State University</p>
            <p>{{ getSettingValue('address_one') ?: 'University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City' }}</p>
            <p>📞 {{ getSettingValue('landline_no') ?: '225-9400 (Local # 188)' }} | 📱 {{ getSettingValue('contact_no') ?: '09263829484' }}</p>
            @if(getSettingValue('email'))
            <p>📧 {{ getSettingValue('email') }}</p>
            @endif
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>

</html>
