<div class="premium-role-dashboard premium-staff-dashboard">
    <style>
        .premium-staff-dashboard {
            --staff-surface: #ffffff;
            --staff-bg: #f4fbf8;
            --staff-heading: #1d3531;
            --staff-muted: #506c66;
            --staff-border: rgba(39, 93, 79, 0.15);
            background: radial-gradient(circle at 100% 0%, #d9fff1 0%, transparent 50%), var(--staff-bg);
            border-radius: 24px;
            padding: 1.5rem;
            font-family: "Sora", "Poppins", "Segoe UI", sans-serif;
        }

        .premium-staff-dashboard .premium-hero-card {
            background: linear-gradient(128deg, #0f4d4b 0%, #187069 55%, #24a091 100%);
            border-radius: 24px;
            color: #ffffff;
            padding: 1.6rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
            box-shadow: 0 20px 48px rgba(12, 80, 72, 0.24);
        }

        .premium-staff-dashboard .premium-hero-card::before {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            border-radius: 999px;
            right: -60px;
            top: -66px;
            background: rgba(255, 255, 255, 0.14);
            animation: staffPulse 10s ease-in-out infinite;
        }

        .premium-staff-dashboard .premium-hero-copy,
        .premium-staff-dashboard .premium-profile-tile {
            position: relative;
            z-index: 1;
        }

        .premium-staff-dashboard .premium-kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            background: rgba(6, 33, 30, 0.2);
            padding: 0.31rem 0.82rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 0.72rem;
            margin-bottom: 0.7rem;
        }

        .premium-staff-dashboard .premium-hero-copy h2 {
            margin: 0;
            font-size: 1.62rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(6, 30, 28, 0.35);
        }

        .premium-staff-dashboard .premium-hero-copy p {
            margin: 0.35rem 0 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
        }

        .premium-staff-dashboard .premium-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.48rem;
            margin-top: 0.9rem;
        }

        .premium-staff-dashboard .premium-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.36);
            background: rgba(10, 40, 36, 0.22);
            padding: 0.31rem 0.76rem;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .premium-staff-dashboard .premium-profile-tile {
            background: rgba(8, 38, 35, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            min-width: 220px;
            padding: 0.9rem;
            text-align: left;
        }

        .premium-staff-dashboard .premium-avatar {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.72);
            margin-bottom: 0.45rem;
        }

        .premium-staff-dashboard .premium-profile-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.8);
        }

        .premium-staff-dashboard .premium-profile-value {
            margin-top: 0.2rem;
            font-weight: 700;
            font-size: 1.02rem;
            color: #ffffff;
        }

        .premium-staff-dashboard .premium-stat-card {
            background: var(--staff-surface);
            border: 1px solid var(--staff-border);
            border-radius: 20px;
            padding: 1.05rem;
            box-shadow: 0 14px 30px rgba(20, 69, 62, 0.1);
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            height: 100%;
            transition: transform 0.25s ease;
            animation: staffReveal 0.5s ease both;
        }

        .premium-staff-dashboard .premium-stat-card:hover {
            transform: translateY(-4px);
        }

        .premium-staff-dashboard .premium-stat-label {
            margin-bottom: 0.32rem;
            font-size: 0.78rem;
            color: var(--staff-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .premium-staff-dashboard .premium-stat-value {
            margin: 0;
            font-size: 1.75rem;
            line-height: 1;
            color: var(--staff-heading);
            font-weight: 700;
        }

        .premium-staff-dashboard .premium-stat-meta {
            margin-top: 0.35rem;
            color: #64807a;
            font-size: 0.8rem;
        }

        .premium-staff-dashboard .premium-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .premium-staff-dashboard .premium-icon-doctor {
            color: #0869a0;
            background: linear-gradient(140deg, #d8eeff 0%, #b6ddfd 100%);
        }

        .premium-staff-dashboard .premium-icon-patient {
            color: #177a44;
            background: linear-gradient(140deg, #ddfbe8 0%, #bef2d2 100%);
        }

        .premium-staff-dashboard .premium-icon-new {
            color: #986100;
            background: linear-gradient(140deg, #fff0cf 0%, #ffe2a2 100%);
        }

        @keyframes staffPulse {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes staffReveal {
            from {
                opacity: 0;
                transform: translateY(9px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767.98px) {
            .premium-staff-dashboard {
                padding: 1rem;
                border-radius: 20px;
            }

            .premium-staff-dashboard .premium-hero-card {
                padding: 1.15rem;
                border-radius: 20px;
            }

            .premium-staff-dashboard .premium-profile-tile {
                min-width: 0;
                width: 100%;
            }

            .premium-staff-dashboard .premium-hero-copy h2 {
                font-size: 1.38rem;
            }
        }
    </style>

    @php
    $user = getLogInUser();
    @endphp

    <section class="premium-hero-card">
        <div class="premium-hero-copy">
            <span class="premium-kicker">{{ __('messages.welcome_back') }}</span>
            <h2>{{ $user->full_name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="premium-chip-row">
                <span class="premium-chip">{{ __('messages.dashboard') }}</span>
                <span class="premium-chip">Staff Operations Hub</span>
                <span class="premium-chip">{{ now()->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="premium-profile-tile">
            <img class="premium-avatar" src="{{ $user->profile_image }}" alt="Profile image">
            <div class="premium-profile-title">Focus for Today</div>
            <div class="premium-profile-value">Support {{ $totalRegisteredPatientCount }} new registrations</div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.common.active') . ' ' . __('messages.doctors') }}</p>
                    <h3 class="premium-stat-value">{{ $totalDoctorCount }}</h3>
                    <p class="premium-stat-meta">Doctors currently available</p>
                </div>
                <span class="premium-stat-icon premium-icon-doctor"><i class="fas fa-user-md"></i></span>
            </article>
        </div>

        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.admin_dashboard.total_patients') }}</p>
                    <h3 class="premium-stat-value">{{ $totalPatientCount }}</h3>
                    <p class="premium-stat-meta">Patients to support in records and queues</p>
                </div>
                <span class="premium-stat-icon premium-icon-patient"><i class="fas fa-users"></i></span>
            </article>
        </div>

        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.admin_dashboard.today_registered_patients') }}</p>
                    <h3 class="premium-stat-value">{{ $totalRegisteredPatientCount }}</h3>
                    <p class="premium-stat-meta">Fresh registrations processed today</p>
                </div>
                <span class="premium-stat-icon premium-icon-new"><i class="fas fa-user-plus"></i></span>
            </article>
        </div>
    </div>
</div>