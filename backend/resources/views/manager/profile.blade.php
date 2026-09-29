@extends('layouts.app')

@section('title', 'Manager Profile & Audit Logs')

@section('container-class', 'wide')

@section('header')
<header class="app-header">

    <a href="{{ route('manager.dashboard') }}" class="icon-btn" title="Back to Manager Queue">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div style="font-weight: 700; font-size: 1rem;">
        Manager Profile
    </div>

    <div class="header-actions">

        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Switch Theme">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>

        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf

            <button type="submit" class="icon-btn" title="Sign Out">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </form>

    </div>

</header>
@endsection


@section('content')

<main class="app-body">

    <!-- Manager Identity Card -->
    <div class="f-card glow-warning animate-fade-in" style="padding: 22px;">

        <div style="display: flex; align-items: center; gap: 16px;">

            <!-- Manager Profile Photo -->
            <div
                class="manager-profile-photo"
                style="
                    width: 72px;
                    height: 72px;
                    border-radius: 50%;
                    overflow: hidden;
                    flex-shrink: 0;
                    border: 3px solid var(--warning);
                    background: var(--warning);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                "
            >
                <img
                    src="{{ asset('images/manager-profile.jpg') }}"
                    alt="{{ $manager->name }} Profile Photo"
                    style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        display: block;
                    "
                >
            </div>


            <!-- Manager Information -->
            <div>

                <h1 class="title-lg" style="font-size: 1.25rem;">
                    {{ $manager->name }}
                </h1>

                <p class="text-subtext" style="font-size: 0.85rem;">
                    {{ $manager->email }}
                </p>

                <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">

                    <span class="badge badge-warning">
                        Credit Manager
                    </span>

                    <span class="badge badge-primary">
                        Level 3 Clearance
                    </span>

                </div>

            </div>

        </div>


        <!-- Accreditation Information -->
        <div
            style="
                border-top: 1px solid var(--border-color);
                margin-top: 18px;
                padding-top: 14px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                text-align: center;
            "
        >

            <!-- Accreditation -->
            <div class="f-card-sub" style="padding: 10px;">

                <div class="text-caption">
                    Accreditation
                </div>

                <div
                    style="
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--text-main);
                        margin-top: 2px;
                    "
                >
                    {{ $manager->id_type ?? 'PRC Licensed' }}
                </div>

            </div>


            <!-- License Serial -->
            <div class="f-card-sub" style="padding: 10px;">

                <div class="text-caption">
                    License Serial
                </div>

                <div
                    style="
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--primary);
                        margin-top: 2px;
                    "
                >
                    {{ $manager->id_number ?? 'PRC-098271' }}
                </div>

            </div>


            <!-- Regulatory Department -->
            <div class="f-card-sub" style="padding: 10px;">

                <div class="text-caption">
                    Regulatory Dept
                </div>

                <div
                    style="
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--success);
                        margin-top: 2px;
                    "
                >
                    BSP & RA 10173
                </div>

            </div>

        </div>

    </div>


    <!-- Recent Security & Audit Logs -->
    <div class="f-card animate-fade-in">

        <h2 class="title-md" style="margin-bottom: 4px;">
            Manager Audit Trail
        </h2>

        <p
            class="text-subtext"
            style="font-size: 0.78rem; margin-bottom: 14px;"
        >
            Immutable cryptographic audit logs recorded in accordance with Philippine credit reporting laws.
        </p>


        <div style="display: flex; flex-direction: column; gap: 8px;">

            @forelse($recentAuditLogs as $log)

                <div
                    class="f-card-sub"
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 10px 14px;
                        font-size: 0.82rem;
                    "
                >

                    <div>

                        <div style="font-weight: 700; color: var(--text-main);">

                            <i
                                class="fa-solid fa-clock-rotate-left"
                                style="color: var(--primary); margin-right: 6px;"
                            ></i>

                            {{ ucwords(str_replace('_', ' ', $log->action)) }}

                        </div>


                        <div
                            class="text-subtext"
                            style="font-size: 0.72rem; margin-top: 2px;"
                        >

                            @if(is_array($log->details))

                                {{ implode(' • ', array_map(function($k, $v) {
                                    return ucwords(str_replace('_', ' ', $k))
                                        . ': '
                                        . (is_array($v) ? json_encode($v) : $v);
                                }, array_keys($log->details), $log->details)) }}

                            @else

                                {{ $log->details }}

                            @endif

                        </div>

                    </div>


                    <div
                        style="
                            text-align: right;
                            color: var(--text-muted);
                            font-size: 0.75rem;
                        "
                    >
                        {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                    </div>

                </div>

            @empty

                <div
                    class="text-subtext"
                    style="text-align: center; padding: 20px;"
                >
                    No recent audit logs found.
                </div>

            @endforelse

        </div>

    </div>


    <!-- Back Button -->
    <div style="display: flex; gap: 10px;">

        <a
            href="{{ route('manager.dashboard') }}"
            class="btn-primary"
            style="flex: 1;"
        >

            <i class="fa-solid fa-list-check"></i>

            <span>
                Back to Applications Queue
            </span>

        </a>

    </div>

</main>

@endsection