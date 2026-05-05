<div class="premium-role-dashboard premium-admin-dashboard">
    <style>
        .premium-role-dashboard {
            font-family: "Sora", "Poppins", "Segoe UI", sans-serif;
        }

        .premium-admin-dashboard {
            --premium-bg: #f5f8ff;
            --premium-surface: #ffffff;
            --premium-heading: #0f2742;
            --premium-muted: #4b6078;
            --premium-accent: #0a7ea4;
            --premium-shadow: 0 20px 55px rgba(11, 36, 64, 0.15);
            --premium-border: rgba(16, 73, 117, 0.14);
            background: radial-gradient(circle at top right, #dff3ff 0%, transparent 48%), var(--premium-bg);
            border-radius: 24px;
            padding: 1.5rem;
        }

        .premium-admin-dashboard .premium-hero-card {
            background: linear-gradient(122deg, #12365a 0%, #1a527d 52%, #1797b8 100%);
            border-radius: 24px;
            color: #ffffff;
            box-shadow: var(--premium-shadow);
            padding: 1.75rem;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .premium-admin-dashboard .premium-hero-card::before,
        .premium-admin-dashboard .premium-hero-card::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .premium-admin-dashboard .premium-hero-card::before {
            width: 190px;
            height: 190px;
            right: -38px;
            top: -56px;
            background: rgba(255, 255, 255, 0.16);
            animation: premiumFloat 8s ease-in-out infinite;
        }

        .premium-admin-dashboard .premium-hero-card::after {
            width: 130px;
            height: 130px;
            bottom: -44px;
            left: 28%;
            background: rgba(255, 255, 255, 0.09);
            animation: premiumFloat 11s ease-in-out infinite;
        }

        .premium-admin-dashboard .premium-hero-copy,
        .premium-admin-dashboard .premium-hero-user {
            position: relative;
            z-index: 1;
        }

        .premium-admin-dashboard .premium-kicker {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.44);
            border-radius: 999px;
            padding: 0.32rem 0.88rem;
            font-size: 0.72rem;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            background: rgba(11, 26, 45, 0.17);
        }

        .premium-admin-dashboard .premium-hero-copy h2 {
            margin: 0;
            font-size: 1.72rem;
            font-weight: 700;
            line-height: 1.25;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(8, 20, 36, 0.35);
        }

        .premium-admin-dashboard .premium-hero-copy p {
            margin: 0.42rem 0 0;
            font-size: 0.96rem;
            color: rgba(255, 255, 255, 0.86);
        }

        .premium-admin-dashboard .premium-chip-row {
            margin-top: 0.95rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.48rem;
        }

        .premium-admin-dashboard .premium-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 0.3rem 0.78rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(13, 35, 57, 0.2);
        }

        .premium-admin-dashboard .premium-profile-tile {
            background: rgba(9, 25, 42, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 18px;
            padding: 1rem;
            min-width: 220px;
            backdrop-filter: blur(2px);
        }

        .premium-admin-dashboard .premium-avatar {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            border: 2px solid rgba(255, 255, 255, 0.72);
            object-fit: cover;
            margin-bottom: 0.5rem;
            box-shadow: 0 12px 24px rgba(4, 16, 29, 0.28);
        }

        .premium-admin-dashboard .premium-profile-title {
            font-size: 0.74rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .premium-admin-dashboard .premium-profile-value {
            font-size: 1.07rem;
            font-weight: 700;
            color: #ffffff;
        }

        .premium-admin-dashboard .premium-stat-card {
            background: var(--premium-surface);
            border: 1px solid var(--premium-border);
            border-radius: 20px;
            height: 100%;
            box-shadow: 0 14px 36px rgba(13, 45, 77, 0.1);
            padding: 1.1rem;
            display: flex;
            justify-content: space-between;
            gap: 0.85rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            animation: premiumReveal 0.55s ease both;
        }

        .premium-admin-dashboard .premium-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 34px rgba(11, 42, 71, 0.14);
        }

        .premium-admin-dashboard .premium-stat-label {
            margin-bottom: 0.35rem;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            color: var(--premium-muted);
            text-transform: uppercase;
        }

        .premium-admin-dashboard .premium-stat-value {
            margin: 0;
            color: var(--premium-heading);
            font-weight: 700;
            font-size: 1.85rem;
            line-height: 1;
        }

        .premium-admin-dashboard .premium-stat-meta {
            margin-top: 0.35rem;
            font-size: 0.81rem;
            color: #6a7a8e;
        }

        .premium-admin-dashboard .premium-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .premium-admin-dashboard .premium-icon-doctor {
            color: #1044a9;
            background: linear-gradient(140deg, #dce7ff 0%, #c0d7ff 100%);
        }

        .premium-admin-dashboard .premium-icon-patient {
            color: #0c8d61;
            background: linear-gradient(140deg, #daf9ed 0%, #b8f0dc 100%);
        }

        .premium-admin-dashboard .premium-icon-queue {
            color: #006f8a;
            background: linear-gradient(140deg, #def8ff 0%, #b7ecf9 100%);
        }

        .premium-admin-dashboard .premium-icon-new {
            color: #9b6a00;
            background: linear-gradient(140deg, #fff3d8 0%, #ffe5ab 100%);
        }

        @keyframes premiumFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes premiumReveal {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767.98px) {
            .premium-admin-dashboard {
                padding: 1rem;
                border-radius: 20px;
            }

            .premium-admin-dashboard .premium-hero-card {
                padding: 1.2rem;
                border-radius: 20px;
            }

            .premium-admin-dashboard .premium-hero-copy h2 {
                font-size: 1.4rem;
            }

            .premium-admin-dashboard .premium-profile-tile {
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    @php
    $user = getLogInUser();
    $avatar = $user->hasRole('patient') ? optional($user->patient)->profile : $user->profile_image;
    @endphp

    <section class="premium-hero-card">
        <div class="premium-hero-copy">
            <span class="premium-kicker">{{ __('messages.welcome_back') }}</span>
            <h2>{{ $user->full_name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="premium-chip-row">
                <span class="premium-chip">{{ __('messages.dashboard') }}</span>
                <span class="premium-chip">Administrator Command Center</span>
                <span class="premium-chip">{{ now()->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="premium-hero-user">
            <div class="premium-profile-tile">
                <img class="premium-avatar" src="{{ $avatar }}" alt="Profile image">
                <div class="premium-profile-title">Live Operations</div>
                <div class="premium-profile-value">{{ $todayQueueCount }} patients in active queue</div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-3 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.common.active') . ' ' . __('messages.doctors') }}</p>
                    <h3 class="premium-stat-value">{{ $totalDoctorCount }}</h3>
                    <p class="premium-stat-meta">Available physicians in the clinic</p>
                </div>
                <span class="premium-stat-icon premium-icon-doctor"><i class="fas fa-user-md"></i></span>
            </article>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.admin_dashboard.total_patients') }}</p>
                    <h3 class="premium-stat-value">{{ $totalPatientCount }}</h3>
                    <p class="premium-stat-meta">Total registered patient records</p>
                </div>
                <span class="premium-stat-icon premium-icon-patient"><i class="fas fa-users"></i></span>
            </article>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">Patient Queue Today</p>
                    <h3 class="premium-stat-value">{{ $todayQueueCount }}</h3>
                    <p class="premium-stat-meta">Waiting and in progress consultations</p>
                </div>
                <span class="premium-stat-icon premium-icon-queue"><i class="fas fa-users-line"></i></span>
            </article>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">{{ __('messages.admin_dashboard.today_registered_patients') }}</p>
                    <h3 class="premium-stat-value">{{ $totalRegisteredPatientCount }}</h3>
                    <p class="premium-stat-meta">New patients added today</p>
                </div>
                <span class="premium-stat-icon premium-icon-new"><i class="fas fa-user-plus"></i></span>
            </article>
        </div>
    </div>
</div>