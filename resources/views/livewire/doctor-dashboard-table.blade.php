<div class="premium-role-dashboard premium-doctor-dashboard">
    <style>
        .premium-doctor-dashboard {
            --doctor-bg: #f6fbff;
            --doctor-surface: #ffffff;
            --doctor-heading: #1f2f47;
            --doctor-muted: #5d6f85;
            --doctor-border: rgba(39, 76, 130, 0.16);
            background: radial-gradient(circle at 95% 10%, #d8eeff 0%, transparent 54%), var(--doctor-bg);
            border-radius: 24px;
            padding: 1.5rem;
            font-family: "Sora", "Poppins", "Segoe UI", sans-serif;
        }

        .premium-doctor-dashboard .premium-hero-card {
            background: linear-gradient(130deg, #184b88 0%, #1e77a5 56%, #28a6bf 100%);
            border-radius: 24px;
            color: #ffffff;
            padding: 1.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 46px rgba(17, 66, 120, 0.23);
        }

        .premium-doctor-dashboard .premium-hero-card::before,
        .premium-doctor-dashboard .premium-hero-card::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .premium-doctor-dashboard .premium-hero-card::before {
            width: 180px;
            height: 180px;
            right: -55px;
            top: -58px;
            background: rgba(255, 255, 255, 0.16);
            animation: doctorFloat 8s ease-in-out infinite;
        }

        .premium-doctor-dashboard .premium-hero-card::after {
            width: 110px;
            height: 110px;
            left: 30%;
            bottom: -34px;
            background: rgba(255, 255, 255, 0.12);
            animation: doctorFloat 11s ease-in-out infinite;
        }

        .premium-doctor-dashboard .premium-hero-copy,
        .premium-doctor-dashboard .premium-profile-tile {
            position: relative;
            z-index: 1;
        }

        .premium-doctor-dashboard .premium-kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.44);
            background: rgba(9, 34, 59, 0.24);
            padding: 0.32rem 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.72rem;
            margin-bottom: 0.66rem;
        }

        .premium-doctor-dashboard .premium-hero-copy h2 {
            margin: 0;
            font-size: 1.66rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(8, 29, 50, 0.35);
        }

        .premium-doctor-dashboard .premium-hero-copy p {
            margin: 0.34rem 0 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.94rem;
        }

        .premium-doctor-dashboard .premium-chip-row {
            margin-top: 0.92rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.48rem;
        }

        .premium-doctor-dashboard .premium-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(11, 39, 66, 0.2);
            padding: 0.31rem 0.78rem;
            font-size: 0.71rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .premium-doctor-dashboard .premium-profile-tile {
            min-width: 225px;
            background: rgba(9, 29, 50, 0.3);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.26);
            padding: 0.9rem;
        }

        .premium-doctor-dashboard .premium-avatar {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, 0.72);
            object-fit: cover;
            margin-bottom: 0.45rem;
        }

        .premium-doctor-dashboard .premium-profile-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.82);
        }

        .premium-doctor-dashboard .premium-profile-value {
            margin-top: 0.2rem;
            font-size: 1.04rem;
            font-weight: 700;
            color: #ffffff;
        }

        .premium-doctor-dashboard .premium-stat-card {
            background: var(--doctor-surface);
            border: 1px solid var(--doctor-border);
            border-radius: 20px;
            padding: 1.05rem;
            box-shadow: 0 14px 31px rgba(18, 58, 97, 0.11);
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            height: 100%;
            transition: transform 0.24s ease;
            animation: doctorReveal 0.5s ease both;
        }

        .premium-doctor-dashboard .premium-stat-card:hover {
            transform: translateY(-4px);
        }

        .premium-doctor-dashboard .premium-stat-label {
            margin-bottom: 0.32rem;
            color: var(--doctor-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .premium-doctor-dashboard .premium-stat-value {
            margin: 0;
            font-size: 1.76rem;
            line-height: 1;
            color: var(--doctor-heading);
            font-weight: 700;
        }

        .premium-doctor-dashboard .premium-stat-meta {
            margin-top: 0.35rem;
            color: #6b7f98;
            font-size: 0.8rem;
        }

        .premium-doctor-dashboard .premium-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .premium-doctor-dashboard .premium-icon-meds {
            color: #1b4fae;
            background: linear-gradient(140deg, #dde8ff 0%, #bfd4ff 100%);
        }

        .premium-doctor-dashboard .premium-icon-queue {
            color: #0b7e64;
            background: linear-gradient(140deg, #dafbeb 0%, #baefdc 100%);
        }

        .premium-doctor-dashboard .premium-icon-patients {
            color: #00718d;
            background: linear-gradient(140deg, #ddf7ff 0%, #b7e8f7 100%);
        }

        @keyframes doctorFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-9px);
            }
        }

        @keyframes doctorReveal {
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
            .premium-doctor-dashboard {
                padding: 1rem;
                border-radius: 20px;
            }

            .premium-doctor-dashboard .premium-hero-card {
                padding: 1.15rem;
                border-radius: 20px;
            }

            .premium-doctor-dashboard .premium-profile-tile {
                width: 100%;
                min-width: 0;
            }

            .premium-doctor-dashboard .premium-hero-copy h2 {
                font-size: 1.4rem;
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
                <span class="premium-chip">Doctor Clinical Board</span>
                <span class="premium-chip">{{ now()->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="premium-profile-tile">
            <img class="premium-avatar" src="{{ $avatar }}" alt="Profile image">
            <div class="premium-profile-title">Clinical Focus</div>
            <div class="premium-profile-value">{{ $patientQueuesCount }} queues active today</div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">Total Medicines</p>
                    <h3 class="premium-stat-value">{{ $medicinesCount }}</h3>
                    <p class="premium-stat-meta">Medicine catalog available for plans</p>
                </div>
                <span class="premium-stat-icon premium-icon-meds"><i class="fas fa-pills"></i></span>
            </article>
        </div>

        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">Patient Queues Today</p>
                    <h3 class="premium-stat-value">{{ $patientQueuesCount }}</h3>
                    <p class="premium-stat-meta">Waiting and in progress consultations</p>
                </div>
                <span class="premium-stat-icon premium-icon-queue"><i class="fas fa-clock"></i></span>
            </article>
        </div>

        <div class="col-xl-4 col-md-6 col-sm-12">
            <article class="premium-stat-card">
                <div>
                    <p class="premium-stat-label">Total Patients</p>
                    <h3 class="premium-stat-value">{{ $patientsCount }}</h3>
                    <p class="premium-stat-meta">Patients currently in the system</p>
                </div>
                <span class="premium-stat-icon premium-icon-patients"><i class="fas fa-users"></i></span>
            </article>
        </div>
    </div>
</div>