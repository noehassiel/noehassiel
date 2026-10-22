<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Proposal {{ $proposal->number }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@300,400,500,700,800,900&display=swap" rel="stylesheet">
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #0A0A0A;
            font-family: 'Cabinet Grotesk', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            display: block;
            max-width: 100%;
        }

        a {
            text-decoration: none;
        }

        /* ── Greeting ── */
        .greeting {
            font-size: 40px;
            font-weight: 900;
            color: #FAFAF9;
            letter-spacing: -0.04em;
            line-height: 1.02;
            margin: 0 0 30px;
        }

        /* ── Body copy ── */
        .body-copy {
            font-size: 14px;
            color: #505050;
            line-height: 1.8;
            margin: 0 0 10px;
        }

        /* ── Custom message ── */
        .custom-msg {
            font-size: 13px;
            color: #6A6A6A;
            line-height: 1.75;
            font-style: italic;
            border-left: 1px solid #C8A87C;
            padding-left: 16px;
            margin: 0 0 44px;
        }

        /* ── Proposal data ── */
        .prop-ref {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 9px;
            color: #FF420B;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin: 0 0 12px;
        }

        .prop-title {
            font-size: 21px;
            font-weight: 800;
            color: #FAFAF9;
            letter-spacing: -0.025em;
            line-height: 1.15;
            margin: 0 0 7px;
        }

        .prop-meta {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 10px;
            color: #333;
            letter-spacing: 0.04em;
            margin: 0 0 40px;
        }

        /* ── Investment ── */
        .invest-label {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 9px;
            color: #333;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin: 0 0 10px;
        }

        .invest-amount {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 46px;
            font-weight: 700;
            color: #FF420B;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .invest-currency {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 16px;
            color: #333;
            font-weight: 400;
        }

        /* ── Meta lines ── */
        .attach-line {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 10px;
            color: #363636;
            letter-spacing: 0.1em;
            margin: 0 0 10px;
        }

        .validity-line {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 10px;
            color: #2A2A2A;
            letter-spacing: 0.08em;
        }

        /* ── Footer ── */
        .footer-name {
            font-size: 11px;
            font-weight: 800;
            color: #FAFAF9;
            letter-spacing: -0.02em;
        }

        .footer-loc {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 8px;
            color: #303030;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .footer-tag {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 8px;
            color: #FF420B;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        /* ── Responsive ── */
        @media only screen and (max-width: 600px) {
            .greeting { font-size: 28px !important; margin-bottom: 22px !important; }
            .invest-amount { font-size: 34px !important; }
            .content-pad { padding: 0 0 0 22px !important; }
            .outer-wrap { padding: 36px 16px 48px !important; }
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#0A0A0A;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0A0A0A;">
        <tr>
            <td class="outer-wrap" align="center" style="padding:52px 20px 64px;">

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;">
                    <tr>

                        {{-- ── Left ember accent bar ── --}}
                        <td width="2" bgcolor="#FF420B" style="background-color:#FF420B;" valign="top">
                            <div style="width:2px;min-height:560px;font-size:0;line-height:0;">&nbsp;</div>
                        </td>

                        {{-- ── Main content ── --}}
                        <td class="content-pad" style="padding:0 0 0 40px;" valign="top">

                            {{-- Logo --}}
                            <div style="margin-bottom:48px;">
                                <img src="{{ asset('logo/isotipo_dark_ember_animated.svg') }}"
                                    width="40" height="40" alt="noehassiel"
                                    style="display:block;border:0;" />
                            </div>

                            {{-- Greeting --}}
                            @php
                                $firstName = $proposal->client->contact_name
                                    ? explode(' ', trim($proposal->client->contact_name))[0]
                                    : $proposal->recipient()?->name;
                            @endphp
                            <h1 class="greeting"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:40px;font-weight:900;color:#FAFAF9;letter-spacing:-0.04em;line-height:1.02;margin:0 0 30px;">
                                Hola,<br>
                                <span style="color:#FF420B;">{{ $firstName }}.</span>
                            </h1>

                            {{-- Body copy --}}
                            @if (!empty($customMessage))
                                <div class="custom-msg"
                                    style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:13px;color:#6A6A6A;line-height:1.75;font-style:italic;border-left:1px solid #C8A87C;padding-left:16px;margin:0 0 44px;">
                                    {{ $customMessage }}
                                </div>
                            @else
                                <p class="body-copy"
                                    style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#505050;line-height:1.8;margin:0 0 10px;">
                                    Te comparto la propuesta que preparé para tu proyecto. Está pensada para que
                                    tengas todo claro desde el primer vistazo: alcance, etapas y el detalle completo
                                    de inversión.
                                </p>
                                <p class="body-copy"
                                    style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#505050;line-height:1.8;margin:0 0 44px;">
                                    Si algo no queda claro o quieres hacer ajustes, con gusto lo conversamos.
                                </p>
                            @endif

                            {{-- Separator --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:36px;">
                                <tr>
                                    <td style="border-top:1px solid #181818;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Proposal ref --}}
                            <div class="prop-ref"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#FF420B;letter-spacing:0.22em;text-transform:uppercase;margin:0 0 12px;">
                                {{ $proposal->number }}
                            </div>

                            {{-- Proposal title --}}
                            <div class="prop-title"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:21px;font-weight:800;color:#FAFAF9;letter-spacing:-0.025em;line-height:1.15;margin:0 0 7px;">
                                {{ $proposal->title }}
                            </div>

                            {{-- Proposal meta --}}
                            <div class="prop-meta"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#333;letter-spacing:0.04em;margin:0 0 40px;">
                                {{ $proposal->project_type->label() }} · {{ $proposal->recipient()?->name }}
                            </div>

                            {{-- Investment label --}}
                            <div class="invest-label"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#333;letter-spacing:0.2em;text-transform:uppercase;margin:0 0 10px;">
                                inversión total
                            </div>

                            {{-- Investment amount — the hero --}}
                            <div style="margin:0 0 52px;">
                                <span class="invest-currency"
                                    style="font-family:'Geist Mono','Courier New',monospace;font-size:16px;color:#333;font-weight:400;">{{ $proposal->currency }}&nbsp;</span><span
                                    class="invest-amount"
                                    style="font-family:'Geist Mono','Courier New',monospace;font-size:46px;font-weight:700;color:#FF420B;letter-spacing:-0.02em;line-height:1;">{{ number_format($proposal->total, 2) }}</span>
                            </div>

                            {{-- Separator --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:24px;">
                                <tr>
                                    <td style="border-top:1px solid #181818;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Private link: the password travels on a separate channel --}}
                            <a href="{{ $proposal->publicUrl() }}"
                                style="display:inline-block;background-color:#FF420B;color:#0A0A0A;font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:15px;font-weight:800;padding:14px 26px;border-radius:12px;margin:0 0 16px;">
                                Ver propuesta
                            </a>
                            <div class="attach-line"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#6A6A6A;letter-spacing:0.1em;margin:0 0 10px;">
                                ↳ Es privada. Te mando la contraseña por WhatsApp.
                            </div>

                            {{-- Validity --}}
                            @if ($proposal->valid_until)
                                <div class="validity-line"
                                    style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#2A2A2A;letter-spacing:0.08em;">
                                    válido hasta · {{ $proposal->valid_until->translatedFormat('d \d\e F \d\e Y') }}
                                </div>
                            @endif

                            {{-- Footer --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-top:60px;border-top:1px solid #141414;">
                                <tr>
                                    <td style="padding-top:22px;" valign="middle">
                                        <div class="footer-name"
                                            style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:11px;font-weight:800;color:#FAFAF9;letter-spacing:-0.02em;">
                                            noehassiel</div>
                                        <div class="footer-loc"
                                            style="font-family:'Geist Mono','Courier New',monospace;font-size:8px;color:#303030;letter-spacing:0.12em;text-transform:uppercase;margin-top:3px;">
                                            Puebla, México</div>
                                    </td>
                                    <td align="right" style="padding-top:22px;" valign="middle">
                                        <div class="footer-tag"
                                            style="font-family:'Geist Mono','Courier New',monospace;font-size:8px;color:#FF420B;letter-spacing:0.18em;text-transform:uppercase;">
                                            artisan developer</div>
                                    </td>
                                </tr>
                            </table>

                        </td>{{-- end main content --}}
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>
