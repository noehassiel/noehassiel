<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>{{ $proposal->number }} — {{ $proposal->title }}</title>
    <style>
        /* ── FONTS ────────────────────────────────────── */
        @font-face {
            font-family: 'Cabinet Grotesk';
            src: url('{{ resource_path('fonts/CabinetGrotesk-Regular.ttf') }}') format('truetype');
            font-weight: 400;
        }
        @font-face {
            font-family: 'Cabinet Grotesk';
            src: url('{{ resource_path('fonts/CabinetGrotesk-Medium.ttf') }}') format('truetype');
            font-weight: 500;
        }
        @font-face {
            font-family: 'Cabinet Grotesk';
            src: url('{{ resource_path('fonts/CabinetGrotesk-Bold.ttf') }}') format('truetype');
            font-weight: 700;
        }
        @font-face {
            font-family: 'Cabinet Grotesk';
            src: url('{{ resource_path('fonts/CabinetGrotesk-Extrabold.ttf') }}') format('truetype');
            font-weight: 800;
        }
        @font-face {
            font-family: 'Cabinet Grotesk';
            src: url('{{ resource_path('fonts/CabinetGrotesk-Black.ttf') }}') format('truetype');
            font-weight: 900;
        }
        @font-face {
            font-family: 'Geist Mono';
            src: url('{{ resource_path('fonts/GeistMono-Regular.ttf') }}') format('truetype');
            font-weight: 400;
        }
        @font-face {
            font-family: 'Geist Mono';
            src: url('{{ resource_path('fonts/GeistMono-Medium.ttf') }}') format('truetype');
            font-weight: 500;
        }
        @font-face {
            font-family: 'Geist Mono';
            src: url('{{ resource_path('fonts/GeistMono-SemiBold.ttf') }}') format('truetype');
            font-weight: 600;
        }
        @font-face {
            font-family: 'Geist Mono';
            src: url('{{ resource_path('fonts/GeistMono-Bold.ttf') }}') format('truetype');
            font-weight: 700;
        }

        /* ── PAGE ─────────────────────────────────────── */
        @page {
            size: A4;
            margin: 0;
        }

        /* ── RESET ────────────────────────────────────── */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cabinet Grotesk', 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #2D2D2D;
            background: #FAFAF9;
            line-height: 1.5;
        }

        /* ── WATERMARK (draft) ────────────────────────── */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            font-size: 72px;
            font-weight: 900;
            color: rgba(255, 66, 11, 0.04);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            z-index: 0;
        }

        /* ── RUNNING FOOTER (every page) ─────────────── */
        .running-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 32px;
            background: #0A0A0A;
            border-top: 2px solid #FF420B;
            padding: 0 52px;
        }

        .running-footer-table {
            display: table;
            width: 100%;
            height: 32px;
        }

        .running-footer-left {
            display: table-cell;
            vertical-align: middle;
            font-family: 'Geist Mono', monospace;
            font-size: 7px;
            color: #3A3A3A;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .running-footer-brand {
            color: #FAFAF9;
            font-weight: 600;
        }

        .running-footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-family: 'Geist Mono', monospace;
            font-size: 7px;
            color: #FF420B;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        /* ── COVER ────────────────────────────────────── */
        .cover {
            background: #0A0A0A;
            position: relative;
            height: 290mm;
            page-break-after: always;
            overflow: hidden;
        }

        /* Ember top stripe */
        .cover-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #FF420B;
        }

        /* Decorative background letterform */
        .cover-bg-mark {
            position: absolute;
            bottom: 80px;
            right: -16px;
            font-family: 'Cabinet Grotesk', sans-serif;
            font-size: 200px;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.025);
            letter-spacing: -0.06em;
            line-height: 1;
        }

        /* Header row: brand + meta */
        .cover-head {
            position: absolute;
            top: 48px;
            left: 52px;
            right: 52px;
        }

        .cover-head-table {
            display: table;
            width: 100%;
        }

        .cover-brand-cell {
            display: table-cell;
            vertical-align: top;
        }

        .cover-meta-cell {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }

        .cover-brand-name {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #FAFAF9;
            margin-top: 10px;
        }

        .cover-brand-tagline {
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #FF420B;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .cover-ref {
            font-family: 'Geist Mono', monospace;
            font-size: 9px;
            color: #FF420B;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .cover-date {
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #3A3A3A;
            letter-spacing: 0.08em;
            margin-top: 4px;
        }

        /* Title area — vertically centered in cover */
        .cover-center {
            position: absolute;
            bottom: 148px;
            left: 52px;
            right: 52px;
        }

        .cover-eyebrow {
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #C8A87C;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .cover-title {
            font-size: 38px;
            font-weight: 900;
            color: #FAFAF9;
            line-height: 1.08;
            letter-spacing: -0.03em;
            max-width: 460px;
        }

        /* Bottom info band */
        .cover-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #1C1C1C;
            padding: 22px 52px 48px;
        }

        .cover-bottom-table {
            display: table;
            width: 100%;
        }

        .cover-info-cell {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
            padding-right: 20px;
        }

        .cover-info-cell:last-child {
            padding-right: 0;
            text-align: right;
        }

        .cover-info-label {
            font-family: 'Geist Mono', monospace;
            font-size: 7px;
            color: #3A3A3A;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .cover-info-name {
            font-size: 13px;
            font-weight: 700;
            color: #FAFAF9;
            letter-spacing: -0.01em;
        }

        .cover-info-sub {
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #484848;
            margin-top: 3px;
        }

        .cover-info-accent {
            font-size: 11px;
            font-weight: 600;
            color: #C8A87C;
            letter-spacing: 0.01em;
        }

        /* ── CONTENT ──────────────────────────────────── */
        .content {
            padding: 40px 52px 64px;
        }

        /* Content top bar — appears on first content page */
        .content-topbar {
            display: table;
            width: 100%;
            padding-bottom: 18px;
            border-bottom: 1px solid #E8E6E3;
            margin-bottom: 0;
        }

        .content-topbar-brand {
            display: table-cell;
            vertical-align: middle;
            font-size: 10px;
            font-weight: 800;
            color: #0A0A0A;
            letter-spacing: -0.02em;
        }

        .content-topbar-ref {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #B0B0B0;
            letter-spacing: 0.08em;
        }

        /* ── SECTIONS ─────────────────────────────────── */
        .section {
            margin-top: 34px;
        }

        .section-head {
            display: table;
            width: 100%;
            border-bottom: 1px solid #E8E6E3;
            padding-bottom: 9px;
            margin-bottom: 16px;
        }

        .section-num {
            display: table-cell;
            width: 36px;
            vertical-align: bottom;
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            font-weight: 600;
            color: #FF420B;
            letter-spacing: 0.12em;
        }

        .section-title {
            display: table-cell;
            vertical-align: bottom;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #B0B0B0;
        }

        /* ── SCOPE ────────────────────────────────────── */
        .scope-grid {
            display: table;
            width: 100%;
        }

        .scope-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 24px;
        }

        .scope-col:last-child {
            padding-right: 0;
            padding-left: 24px;
            border-left: 1px solid #E8E6E3;
        }

        .scope-q {
            font-size: 8px;
            font-weight: 700;
            color: #0A0A0A;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
            padding-left: 10px;
            border-left: 2px solid #FF420B;
        }

        .scope-body {
            font-size: 9.5px;
            color: #6B6B6B;
            line-height: 1.65;
        }

        /* ── INCLUDES / EXCLUDES ──────────────────────── */
        .ie-grid {
            display: table;
            width: 100%;
        }

        .ie-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .ie-col:last-child {
            padding-right: 0;
            padding-left: 20px;
            border-left: 1px solid #E8E6E3;
        }

        .ie-head {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-left: 10px;
        }

        .ie-head-incl {
            color: #16a34a;
            border-left: 2px solid #16a34a;
        }

        .ie-head-excl {
            color: #dc2626;
            border-left: 2px solid #dc2626;
        }

        .ie-list {
            list-style: none;
        }

        .ie-list li {
            font-size: 9px;
            color: #6B6B6B;
            padding: 4px 0 4px 16px;
            position: relative;
            border-bottom: 1px solid #F0EBE3;
            line-height: 1.5;
        }

        .ie-list li::before {
            position: absolute;
            left: 0;
            font-weight: 700;
            font-size: 9px;
        }

        .incl-list li::before {
            content: "✓";
            color: #16a34a;
        }

        .excl-list li::before {
            content: "×";
            color: #dc2626;
        }

        /* ── STAGES ───────────────────────────────────── */
        .stage-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            border-bottom: 1px solid #F0EBE3;
            padding: 8px 0;
        }

        .stage-num {
            display: table-cell;
            width: 48px;
            vertical-align: middle;
            font-family: 'Geist Mono', monospace;
            font-size: 22px;
            font-weight: 700;
            color: rgba(255, 66, 11, 0.18);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .stage-body {
            display: table-cell;
            vertical-align: middle;
        }

        .stage-name {
            font-size: 10px;
            font-weight: 700;
            color: #0A0A0A;
            letter-spacing: -0.01em;
        }

        .stage-desc {
            font-size: 9px;
            color: #6B6B6B;
            margin-top: 2px;
            line-height: 1.5;
        }

        .stage-dur {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
            padding-left: 12px;
        }

        .stage-dur-badge {
            font-family: 'Geist Mono', monospace;
            font-size: 8px;
            color: #9E7E54;
            font-weight: 600;
            letter-spacing: 0.04em;
            background: #F5EDE3;
            padding: 3px 8px;
        }

        /* ── COST TABLE ───────────────────────────────── */
        .cost-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cost-table thead th {
            padding: 9px 12px;
            font-family: 'Geist Mono', monospace;
            font-size: 7px;
            font-weight: 600;
            color: #FAFAF9;
            background: #0A0A0A;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-align: left;
            border-bottom: 2px solid #FF420B;
        }

        .cost-table thead th.right {
            text-align: right;
        }

        .cost-table tbody tr:nth-child(odd) {
            background: #FAFAF9;
        }

        .cost-table tbody tr:nth-child(even) {
            background: #F5F4F2;
        }

        .cost-table tbody td {
            padding: 8px 12px;
            font-size: 9.5px;
            color: #2D2D2D;
            border-bottom: 1px solid #ECEAE7;
        }

        .cost-table tbody td.right {
            text-align: right;
            font-family: 'Geist Mono', monospace;
        }

        .cost-table tbody td.muted {
            color: #B0B0B0;
            font-size: 8.5px;
            font-family: 'Geist Mono', monospace;
        }

        /* ── TOTALS ───────────────────────────────────── */
        .totals-wrap {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .totals-spacer {
            display: table-cell;
            width: 55%;
        }

        .totals-block {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .totals-row {
            display: table;
            width: 100%;
            padding: 5px 12px;
            border-bottom: 1px solid #ECEAE7;
        }

        .tr-label {
            display: table-cell;
            font-size: 9px;
            color: #6B6B6B;
        }

        .tr-amount {
            display: table-cell;
            text-align: right;
            font-family: 'Geist Mono', monospace;
            font-size: 9px;
            font-weight: 600;
            color: #2D2D2D;
        }

        .totals-total {
            display: table;
            width: 100%;
            background: #0A0A0A;
            padding: 10px 12px;
            margin-top: 2px;
        }

        .tt-label {
            display: table-cell;
            font-size: 10px;
            font-weight: 700;
            color: #FAFAF9;
            letter-spacing: -0.01em;
        }

        .tt-amount {
            display: table-cell;
            text-align: right;
            font-family: 'Geist Mono', monospace;
            font-size: 15px;
            font-weight: 700;
            color: #FF420B;
            letter-spacing: -0.02em;
        }

        /* ── NOTES ────────────────────────────────────── */
        .notes-box {
            padding: 14px 18px 14px 20px;
            border-left: 3px solid #C8A87C;
            background: #FAF5EE;
            font-size: 9.5px;
            color: #5A4A3A;
            line-height: 1.65;
        }

        /* ── SIGNATURES ───────────────────────────────── */
        .sig-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
        }

        .sig-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 36px;
        }

        .sig-col:last-child {
            padding-right: 0;
            padding-left: 36px;
        }

        .sig-area {
            height: 52px;
            border-bottom: 1px solid #2D2D2D;
            margin-bottom: 8px;
        }

        .sig-name {
            font-size: 9px;
            font-weight: 700;
            color: #0A0A0A;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .sig-role {
            font-family: 'Geist Mono', monospace;
            font-size: 7.5px;
            color: #B0B0B0;
            letter-spacing: 0.06em;
            margin-top: 4px;
        }

        .sig-signed {
            background: #F0FDF4;
            border-top: 2px solid #16a34a;
            padding: 10px 14px;
            height: 52px;
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }

        .sig-signed-name {
            display: table-cell;
            vertical-align: middle;
            font-size: 13px;
            font-style: italic;
            color: #166534;
            font-weight: 500;
        }
    </style>
</head>

<body>

    @if ($proposal->status->value === 'draft')
        <div class="watermark">Borrador</div>
    @endif

    {{-- ── RUNNING FOOTER (every page) ────────────── --}}
    <div class="running-footer">
        <div class="running-footer-table">
            <div class="running-footer-left">
                <span class="running-footer-brand">noehassiel</span>
                &nbsp;·&nbsp; artisan developer
            </div>
            <div class="running-footer-right">{{ $proposal->number }}</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         COVER
    ════════════════════════════════════════ --}}
    <div class="cover">
        <div class="cover-stripe"></div>

        {{-- Decorative background mark --}}
        <div class="cover-bg-mark">n/h</div>

        {{-- Brand + proposal reference --}}
        <div class="cover-head">
            <div class="cover-head-table">
                <div class="cover-brand-cell">
                    <img src="{{ public_path('logo/isotipo_dark_ember.svg') }}"
                        width="38" height="38" alt="noehassiel"
                        style="display:block;margin-bottom:10px;" />
                    <div class="cover-brand-name">noehassiel</div>
                    <div class="cover-brand-tagline">artisan developer</div>
                </div>
                <div class="cover-meta-cell">
                    <div class="cover-ref">{{ $proposal->number }}</div>
                    <div class="cover-date">{{ now()->translatedFormat('d \d\e F, Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="cover-center">
            <div class="cover-eyebrow">Propuesta de proyecto</div>
            <div class="cover-title">{{ $proposal->title }}</div>
        </div>

        {{-- Client / type / validity --}}
        <div class="cover-bottom">
            <div class="cover-bottom-table">
                <div class="cover-info-cell">
                    <div class="cover-info-label">Preparado para</div>
                    <div class="cover-info-name">{{ $proposal->recipient()?->name }}</div>
                    @if ($proposal->recipient()?->email)
                        <div class="cover-info-sub">{{ $proposal->recipient()?->email }}</div>
                    @endif
                </div>
                <div class="cover-info-cell">
                    <div class="cover-info-label">Tipo de proyecto</div>
                    <div class="cover-info-accent">{{ $proposal->project_type->label() }}</div>
                </div>
                <div class="cover-info-cell">
                    @if ($proposal->valid_until)
                        <div class="cover-info-label">Válido hasta</div>
                        <div class="cover-info-name">{{ $proposal->valid_until->format('d / m / Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         CONTENT
    ════════════════════════════════════════ --}}
    <div class="content">

        {{-- Page top reference bar --}}
        <div class="content-topbar">
            <div class="content-topbar-brand">noehassiel</div>
            <div class="content-topbar-ref">
                {{ $proposal->number }} &nbsp;·&nbsp; {{ now()->format('d/m/Y') }}
            </div>
        </div>

        @php $n = 0; @endphp

        {{-- ── 01 · Alcance ─────────────────────────── --}}
        @php $n++; @endphp
        <div class="section">
            <div class="section-head">
                <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                <div class="section-title">Alcance del proyecto</div>
            </div>
            <div class="scope-grid">
                <div class="scope-col">
                    <div class="scope-q">¿Qué vamos a hacer?</div>
                    <div class="scope-body">{{ $proposal->what_we_do }}</div>
                </div>
                <div class="scope-col">
                    <div class="scope-q">¿Cómo lo vamos a hacer?</div>
                    <div class="scope-body">{{ $proposal->how_we_do_it }}</div>
                </div>
            </div>
        </div>

        {{-- ── 02 · Alcance detallado ────────────────── --}}
        @if ($proposal->includes || $proposal->excludes)
            @php $n++; @endphp
            <div class="section">
                <div class="section-head">
                    <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                    <div class="section-title">Alcance detallado</div>
                </div>
                <div class="ie-grid">
                    @if ($proposal->includes)
                        <div class="ie-col">
                            <div class="ie-head ie-head-incl">Incluye</div>
                            <ul class="ie-list incl-list">
                                @foreach ($proposal->includes as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($proposal->excludes)
                        <div class="ie-col">
                            <div class="ie-head ie-head-excl">No incluye</div>
                            <ul class="ie-list excl-list">
                                @foreach ($proposal->excludes as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── 03 · Etapas ──────────────────────────── --}}
        @if ($proposal->stages->isNotEmpty())
            @php $n++; @endphp
            <div class="section">
                <div class="section-head">
                    <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                    <div class="section-title">Plan de trabajo — Etapas</div>
                </div>
                @foreach ($proposal->stages as $stage)
                    <div class="stage-row">
                        <div class="stage-num">{{ sprintf('%02d', $loop->iteration) }}</div>
                        <div class="stage-body">
                            <div class="stage-name">{{ $stage->name }}</div>
                            @if ($stage->description)
                                <div class="stage-desc">{{ $stage->description }}</div>
                            @endif
                        </div>
                        @if ($stage->duration)
                            <div class="stage-dur">
                                <span class="stage-dur-badge">{{ $stage->duration }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── 04 · Inversión ───────────────────────── --}}
        @if ($proposal->items->isNotEmpty())
            @php $n++; @endphp
            <div class="section">
                <div class="section-head">
                    <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                    <div class="section-title">Desglose de inversión</div>
                </div>
                <table class="cost-table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="right" style="width:52px;">Cant.</th>
                            <th class="right" style="width:104px;">Precio unit.</th>
                            <th class="right" style="width:104px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proposal->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="right muted">
                                    {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                                </td>
                                <td class="right">
                                    {{ $proposal->currency }} {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="right">
                                    {{ $proposal->currency }} {{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="totals-wrap">
                    <div class="totals-spacer"></div>
                    <div class="totals-block">
                        <div class="totals-row">
                            <div class="tr-label">Subtotal</div>
                            <div class="tr-amount">
                                {{ $proposal->currency }} {{ number_format($proposal->subtotal, 2) }}
                            </div>
                        </div>
                        @if ($proposal->discount > 0)
                            <div class="totals-row">
                                <div class="tr-label">Descuento</div>
                                <div class="tr-amount">− {{ number_format($proposal->discount, 2) }}</div>
                            </div>
                        @endif
                        @if ($proposal->tax_rate > 0)
                            @php $tax = ($proposal->subtotal - $proposal->discount) * ($proposal->tax_rate / 100); @endphp
                            <div class="totals-row">
                                <div class="tr-label">IVA ({{ number_format($proposal->tax_rate, 0) }}%)</div>
                                <div class="tr-amount">{{ number_format($tax, 2) }}</div>
                            </div>
                        @endif
                        <div class="totals-total">
                            <div class="tt-label">Total</div>
                            <div class="tt-amount">
                                {{ $proposal->currency }} {{ number_format($proposal->total, 2) }}
                            </div>
                        </div>
                        @foreach ($proposal->paymentSchedule() as $payment)
                            <div class="totals-row">
                                <div class="tr-label" @if ($payment['paid_at']) style="color:#15803D;" @endif>
                                    {{ $payment['label'] }} {{ $payment['percent'] }}% · {{ $payment['paid_at'] ? 'pagado el '.$payment['paid_at']->format('d/m/Y') : Str::lower($payment['description']) }}
                                </div>
                                <div class="tr-amount" @if ($payment['paid_at']) style="color:#15803D;" @endif>{{ $proposal->currency }} {{ number_format($payment['amount'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 05 · Términos ────────────────────────── --}}
        @if ($proposal->notes)
            @php $n++; @endphp
            <div class="section">
                <div class="section-head">
                    <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                    <div class="section-title">Términos y condiciones</div>
                </div>
                <div class="notes-box">{{ $proposal->notes }}</div>
            </div>
        @endif

        {{-- ── 06 · Firmas ──────────────────────────── --}}
        @php $n++; @endphp
        <div class="section">
            <div class="section-head">
                <div class="section-num">{{ sprintf('%02d', $n) }}</div>
                <div class="section-title">Firmas y aceptación</div>
            </div>
            <div class="sig-grid">
                <div class="sig-col">
                    <div class="sig-area"></div>
                    <div class="sig-name">noehassiel</div>
                    <div class="sig-role">artisan developer · noehassiel.dev</div>
                </div>
                <div class="sig-col">
                    @if ($proposal->client_signed_at)
                        <div class="sig-signed">
                            <div class="sig-signed-name">{{ $proposal->client_signature }}</div>
                        </div>
                        <div class="sig-name">{{ $proposal->recipient()?->name }}</div>
                        <div class="sig-role">
                            Firmado el {{ $proposal->client_signed_at->format('d/m/Y') }}
                        </div>
                    @else
                        <div class="sig-area"></div>
                        <div class="sig-name">{{ $proposal->recipient()?->name }}</div>
                        <div class="sig-role">Nombre y firma del cliente</div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end .content --}}

</body>

</html>
