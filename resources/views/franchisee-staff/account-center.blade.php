@extends('layouts.franchisee-staff')

@section('content')
<style>
    .account-center-page {
        --brand-primary: #FF9968;
        --brand-secondary: #FF9968;
        --brand-accent: #FF9968;
        --surface: #ffffff;
        --surface-soft: #fff7ed;
        --text-main: #172554;
        --text-muted: #475569;
        --border-soft: rgba(234, 88, 12, 0.14);
        --shadow-soft: 0 24px 60px rgba(15, 23, 42, 0.12);
        padding: 24px 18px 40px;
        background:
            radial-gradient(circle at top left, rgba(250, 204, 21, 0.24), transparent 30%),
            linear-gradient(180deg, #fff7ed 0%, #fffaf0 54%, #eff6ff 100%);
    }

    .account-center-shell {
        max-width: 1040px;
        margin: 0 auto;
    }

    .account-center-hero {
        position: relative;
        overflow: hidden;
        padding: 26px 30px;
        border-radius: 24px;
        color: #fff;
        background: #FF9968;
        box-shadow: var(--shadow-soft);
    }

    .account-center-hero::before,
    .account-center-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .account-center-hero::before {
        width: 220px;
        height: 220px;
        top: -96px;
        right: -28px;
        background: rgba(255, 255, 255, 0.14);
    }

    .account-center-hero::after {
        width: 140px;
        height: 140px;
        bottom: -54px;
        right: 150px;
        background: rgba(255, 255, 255, 0.1);
    }

    .account-center-kicker {
        margin: 0 0 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.8);
    }

    .account-center-title {
        margin: 0;
        font-size: clamp(1.8rem, 3.2vw, 2.9rem);
        line-height: 1.05;
        font-weight: 800;
    }

    .account-center-copy {
        max-width: 660px;
        margin: 12px 0 0;
        font-size: 0.98rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.92);
    }

    .account-center-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-top: 20px;
    }

    .account-center-card {
        position: relative;
        display: block;
        min-height: 220px;
        padding: 22px;
        border: 1px solid var(--border-soft);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .account-center-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.14);
        border-color: rgba(255, 153, 104, 0.26);
    }

    .account-center-card::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
        pointer-events: none;
    }

    .account-center-card-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 18px;
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--brand-primary);
        background: rgba(255, 153, 104, 0.18);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .account-center-card-title {
        margin: 18px 0 10px;
        color: var(--text-main);
        font-size: clamp(1.5rem, 2vw, 2rem);
        line-height: 1.1;
        font-weight: 800;
    }

    .account-center-card-copy {
        margin: 0;
        max-width: 28rem;
        color: var(--text-muted);
        font-size: 0.98rem;
        line-height: 1.6;
    }

    .account-center-card-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
        color: var(--brand-primary);
        font-size: 0.95rem;
        font-weight: 700;
    }

    .account-center-card-link::after {
        content: "->";
        transition: transform 0.2s ease;
    }

    .account-center-card:hover .account-center-card-link::after {
        transform: translateX(4px);
    }

    .account-center-user {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 18px;
        padding: 18px 20px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .account-center-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        background: #FF9968;
    }

    .account-center-user-label {
        margin: 0 0 4px;
        color: #64748b;
        font-size: 0.82rem;
    }

    .account-center-user-name {
        margin: 0;
        color: var(--text-main);
        font-size: 1.12rem;
        font-weight: 800;
    }

    .account-center-user-email {
        margin: 4px 0 0;
        color: var(--text-muted);
        font-size: 0.92rem;
    }

    @media (max-width: 900px) {
        .account-center-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .account-center-page {
            padding: 20px 12px 32px;
        }

        .account-center-hero {
            padding: 22px 18px;
            border-radius: 20px;
        }

        .account-center-card {
            min-height: unset;
            padding: 20px;
            border-radius: 20px;
        }

        .account-center-user {
            align-items: flex-start;
            padding: 18px;
        }
    }
</style>

<div class="account-center-page">
    <div class="account-center-shell">
        <section class="account-center-hero">
            <p class="account-center-kicker">Franchisee Staff Portal</p>
            <h1 class="account-center-title">Account Center</h1>
            <p class="account-center-copy">
                Manage your staff account in one place. Choose whether you want to update your contact information or change your login credentials.
            </p>
        </section>

        <div class="account-center-grid">
            <a href="{{ route('franchisee-staff.account.show') }}" class="account-center-card">
                <span class="account-center-card-badge">P</span>
                <h2 class="account-center-card-title">Update Information</h2>
                <p class="account-center-card-copy">
                    Edit your email address and phone number to keep your account details up to date.
                </p>
                <span class="account-center-card-link">Open profile</span>
            </a>

            <a href="{{ route('franchisee-staff.password') }}" class="account-center-card">
                <span class="account-center-card-badge">S</span>
                <h2 class="account-center-card-title">Update Password</h2>
                <p class="account-center-card-copy">
                    Change your username and password securely from inside the portal.
                </p>
                <span class="account-center-card-link">Open security</span>
            </a>
        </div>

        <section class="account-center-user">
            <div class="account-center-avatar">{{ strtoupper(substr($user->fstaff_username ?? 'A', 0, 1)) }}</div>
            <div>
                <p class="account-center-user-label">Signed in as</p>
                <p class="account-center-user-name">{{ $user->fstaff_username }}</p>
                <p class="account-center-user-email">{{ $user->fstaff_email }}</p>
            </div>
        </section>
    </div>
</div>
@endsection
