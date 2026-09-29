<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - Finclude-AI</title>

    <style>
        /* =========================================================
           RESET
        ========================================================= */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
            overflow-x: hidden;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        button {
            -webkit-tap-highlight-color: transparent;
        }

        /* =========================================================
           APP
        ========================================================= */

        .app {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* =========================================================
           SIDEBAR
        ========================================================= */

        .sidebar {
            width: 250px;
            background: #111827;
            color: #ffffff;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.25s ease;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
        }

        .brand-title {
            font-size: 17px;
            font-weight: 800;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .nav-section-title {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding: 0 12px;
            margin: 20px 0 8px;
        }

        .nav-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            min-height: 42px;
            border-radius: 9px;
            color: #d1d5db;
            text-decoration: none;
            margin-bottom: 4px;
            transition: 0.2s ease;
            font-size: 14px;
            cursor: pointer;
            border: none;
            background: transparent;
            text-align: left;
        }

        .nav-item:hover {
            background: #1f2937;
            color: #ffffff;
        }

        .nav-item.active {
            background: #2563eb;
            color: #ffffff;
        }

        .nav-icon {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar-bottom {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.10);
        }

        .admin-mini-profile {
            padding: 12px;
            background: #1f2937;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .admin-mini-name {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            word-break: break-word;
        }

        .admin-mini-role {
            color: #9ca3af;
            font-size: 11px;
            margin-top: 3px;
        }

        /* =========================================================
           MOBILE OVERLAY
        ========================================================= */

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 900;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* =========================================================
           MAIN
        ========================================================= */

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            min-width: 0;
        }

        /* =========================================================
           TOPBAR
        ========================================================= */

        .topbar {
            min-height: 70px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 30px;
            position: sticky;
            top: 0;
            z-index: 500;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .topbar-title {
            font-size: 19px;
            font-weight: 800;
            color: #111827;
        }

        .topbar-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .mobile-menu-btn {
            display: none;
            width: 42px;
            height: 42px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #374151;
            border-radius: 9px;
            font-size: 22px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        .mobile-menu-btn:hover {
            background: #f3f4f6;
        }

        /* =========================================================
           BUTTONS
        ========================================================= */

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            min-height: 40px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            white-space: nowrap;
            transition: 0.2s ease;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #eef2f7;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            background: #dc2626;
            color: #ffffff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: #16a34a;
            color: #ffffff;
        }

        .btn-success:hover {
            background: #15803d;
        }

        /* =========================================================
           CONTENT
        ========================================================= */

        .content {
            padding: 30px;
            width: 100%;
            max-width: 1800px;
            margin: 0 auto;
        }

        /* =========================================================
           WELCOME
        ========================================================= */

        .welcome-card {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .welcome-card h1 {
            font-size: 25px;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .welcome-card p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 850px;
        }

        .welcome-date {
            margin-top: 15px;
            font-size: 12px;
            opacity: 0.8;
        }

        /* =========================================================
           SECTION
        ========================================================= */

        .section-title {
            font-size: 17px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 14px;
        }

        /* =========================================================
           STATS
        ========================================================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            min-width: 0;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            overflow-wrap: anywhere;
        }

        .stat-description {
            margin-top: 6px;
            font-size: 11px;
            color: #9ca3af;
        }

        /* =========================================================
           TWO COLUMN
        ========================================================= */

        .two-column {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            min-width: 0;
        }

        .panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
        }

        .panel-subtitle {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 3px;
        }

        .panel-body {
            padding: 20px;
        }

        /* =========================================================
           LOAN SUMMARY
        ========================================================= */

        .loan-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .loan-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            min-width: 0;
        }

        .loan-box-label {
            font-size: 11px;
            color: #6b7280;
        }

        .loan-box-value {
            font-size: 20px;
            font-weight: 800;
            margin-top: 5px;
            color: #111827;
            overflow-wrap: anywhere;
        }

        .loan-box.full-width {
            grid-column: 1 / -1;
        }

        /* =========================================================
           CREDIT SCORE
        ========================================================= */

        .score-row {
            margin-bottom: 17px;
        }

        .score-row:last-child {
            margin-bottom: 0;
        }

        .score-row-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .score-row-label {
            color: #374151;
            font-weight: 700;
        }

        .score-row-value {
            color: #6b7280;
        }

        .progress {
            width: 100%;
            height: 9px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 999px;
            background: #2563eb;
        }

        .progress-green {
            background: #16a34a;
        }

        .progress-yellow {
            background: #eab308;
        }

        .progress-red {
            background: #dc2626;
        }

        /* =========================================================
           TABLES
        ========================================================= */

        .table-panel {
            margin-bottom: 24px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        td {
            padding: 13px 16px;
            border-bottom: 1px solid #eef0f3;
            font-size: 13px;
            color: #374151;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .user-name {
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }

        .user-email {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
            word-break: break-word;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-admin {
            background: #ede9fe;
            color: #6d28d9;
        }

        .badge-manager {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-worker {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-disbursed {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-other {
            background: #f3f4f6;
            color: #4b5563;
        }

        .action-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-btn {
            border: 1px solid #dbe3ef;
            background: #ffffff;
            color: #2563eb;
            padding: 7px 11px;
            min-height: 34px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .action-btn:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .action-btn-danger {
            color: #dc2626;
            border-color: #fecaca;
        }

        .action-btn-danger:hover {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .action-btn-success {
            color: #15803d;
            border-color: #bbf7d0;
        }

        .action-btn-success:hover {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .empty-state {
            text-align: center;
            color: #9ca3af;
            padding: 30px;
            font-size: 13px;
        }

        /* =========================================================
           MODAL
        ========================================================= */

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.60);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.20);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
        }

        .modal-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            line-height: 1.5;
        }

        .modal-close {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border: none;
            background: #f3f4f6;
            color: #374151;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .form-group {
            margin-bottom: 0;
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 11px;
            min-height: 42px;
            font-size: 13px;
            color: #111827;
            background: #ffffff;
            outline: none;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .form-help {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 5px;
            line-height: 1.5;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .status-message {
            display: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .status-message.show {
            display: block;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-error {
            background: #fee2e2;
            color: #991b1b;
        }

        /* =========================================================
           CONFIRM MODAL
        ========================================================= */

        .confirm-card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.20);
        }

        .confirm-title {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 8px;
        }

        .confirm-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* =========================================================
           THEME BUTTON
        ========================================================= */

        .btn-theme {
            background: #eef2f7;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-theme:hover {
            background: #e5e7eb;
        }

        /* =========================================================
           DARK MODE
        ========================================================= */

        body.dark-mode {
            background: #0f172a;
            color: #e5e7eb;
        }

        body.dark-mode .topbar {
            background: #111827;
            border-bottom-color: #1f2937;
        }

        body.dark-mode .topbar-title {
            color: #f9fafb;
        }

        body.dark-mode .topbar-subtitle {
            color: #9ca3af;
        }

        body.dark-mode .mobile-menu-btn {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }

        body.dark-mode .mobile-menu-btn:hover {
            background: #374151;
        }

        body.dark-mode .btn-secondary,
        body.dark-mode .btn-theme {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }

        body.dark-mode .btn-secondary:hover,
        body.dark-mode .btn-theme:hover {
            background: #374151;
        }

        body.dark-mode .section-title {
            color: #f9fafb;
        }

        body.dark-mode .stat-card,
        body.dark-mode .panel,
        body.dark-mode .modal-card,
        body.dark-mode .confirm-card {
            background: #111827;
            border-color: #374151;
            color: #e5e7eb;
        }

        body.dark-mode .stat-label,
        body.dark-mode .panel-subtitle,
        body.dark-mode .user-email,
        body.dark-mode .form-help {
            color: #9ca3af;
        }

        body.dark-mode .stat-value,
        body.dark-mode .panel-title,
        body.dark-mode .user-name,
        body.dark-mode .loan-box-value,
        body.dark-mode .modal-title,
        body.dark-mode .confirm-title {
            color: #f9fafb;
        }

        body.dark-mode .loan-box {
            background: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .loan-box-label,
        body.dark-mode .score-row-label {
            color: #d1d5db;
        }

        body.dark-mode .score-row-value {
            color: #9ca3af;
        }

        body.dark-mode .progress {
            background: #374151;
        }

        body.dark-mode .panel-header,
        body.dark-mode .modal-header,
        body.dark-mode .modal-footer {
            border-color: #374151;
        }

        body.dark-mode th {
            background: #1f2937;
            color: #9ca3af;
            border-bottom-color: #374151;
        }

        body.dark-mode td {
            color: #d1d5db;
            border-bottom-color: #263244;
        }

        body.dark-mode .action-btn {
            background: #1f2937;
            color: #60a5fa;
            border-color: #374151;
        }

        body.dark-mode .action-btn:hover {
            background: #263244;
            border-color: #60a5fa;
        }

        body.dark-mode .action-btn-danger {
            color: #f87171;
            border-color: #7f1d1d;
        }

        body.dark-mode .action-btn-success {
            color: #4ade80;
            border-color: #166534;
        }

        body.dark-mode .modal-close {
            background: #1f2937;
            color: #e5e7eb;
        }

        body.dark-mode .form-label {
            color: #d1d5db;
        }

        body.dark-mode .form-control {
            background: #1f2937;
            color: #f9fafb;
            border-color: #4b5563;
        }

        body.dark-mode .form-control::placeholder {
            color: #9ca3af;
        }

        body.dark-mode .form-control:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
        }

        body.dark-mode .empty-state {
            color: #9ca3af;
        }

        body.dark-mode .modal-backdrop {
            background: rgba(2, 6, 23, 0.78);
        }

        body.dark-mode .sidebar {
            background: #0b1220;
        }

        body.dark-mode .confirm-message {
            color: #9ca3af;
        }

        /* =========================================================
           TRANSITIONS
        ========================================================= */

        body,
        .topbar,
        .stat-card,
        .panel,
        .loan-box,
        .modal-card,
        .confirm-card,
        .form-control,
        th,
        td,
        .action-btn,
        .btn-secondary,
        .btn-theme,
        .mobile-menu-btn {
            transition:
                background-color 0.2s ease,
                color 0.2s ease,
                border-color 0.2s ease;
        }

        /* =========================================================
           TABLET
        ========================================================= */

        @media (max-width: 1100px) {

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .two-column {
                grid-template-columns: 1fr;
            }

        }

        /* =========================================================
           SMALL TABLET
        ========================================================= */

        @media (max-width: 800px) {

            .sidebar {
                width: 250px;
                transform: translateX(-100%);
                box-shadow: 15px 0 40px rgba(0, 0, 0, 0.20);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .mobile-menu-btn {
                display: inline-flex;
            }

            .content {
                padding: 22px;
            }

            .topbar {
                padding: 10px 20px;
            }

            .topbar-title {
                font-size: 17px;
            }

            .topbar-subtitle {
                font-size: 11px;
            }

        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 600px) {

            body {
                font-size: 14px;
            }

            .topbar {
                min-height: 62px;
                padding: 9px 12px;
                gap: 8px;
            }

            .topbar-left {
                gap: 8px;
                min-width: 0;
            }

            .mobile-menu-btn {
                width: 40px;
                height: 40px;
                min-width: 40px;
                font-size: 21px;
            }

            .topbar-title {
                font-size: 15px;
                line-height: 1.2;
            }

            .topbar-subtitle {
                display: none;
            }

            .topbar-actions {
                gap: 5px;
            }

            .topbar-actions .btn {
                min-height: 38px;
                padding: 8px 9px;
                font-size: 11px;
            }

            #themeText {
                display: none;
            }

            .content {
                padding: 14px;
            }

            .welcome-card {
                padding: 20px;
                border-radius: 13px;
                margin-bottom: 18px;
            }

            .welcome-card h1 {
                font-size: 20px;
                line-height: 1.3;
                margin-bottom: 8px;
            }

            .welcome-card p {
                font-size: 12px;
                line-height: 1.6;
            }

            .welcome-date {
                font-size: 10px;
                margin-top: 12px;
            }

            .section-title {
                font-size: 15px;
                margin-bottom: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-bottom: 18px;
            }

            .stat-card {
                padding: 16px;
                border-radius: 12px;
            }

            .stat-label {
                font-size: 11px;
            }

            .stat-value {
                font-size: 25px;
            }

            .stat-description {
                font-size: 10px;
            }

            .two-column {
                gap: 14px;
                margin-bottom: 18px;
            }

            .panel {
                border-radius: 12px;
            }

            .panel-header {
                padding: 15px;
            }

            .panel-title {
                font-size: 14px;
            }

            .panel-subtitle {
                font-size: 10px;
                line-height: 1.4;
            }

            .panel-body {
                padding: 15px;
            }

            .loan-summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 9px;
            }

            .loan-box {
                padding: 12px;
                border-radius: 9px;
            }

            .loan-box-label {
                font-size: 10px;
                line-height: 1.4;
            }

            .loan-box-value {
                font-size: 17px;
            }

            .table-panel {
                margin-bottom: 18px;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            table {
                min-width: 720px;
            }

            th {
                padding: 10px 12px;
                font-size: 10px;
            }

            td {
                padding: 11px 12px;
                font-size: 12px;
            }

            .user-name {
                font-size: 12px;
            }

            .user-email {
                font-size: 10px;
            }

            .action-btn {
                min-height: 36px;
                padding: 7px 10px;
            }

            .badge {
                font-size: 9px;
                padding: 5px 8px;
            }

            /* Modal */

            .modal-backdrop {
                padding: 10px;
                align-items: flex-end;
            }

            .modal-card {
                max-height: 94vh;
                border-radius: 16px 16px 0 0;
            }

            .modal-header {
                padding: 16px;
            }

            .modal-body {
                padding: 16px;
            }

            .modal-footer {
                padding: 13px 16px;
                position: sticky;
                bottom: 0;
                background: inherit;
            }

            .modal-title {
                font-size: 16px;
            }

            .modal-subtitle {
                font-size: 11px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .form-group.full {
                grid-column: auto;
            }

            .form-control {
                min-height: 44px;
            }

            .modal-footer .btn {
                flex: 1;
            }

            /* Confirm modal */

            .confirm-card {
                max-width: none;
                border-radius: 16px;
                padding: 20px;
            }

            .confirm-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .confirm-actions .btn {
                width: 100%;
            }

        }

        /* =========================================================
           VERY SMALL PHONES
        ========================================================= */

        @media (max-width: 380px) {

            .content {
                padding: 10px;
            }

            .topbar {
                padding: 8px 9px;
            }

            .topbar-actions .btn {
                padding: 7px 8px;
            }

            .welcome-card {
                padding: 17px;
            }

            .welcome-card h1 {
                font-size: 18px;
            }

            .loan-summary-grid {
                grid-template-columns: 1fr;
            }

            .loan-box.full-width {
                grid-column: auto;
            }

            .panel-header {
                padding: 13px;
            }

            .panel-body {
                padding: 13px;
            }

        }

        /* =========================================================
           REDUCE MOTION
        ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }

        }
    </style>
</head>

<body>

<div class="app">

    <!-- =========================================================
         SIDEBAR
    ========================================================= -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <div class="brand-icon">
                F
            </div>

            <div>
                <div class="brand-title">
                    Finclude-AI
                </div>

                <div class="brand-subtitle">
                    Administration
                </div>
            </div>

        </div>


        <div class="nav-section-title">
            Main
        </div>


        <a
            href="{{ route('admin.dashboard') }}"
            class="nav-item active"
            onclick="closeMobileSidebar()"
        >
            <span class="nav-icon">⌂</span>
            <span>Dashboard</span>
        </a>


        <button
            type="button"
            class="nav-item"
            onclick="scrollToSection('usersSection')"
        >
            <span class="nav-icon">👥</span>
            <span>Users</span>
        </button>


        <button
            type="button"
            class="nav-item"
            onclick="scrollToSection('loansSection')"
        >
            <span class="nav-icon">▣</span>
            <span>Loans</span>
        </button>


        <button
            type="button"
            class="nav-item"
            onclick="scrollToSection('scoresSection')"
        >
            <span class="nav-icon">◉</span>
            <span>Credit Scores</span>
        </button>


        <button
            type="button"
            class="nav-item"
            onclick="scrollToSection('auditSection')"
        >
            <span class="nav-icon">▤</span>
            <span>Audit Logs</span>
        </button>


        <div class="nav-section-title">
            Actions
        </div>


        <button
            type="button"
            class="nav-item"
            onclick="window.location.reload()"
        >
            <span class="nav-icon">↻</span>
            <span>Refresh Data</span>
        </button>


        <a
            href="{{ route('dashboard') }}"
            class="nav-item"
            onclick="closeMobileSidebar()"
        >
            <span class="nav-icon">◀</span>
            <span>Worker Portal</span>
        </a>


        <div class="sidebar-bottom">

            <div class="admin-mini-profile">

                <div class="admin-mini-name">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </div>

                <div class="admin-mini-role">
                    Administrator
                </div>

            </div>


            <a
                href="{{ route('logout') }}"
                class="nav-item"
            >
                <span class="nav-icon">↪</span>
                <span>Logout</span>
            </a>

        </div>

    </aside>


    <!-- MOBILE SIDEBAR OVERLAY -->

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="closeMobileSidebar()"
    ></div>


    <!-- =========================================================
         MAIN
    ========================================================= -->

    <main class="main">

        <!-- =====================================================
             TOP BAR
        ====================================================== -->

        <div class="topbar">

            <div class="topbar-left">

                <button
                    type="button"
                    class="mobile-menu-btn"
                    id="mobileMenuBtn"
                    onclick="toggleMobileSidebar()"
                    aria-label="Open menu"
                    aria-expanded="false"
                >
                    ☰
                </button>


                <div>

                    <div class="topbar-title">
                        Admin Dashboard
                    </div>

                    <div class="topbar-subtitle">
                        Finclude-AI System Administration
                    </div>

                </div>

            </div>


            <div class="topbar-actions">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="window.location.reload()"
                >
                    ↻ <span class="refresh-text">Refresh</span>
                </button>


                <button
                    type="button"
                    class="btn btn-theme"
                    id="themeToggle"
                    onclick="toggleTheme()"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode"
                >
                    <span id="themeIcon">☾</span>
                    <span id="themeText">Dark Mode</span>
                </button>

            </div>

        </div>


        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <div class="content">

            <!-- =================================================
                 WELCOME
            ================================================== -->

            <section class="welcome-card">

                <h1>
                    Welcome, {{ auth()->user()->name ?? 'Administrator' }}
                </h1>

                <p>
                    Manage Finclude-AI users, monitor loan activity,
                    view credit scoring information, and review system
                    audit records from this dashboard.
                </p>

                <div class="welcome-date">
                    {{ now()->format('F d, Y • h:i A') }}
                </div>

            </section>


            <!-- =================================================
                 USER OVERVIEW
            ================================================== -->

            <div class="section-title">
                User Overview
            </div>


            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-label">
                        Total Users
                    </div>

                    <div class="stat-value">
                        {{ number_format($summary['total_users'] ?? 0) }}
                    </div>

                    <div class="stat-description">
                        All registered accounts
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-label">
                        Gig Workers
                    </div>

                    <div class="stat-value">
                        {{ number_format($summary['workers'] ?? 0) }}
                    </div>

                    <div class="stat-description">
                        Worker accounts
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-label">
                        Managers
                    </div>

                    <div class="stat-value">
                        {{ number_format($summary['managers'] ?? 0) }}
                    </div>

                    <div class="stat-description">
                        Manager accounts
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-label">
                        Administrators
                    </div>

                    <div class="stat-value">
                        {{ number_format($summary['admins'] ?? 0) }}
                    </div>

                    <div class="stat-description">
                        Admin accounts
                    </div>

                </div>

            </div>


            <!-- =================================================
                 LOAN + SCORE SUMMARY
            ================================================== -->

            <div class="two-column">

                <!-- LOANS -->

                <section
                    class="panel"
                    id="loansSection"
                >

                    <div class="panel-header">

                        <div>

                            <div class="panel-title">
                                Loan Portfolio
                            </div>

                            <div class="panel-subtitle">
                                Current loan application summary
                            </div>

                        </div>

                    </div>


                    <div class="panel-body">

                        <div class="loan-summary-grid">

                            <div class="loan-box">

                                <div class="loan-box-label">
                                    Total Loans
                                </div>

                                <div class="loan-box-value">
                                    {{ number_format($summary['total_loans'] ?? 0) }}
                                </div>

                            </div>


                            <div class="loan-box">

                                <div class="loan-box-label">
                                    Pending
                                </div>

                                <div class="loan-box-value">
                                    {{ number_format($summary['pending_loans'] ?? 0) }}
                                </div>

                            </div>


                            <div class="loan-box">

                                <div class="loan-box-label">
                                    Approved / Disbursed
                                </div>

                                <div class="loan-box-value">
                                    {{ number_format($summary['approved_loans'] ?? 0) }}
                                </div>

                            </div>


                            <div class="loan-box">

                                <div class="loan-box-label">
                                    Rejected
                                </div>

                                <div class="loan-box-value">
                                    {{ number_format($summary['rejected_loans'] ?? 0) }}
                                </div>

                            </div>


                            <div class="loan-box full-width">

                                <div class="loan-box-label">
                                    Total Loan Amount
                                </div>

                                <div class="loan-box-value">
                                    ₱{{ number_format($summary['total_loan_amount'] ?? 0, 2) }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- CREDIT SCORES -->

                <section
                    class="panel"
                    id="scoresSection"
                >

                    <div class="panel-header">

                        <div>

                            <div class="panel-title">
                                Credit Score Distribution
                            </div>

                            <div class="panel-subtitle">
                                Current score classification
                            </div>

                        </div>

                    </div>


                    <div class="panel-body">

                        @php

                            $primeScores =
                                $summary['prime_scores'] ?? 0;

                            $standardScores =
                                $summary['standard_scores'] ?? 0;

                            $highRiskScores =
                                $summary['high_risk_scores'] ?? 0;

                            $totalScores =
                                $primeScores +
                                $standardScores +
                                $highRiskScores;

                            $primePercent =
                                $totalScores > 0
                                    ? min(
                                        100,
                                        ($primeScores / $totalScores) * 100
                                    )
                                    : 0;

                            $standardPercent =
                                $totalScores > 0
                                    ? min(
                                        100,
                                        ($standardScores / $totalScores) * 100
                                    )
                                    : 0;

                            $highRiskPercent =
                                $totalScores > 0
                                    ? min(
                                        100,
                                        ($highRiskScores / $totalScores) * 100
                                    )
                                    : 0;

                        @endphp


                        <div class="score-row">

                            <div class="score-row-header">

                                <span class="score-row-label">
                                    Prime
                                </span>

                                <span class="score-row-value">
                                    {{ $primeScores }}
                                </span>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar progress-green"
                                    style="width: {{ $primePercent }}%;"
                                ></div>

                            </div>

                        </div>


                        <div class="score-row">

                            <div class="score-row-header">

                                <span class="score-row-label">
                                    Standard
                                </span>

                                <span class="score-row-value">
                                    {{ $standardScores }}
                                </span>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar progress-yellow"
                                    style="width: {{ $standardPercent }}%;"
                                ></div>

                            </div>

                        </div>


                        <div class="score-row">

                            <div class="score-row-header">

                                <span class="score-row-label">
                                    High Risk
                                </span>

                                <span class="score-row-value">
                                    {{ $highRiskScores }}
                                </span>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar progress-red"
                                    style="width: {{ $highRiskPercent }}%;"
                                ></div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>


            <!-- =================================================
                 USER MANAGEMENT
            ================================================== -->

            <section
                class="panel table-panel"
                id="usersSection"
            >

                <div class="panel-header">

                    <div>

                        <div class="panel-title">
                            User Management
                        </div>

                        <div class="panel-subtitle">
                            Manage registered accounts and access status
                        </div>

                    </div>

                </div>


                <div class="table-wrapper">

                    @if(isset($recentUsers) && $recentUsers->count())

                        <table>

                            <thead>

                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Action</th>
                                </tr>

                            </thead>


                            <tbody>

                                @foreach($recentUsers as $recentUser)

                                    @php

                                        $userRole =
                                            $recentUser->role
                                            ?? $recentUser->user_type
                                            ?? 'gig_worker';

                                        $roleClass = match ($userRole) {

                                            'admin' =>
                                                'badge-admin',

                                            'manager' =>
                                                'badge-manager',

                                            default =>
                                                'badge-worker',

                                        };

                                        $isActive =
                                            isset($recentUser->is_active)
                                                ? (bool) $recentUser->is_active
                                                : true;

                                    @endphp


                                    <tr>

                                        <td>

                                            <div class="user-name">
                                                {{ $recentUser->name }}
                                            </div>

                                            <div class="user-email">
                                                {{ $recentUser->email }}
                                            </div>

                                        </td>


                                        <td>
                                            {{ $recentUser->username ?? '—' }}
                                        </td>


                                        <td>

                                            <span class="badge {{ $roleClass }}">
                                                {{ str_replace('_', ' ', $userRole) }}
                                            </span>

                                        </td>


                                        <td>

                                            @if($isActive)

                                                <span class="badge badge-active">
                                                    Active
                                                </span>

                                            @else

                                                <span class="badge badge-inactive">
                                                    Inactive
                                                </span>

                                            @endif

                                        </td>


                                        <td>
                                            {{ optional($recentUser->created_at)->format('M d, Y') }}
                                        </td>


                                        <td>

                                            <div class="action-group">

                                                <button
                                                    type="button"
                                                    class="action-btn"
                                                    data-id="{{ $recentUser->id }}"
                                                    data-name="{{ $recentUser->name }}"
                                                    data-email="{{ $recentUser->email }}"
                                                    data-username="{{ $recentUser->username }}"
                                                    data-phone="{{ $recentUser->phone_number ?? '' }}"
                                                    data-role="{{ $userRole }}"
                                                    onclick="openEditUserModal(this)"
                                                >
                                                    Edit
                                                </button>


                                                @if($isActive)

                                                    <button
                                                        type="button"
                                                        class="action-btn action-btn-danger"
                                                        data-user-id="{{ $recentUser->id }}"
                                                        data-user-name="{{ $recentUser->name }}"
                                                        onclick="confirmDeactivateUser(this)"
                                                    >
                                                        Deactivate
                                                    </button>

                                                @else

                                                    <button
                                                        type="button"
                                                        class="action-btn action-btn-success"
                                                        data-user-id="{{ $recentUser->id }}"
                                                        data-user-name="{{ $recentUser->name }}"
                                                        onclick="confirmRestoreUser(this)"
                                                    >
                                                        Restore
                                                    </button>

                                                @endif

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    @else

                        <div class="empty-state">
                            No users found.
                        </div>

                    @endif

                </div>

            </section>


            <!-- =================================================
                 RECENT LOANS
            ================================================== -->

            <section
                class="panel table-panel"
                id="recentLoansSection"
            >

                <div class="panel-header">

                    <div>

                        <div class="panel-title">
                            Recent Loans
                        </div>

                        <div class="panel-subtitle">
                            Latest loan applications
                        </div>

                    </div>

                </div>


                <div class="table-wrapper">

                    @if(isset($recentLoans) && $recentLoans->count())

                        <table>

                            <thead>

                                <tr>
                                    <th>Applicant</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Credit Score</th>
                                    <th>Date</th>
                                </tr>

                            </thead>


                            <tbody>

                                @foreach($recentLoans as $loan)

                                    @php

                                        $loanStatus =
                                            strtolower(
                                                $loan->status ?? 'unknown'
                                            );

                                        $statusClass = match ($loanStatus) {

                                            'pending' =>
                                                'badge-pending',

                                            'approved' =>
                                                'badge-approved',

                                            'disbursed' =>
                                                'badge-disbursed',

                                            'rejected' =>
                                                'badge-rejected',

                                            default =>
                                                'badge-other',

                                        };

                                        $loanScore =
                                            optional(
                                                $loan->user
                                            )->latestCreditScore->score
                                            ?? null;

                                    @endphp


                                    <tr>

                                        <td>

                                            <div class="user-name">
                                                {{ optional($loan->user)->name ?? 'Unknown User' }}
                                            </div>

                                            <div class="user-email">
                                                {{ optional($loan->user)->email ?? '—' }}
                                            </div>

                                        </td>


                                        <td>
                                            ₱{{ number_format((float) ($loan->amount ?? 0), 2) }}
                                        </td>


                                        <td>

                                            <span class="badge {{ $statusClass }}">
                                                {{ str_replace('_', ' ', $loanStatus) }}
                                            </span>

                                        </td>


                                        <td>
                                            {{ $loanScore !== null ? $loanScore : '—' }}
                                        </td>


                                        <td>
                                            {{ optional($loan->created_at)->format('M d, Y') }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    @else

                        <div class="empty-state">
                            No loan applications found.
                        </div>

                    @endif

                </div>

            </section>


            <!-- =================================================
                 AUDIT LOGS
            ================================================== -->

            <section
                class="panel table-panel"
                id="auditSection"
            >

                <div class="panel-header">

                    <div>

                        <div class="panel-title">
                            Recent Audit Logs
                        </div>

                        <div class="panel-subtitle">
                            Latest system activity
                        </div>

                    </div>

                </div>


                <div class="table-wrapper">

                    @if(isset($recentAuditLogs) && $recentAuditLogs->count())

                        <table>

                            <thead>

                                <tr>
                                    <th>Action</th>
                                    <th>User ID</th>
                                    <th>Details</th>
                                    <th>Date</th>
                                </tr>

                            </thead>


                            <tbody>

                                @foreach($recentAuditLogs as $log)

                                    <tr>

                                        <td>

                                            <strong>
                                                {{ str_replace('_', ' ', $log->action ?? 'system activity') }}
                                            </strong>

                                        </td>


                                        <td>
                                            {{ $log->user_id ?? 'System' }}
                                        </td>


                                        <td>

                                            @php

                                                $details =
                                                    $log->details ?? null;

                                                if (is_array($details)) {
                                                    $details =
                                                        json_encode($details);
                                                }

                                            @endphp

                                            {{ $details ?: '—' }}

                                        </td>


                                        <td>
                                            {{ optional($log->created_at)->format('M d, Y h:i A') }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    @else

                        <div class="empty-state">
                            No audit logs found.
                        </div>

                    @endif

                </div>

            </section>

        </div>

    </main>

</div>


<!-- =========================================================
     EDIT USER MODAL
========================================================= -->

<div
    class="modal-backdrop"
    id="editUserModal"
    onclick="handleModalBackdropClick(event)"
>

    <div class="modal-card">

        <div class="modal-header">

            <div>

                <div class="modal-title">
                    Edit User
                </div>

                <div class="modal-subtitle">
                    Update account information and access role.
                </div>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeEditUserModal()"
                aria-label="Close"
            >
                ×
            </button>

        </div>


        <form id="editUserForm">

            <div class="modal-body">

                <div
                    id="editUserStatus"
                    class="status-message"
                ></div>


                <input
                    type="hidden"
                    id="editUserId"
                >


                <div class="form-grid">

                    <!-- NAME -->

                    <div class="form-group full">

                        <label
                            class="form-label"
                            for="editName"
                        >
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="editName"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label
                            class="form-label"
                            for="editEmail"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            id="editEmail"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- USERNAME -->

                    <div class="form-group">

                        <label
                            class="form-label"
                            for="editUsername"
                        >
                            Username
                        </label>

                        <input
                            type="text"
                            id="editUsername"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="form-group">

                        <label
                            class="form-label"
                            for="editPhone"
                        >
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="editPhone"
                            class="form-control"
                            placeholder="+63..."
                        >

                    </div>


                    <!-- ROLE -->

                    <div class="form-group">

                        <label
                            class="form-label"
                            for="editRole"
                        >
                            Account Role
                        </label>

                        <select
                            id="editRole"
                            class="form-control"
                            required
                        >

                            <option value="gig_worker">
                                Worker
                            </option>

                            <option value="manager">
                                Manager
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                        </select>

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group full">

                        <label
                            class="form-label"
                            for="editPassword"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            id="editPassword"
                            class="form-control"
                            minlength="8"
                            placeholder="Leave blank to keep current password"
                        >

                        <div class="form-help">
                            Leave this blank if you do not want to change the password.
                        </div>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeEditUserModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="btn btn-primary"
                    id="saveUserBtn"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<!-- =========================================================
     CONFIRM ACTION MODAL
========================================================= -->

<div
    class="modal-backdrop"
    id="confirmActionModal"
    onclick="handleConfirmBackdropClick(event)"
>

    <div class="confirm-card">

        <div
            class="confirm-title"
            id="confirmActionTitle"
        >
            Confirm Action
        </div>

        <div
            class="confirm-message"
            id="confirmActionMessage"
        >
            Are you sure?
        </div>

        <div class="confirm-actions">

            <button
                type="button"
                class="btn btn-secondary"
                onclick="closeConfirmModal()"
                id="confirmCancelBtn"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn btn-danger"
                id="confirmActionBtn"
                onclick="executeConfirmedAction()"
            >
                Confirm
            </button>

        </div>

    </div>

</div>


<script>

    /* =========================================================
       CSRF TOKEN
    ========================================================= */

    const csrfMeta =
        document.querySelector(
            'meta[name="csrf-token"]'
        );

    const csrfToken =
        csrfMeta
            ? csrfMeta.getAttribute('content')
            : '';


    /* =========================================================
       MOBILE SIDEBAR
    ========================================================= */

    function toggleMobileSidebar() {

        const sidebar =
            document.getElementById('sidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        const menuButton =
            document.getElementById('mobileMenuBtn');

        if (!sidebar || !overlay) {
            return;
        }

        const isOpen =
            sidebar.classList.contains('mobile-open');

        if (isOpen) {

            closeMobileSidebar();

        } else {

            sidebar.classList.add('mobile-open');
            overlay.classList.add('show');

            if (menuButton) {
                menuButton.setAttribute(
                    'aria-expanded',
                    'true'
                );

                menuButton.textContent = '×';
            }

            document.body.style.overflow = 'hidden';
        }
    }


    function closeMobileSidebar() {

        const sidebar =
            document.getElementById('sidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        const menuButton =
            document.getElementById('mobileMenuBtn');

        if (sidebar) {
            sidebar.classList.remove('mobile-open');
        }

        if (overlay) {
            overlay.classList.remove('show');
        }

        if (menuButton) {

            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );

            menuButton.textContent = '☰';
        }

        /*
         * Only restore body scrolling when
         * no modal is currently open.
         */

        const editModal =
            document.getElementById('editUserModal');

        const confirmModal =
            document.getElementById('confirmActionModal');

        const modalOpen =
            (
                editModal &&
                editModal.classList.contains('show')
            ) ||
            (
                confirmModal &&
                confirmModal.classList.contains('show')
            );

        if (!modalOpen) {
            document.body.style.overflow = '';
        }
    }


    /* =========================================================
       CLOSE SIDEBAR WHEN RESIZING TO DESKTOP
    ========================================================= */

    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth > 800) {
                closeMobileSidebar();
            }

        }
    );


    /* =========================================================
       SCROLL TO SECTION
    ========================================================= */

    function scrollToSection(id) {

        const element =
            document.getElementById(id);

        if (!element) {
            return;
        }

        closeMobileSidebar();

        setTimeout(function () {

            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

        }, 100);
    }


    /* =========================================================
       EDIT USER MODAL
    ========================================================= */

    function openEditUserModal(button) {

        const modal =
            document.getElementById(
                'editUserModal'
            );

        const userId =
            button.dataset.id;

        const name =
            button.dataset.name || '';

        const email =
            button.dataset.email || '';

        const username =
            button.dataset.username || '';

        const phone =
            button.dataset.phone || '';

        const role =
            button.dataset.role || 'gig_worker';


        document.getElementById(
            'editUserId'
        ).value = userId;


        document.getElementById(
            'editName'
        ).value = name;


        document.getElementById(
            'editEmail'
        ).value = email;


        document.getElementById(
            'editUsername'
        ).value = username;


        document.getElementById(
            'editPhone'
        ).value = phone;


        document.getElementById(
            'editRole'
        ).value = role;


        document.getElementById(
            'editPassword'
        ).value = '';


        hideEditUserStatus();


        modal.classList.add('show');

        document.body.style.overflow = 'hidden';

    }


    function closeEditUserModal() {

        const modal =
            document.getElementById(
                'editUserModal'
            );

        modal.classList.remove('show');

        document.body.style.overflow = '';

        document
            .getElementById('editUserForm')
            .reset();

        hideEditUserStatus();

    }


    function handleModalBackdropClick(event) {

        if (
            event.target.id ===
            'editUserModal'
        ) {
            closeEditUserModal();
        }

    }


    /* =========================================================
       STATUS MESSAGE
    ========================================================= */

    function showEditUserStatus(
        message,
        type
    ) {

        const element =
            document.getElementById(
                'editUserStatus'
            );

        element.textContent =
            message;

        element.className =
            'status-message show ' +
            (
                type === 'success'
                    ? 'status-success'
                    : 'status-error'
            );

    }


    function hideEditUserStatus() {

        const element =
            document.getElementById(
                'editUserStatus'
            );

        if (!element) {
            return;
        }

        element.textContent = '';

        element.className =
            'status-message';

    }


    /* =========================================================
       UPDATE USER
    ========================================================= */

    const editUserForm =
        document.getElementById(
            'editUserForm'
        );


    if (editUserForm) {

        editUserForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();


                const userId =
                    document
                        .getElementById(
                            'editUserId'
                        )
                        .value;


                const password =
                    document
                        .getElementById(
                            'editPassword'
                        )
                        .value
                        .trim();


                const payload = {

                    name:
                        document
                            .getElementById(
                                'editName'
                            )
                            .value
                            .trim(),

                    email:
                        document
                            .getElementById(
                                'editEmail'
                            )
                            .value
                            .trim(),

                    username:
                        document
                            .getElementById(
                                'editUsername'
                            )
                            .value
                            .trim(),

                    phone_number:
                        document
                            .getElementById(
                                'editPhone'
                            )
                            .value
                            .trim(),

                    role:
                        document
                            .getElementById(
                                'editRole'
                            )
                            .value

                };


                if (password !== '') {
                    payload.password =
                        password;
                }


                const saveButton =
                    document.getElementById(
                        'saveUserBtn'
                    );


                saveButton.disabled =
                    true;

                saveButton.textContent =
                    'Saving...';

                hideEditUserStatus();


                try {

                    const response =
                        await fetch(
                            `/admin/users/${userId}`,
                            {
                                method: 'PUT',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken

                                },

                                body:
                                    JSON.stringify(
                                        payload
                                    )

                            }
                        );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        let message =
                            data.message ||
                            'Unable to update user.';


                        if (data.errors) {

                            const errors =
                                Object
                                    .values(
                                        data.errors
                                    )
                                    .flat();


                            if (
                                errors.length > 0
                            ) {

                                message =
                                    errors.join(' ');

                            }

                        }


                        throw new Error(
                            message
                        );

                    }


                    showEditUserStatus(
                        data.message ||
                        'User updated successfully.',
                        'success'
                    );


                    setTimeout(
                        function () {

                            window.location.reload();

                        },
                        700
                    );


                } catch (error) {

                    showEditUserStatus(
                        error.message ||
                        'An unexpected error occurred.',
                        'error'
                    );


                    saveButton.disabled =
                        false;

                    saveButton.textContent =
                        'Save Changes';

                }

            }
        );

    }


    /* =========================================================
       DEACTIVATE / RESTORE
    ========================================================= */

    let pendingUserAction = null;

    let pendingUserId = null;


    function confirmDeactivateUser(button) {

        pendingUserAction =
            'deactivate';

        pendingUserId =
            button.dataset.userId;


        const userName =
            button.dataset.userName ||
            'this user';


        document.getElementById(
            'confirmActionTitle'
        ).textContent =
            'Deactivate User';


        document.getElementById(
            'confirmActionMessage'
        ).textContent =
            `Are you sure you want to deactivate ${userName}? The account will no longer be active, but its records will remain in the system.`;


        const confirmButton =
            document.getElementById(
                'confirmActionBtn'
            );


        confirmButton.textContent =
            'Deactivate';

        confirmButton.className =
            'btn btn-danger';


        openConfirmModal();

    }


    function confirmRestoreUser(button) {

        pendingUserAction =
            'restore';

        pendingUserId =
            button.dataset.userId;


        const userName =
            button.dataset.userName ||
            'this user';


        document.getElementById(
            'confirmActionTitle'
        ).textContent =
            'Restore User';


        document.getElementById(
            'confirmActionMessage'
        ).textContent =
            `Are you sure you want to restore ${userName}? The account will become active again.`;


        const confirmButton =
            document.getElementById(
                'confirmActionBtn'
            );


        confirmButton.textContent =
            'Restore';

        confirmButton.className =
            'btn btn-success';


        openConfirmModal();

    }


    function openConfirmModal() {

        document
            .getElementById(
                'confirmActionModal'
            )
            .classList.add('show');

        document.body.style.overflow =
            'hidden';

    }


    function closeConfirmModal() {

        document
            .getElementById(
                'confirmActionModal'
            )
            .classList.remove('show');

        document.body.style.overflow =
            '';

        pendingUserAction =
            null;

        pendingUserId =
            null;

    }


    function handleConfirmBackdropClick(
        event
    ) {

        if (
            event.target.id ===
            'confirmActionModal'
        ) {

            closeConfirmModal();

        }

    }


    async function executeConfirmedAction() {

        if (
            !pendingUserAction ||
            !pendingUserId
        ) {
            return;
        }


        const action =
            pendingUserAction;

        const userId =
            pendingUserId;


        const confirmButton =
            document.getElementById(
                'confirmActionBtn'
            );


        const cancelButton =
            document.getElementById(
                'confirmCancelBtn'
            );


        confirmButton.disabled =
            true;

        cancelButton.disabled =
            true;

        confirmButton.textContent =
            'Processing...';


        try {

            const endpoint =
                action === 'deactivate'
                    ? `/admin/users/${userId}/deactivate`
                    : `/admin/users/${userId}/restore`;


            const response =
                await fetch(
                    endpoint,
                    {
                        method: 'PUT',

                        headers: {

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken

                        }

                    }
                );


            const data =
                await response.json();


            if (!response.ok) {

                let message =
                    data.message ||
                    'Unable to complete the action.';


                if (data.errors) {

                    const errors =
                        Object
                            .values(
                                data.errors
                            )
                            .flat();


                    if (
                        errors.length > 0
                    ) {

                        message =
                            errors.join(' ');

                    }

                }


                throw new Error(
                    message
                );

            }


            closeConfirmModal();

            window.location.reload();


        } catch (error) {

            alert(
                error.message ||
                'An unexpected error occurred.'
            );


            confirmButton.disabled =
                false;

            cancelButton.disabled =
                false;


            confirmButton.textContent =
                action === 'deactivate'
                    ? 'Deactivate'
                    : 'Restore';

        }

    }


    /* =========================================================
       ESC KEY
    ========================================================= */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
            ) {

                const sidebar =
                    document.getElementById(
                        'sidebar'
                    );

                if (
                    sidebar &&
                    sidebar.classList.contains(
                        'mobile-open'
                    )
                ) {

                    closeMobileSidebar();

                }


                closeEditUserModal();

                closeConfirmModal();

            }

        }
    );


    /* =========================================================
       DARK / LIGHT MODE
    ========================================================= */

    function applyTheme(theme) {

        const isDark =
            theme === 'dark';


        document.body.classList.toggle(
            'dark-mode',
            isDark
        );


        const icon =
            document.getElementById(
                'themeIcon'
            );


        const text =
            document.getElementById(
                'themeText'
            );


        if (icon && text) {

            icon.textContent =
                isDark
                    ? '☀'
                    : '☾';


            text.textContent =
                isDark
                    ? 'Light Mode'
                    : 'Dark Mode';

        }

    }


    function toggleTheme() {

        const currentTheme =
            document.body.classList.contains(
                'dark-mode'
            )
                ? 'dark'
                : 'light';


        const newTheme =
            currentTheme === 'dark'
                ? 'light'
                : 'dark';


        localStorage.setItem(
            'adminDashboardTheme',
            newTheme
        );


        applyTheme(newTheme);

    }


    /* =========================================================
       INITIALIZE
    ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const savedTheme =
                localStorage.getItem(
                    'adminDashboardTheme'
                );


            if (savedTheme) {

                applyTheme(
                    savedTheme
                );

            } else {

                applyTheme(
                    'light'
                );

            }

        }
    );

</script>

</body>
</html>