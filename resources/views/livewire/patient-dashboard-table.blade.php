<div class="premium-role-dashboard premium-patient-dashboard">
    <style>
        .premium-patient-dashboard {
            --patient-bg: #fffaf3;
            --patient-surface: #ffffff;
            --patient-heading: #4b2a1a;
            --patient-muted: #7f6254;
            --patient-border: rgba(138, 82, 52, 0.15);
            background: radial-gradient(circle at 96% 8%, #ffe8cc 0%, transparent 52%), var(--patient-bg);
            border-radius: 24px;
            padding: 1.5rem;
            font-family: "Sora", "Poppins", "Segoe UI", sans-serif;
        }

        .premium-patient-dashboard .premium-hero-card {
            background: linear-gradient(130deg, #8b4f2a 0%, #b86a35 50%, #e99a55 100%);
            color: #fff;
            border-radius: 24px;
            padding: 1.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(118, 69, 39, 0.25);
        }

        .premium-patient-dashboard .premium-hero-card::before {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 999px;
            right: -54px;
            top: -60px;
            background: rgba(255, 255, 255, 0.17);
            animation: patientFloat 9s ease-in-out infinite;
        }

        .premium-patient-dashboard .premium-hero-copy,
        .premium-patient-dashboard .premium-profile {
            position: relative;
            z-index: 1;
        }

        .premium-patient-dashboard .premium-kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.44);
            background: rgba(52, 28, 16, 0.2);
            padding: 0.3rem 0.8rem;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.66rem;
        }

        .premium-patient-dashboard .premium-hero-copy h2 {
            margin: 0;
            font-size: 1.62rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(50, 27, 17, 0.35);
        }

        .premium-patient-dashboard .premium-hero-copy p {
            margin: 0.35rem 0 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
        }

        .premium-patient-dashboard .premium-chip-row {
            margin-top: 0.88rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.48rem;
        }

        .premium-patient-dashboard .premium-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.36);
            background: rgba(60, 33, 20, 0.2);
            padding: 0.3rem 0.76rem;
            font-size: 0.71rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .premium-patient-dashboard .premium-profile {
            background: rgba(62, 35, 22, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.26);
            border-radius: 16px;
            padding: 0.9rem;
            min-width: 220px;
        }

        .premium-patient-dashboard .premium-avatar {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, 0.72);
            margin-bottom: 0.45rem;
        }

        .premium-patient-dashboard .premium-profile-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.82);
        }

        .premium-patient-dashboard .premium-profile-value {
            margin-top: 0.2rem;
            font-size: 1.03rem;
            font-weight: 700;
            color: #ffffff;
        }

        .premium-patient-dashboard .premium-info-grid {
            margin-top: 1rem;
        }

        .premium-patient-dashboard .premium-info-card {
            height: 100%;
            background: var(--patient-surface);
            border: 1px solid var(--patient-border);
            border-radius: 18px;
            padding: 0.9rem;
            box-shadow: 0 12px 30px rgba(130, 83, 46, 0.08);
        }

        .premium-patient-dashboard .premium-info-label {
            display: block;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--patient-muted);
            margin-bottom: 0.3rem;
        }

        .premium-patient-dashboard .premium-info-value {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--patient-heading);
            word-break: break-word;
        }

        @keyframes patientFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-9px);
            }
        }

        @media (max-width: 767.98px) {
            .premium-patient-dashboard {
                padding: 1rem;
                border-radius: 20px;
            }

            .premium-patient-dashboard .premium-hero-card {
                padding: 1.15rem;
                border-radius: 20px;
            }

            .premium-patient-dashboard .premium-profile {
                min-width: 0;
                width: 100%;
            }

            .premium-patient-dashboard .premium-hero-copy h2 {
                font-size: 1.38rem;
            }
        }
    </style>

    @php
    $user = getLogInUser();
    $patient = optional($user->patient);
    $avatar = $user->hasRole('patient') ? $patient->profile : $user->profile_image;
    $patientIdentifier = $user->university_id_number ?: ($patient->patient_unique_id ?: __('messages.common.n/a'));
    @endphp

    <section class="premium-hero-card">
        <div class="premium-hero-copy">
            <span class="premium-kicker">{{ __('messages.welcome_back') }}</span>
            <h2>{{ $user->full_name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="premium-chip-row">
                <span class="premium-chip">{{ __('messages.dashboard') }}</span>
                <span class="premium-chip">Patient Care Portal</span>
                <span class="premium-chip">{{ now()->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="premium-profile">
            <img class="premium-avatar" src="{{ $avatar }}" alt="Profile image">
            <div class="premium-profile-title">Patient ID</div>
            <div class="premium-profile-value">{{ $patientIdentifier }}</div>
        </div>
    </section>

    <div class="row g-3 premium-info-grid">
        <div class="col-md-4 col-sm-6">
            <article class="premium-info-card">
                <span class="premium-info-label">University ID Number</span>
                <p class="premium-info-value">{{ $patientIdentifier }}</p>
            </article>
        </div>
        <div class="col-md-4 col-sm-6">
            <article class="premium-info-card">
                <span class="premium-info-label">{{ __('messages.user.phone') }}</span>
                <p class="premium-info-value">{{ $user->contact ?: __('messages.common.n/a') }}</p>
            </article>
        </div>
        <div class="col-md-4 col-sm-12">
            <article class="premium-info-card">
                <span class="premium-info-label">Account Type</span>
                <p class="premium-info-value">Patient</p>
            </article>
        </div>
    </div>
</div>