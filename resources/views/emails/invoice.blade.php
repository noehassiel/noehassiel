<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Factura {{ $invoice->number }}</title>
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

        .greeting {
            font-size: 40px;
            font-weight: 900;
            color: #FAFAF9;
            letter-spacing: -0.04em;
            line-height: 1.02;
            margin: 0 0 30px;
        }

        .body-copy {
            font-size: 14px;
            color: #505050;
            line-height: 1.8;
            margin: 0 0 10px;
        }

        .inv-ref {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 9px;
            color: #FF420B;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin: 0 0 12px;
        }

        .inv-title {
            font-size: 21px;
            font-weight: 800;
            color: #FAFAF9;
            letter-spacing: -0.025em;
            line-height: 1.15;
            margin: 0 0 7px;
        }

        .inv-meta {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 10px;
            color: #333;
            letter-spacing: 0.04em;
            margin: 0 0 36px;
        }

        .breakdown-label {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 9px;
            color: #2A2A2A;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 0 0 10px;
            border-bottom: 1px solid #141414;
            margin: 0 0 14px;
        }

        .breakdown-row-label {
            font-family: 'Cabinet Grotesk', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            font-size: 13px;
            color: #404040;
            padding: 7px 0;
        }

        .breakdown-row-value {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 13px;
            color: #404040;
            text-align: right;
            padding: 7px 0;
        }

        .breakdown-total-label {
            font-family: 'Cabinet Grotesk', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #FAFAF9;
            padding: 12px 0 0;
            border-top: 1px solid #1E1E1E;
        }

        .breakdown-total-value {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #FAFAF9;
            text-align: right;
            padding: 12px 0 0;
            border-top: 1px solid #1E1E1E;
        }

        .total-label {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 9px;
            color: #333;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin: 0 0 10px;
        }

        .total-currency {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 16px;
            color: #333;
            font-weight: 400;
        }

        .total-amount {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 46px;
            font-weight: 700;
            color: #FF420B;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .attach-line {
            font-family: 'Geist Mono', 'Courier New', monospace;
            font-size: 10px;
            color: #363636;
            letter-spacing: 0.1em;
            margin: 0 0 10px;
        }

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

        @media only screen and (max-width: 600px) {
            .greeting { font-size: 28px !important; margin-bottom: 22px !important; }
            .total-amount { font-size: 34px !important; }
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
                                $firstName = $invoice->client->contact_name
                                    ? explode(' ', trim($invoice->client->contact_name))[0]
                                    : $invoice->client->name;
                            @endphp
                            <h1 class="greeting"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:40px;font-weight:900;color:#FAFAF9;letter-spacing:-0.04em;line-height:1.02;margin:0 0 30px;">
                                Hola,<br>
                                <span style="color:#FF420B;">{{ $firstName }}.</span>
                            </h1>

                            {{-- Body copy --}}
                            <p class="body-copy"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#505050;line-height:1.8;margin:0 0 10px;">
                                Te comparto la factura correspondiente a nuestro trabajo. Adjunto encontrarás el ZIP
                                con todos los archivos fiscales (PDF + XML).
                            </p>
                            <p class="body-copy"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#505050;line-height:1.8;margin:0 0 44px;">
                                Si tienes alguna duda o necesitas algo adicional, con gusto te ayudo.
                            </p>

                            {{-- Separator --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:36px;">
                                <tr>
                                    <td style="border-top:1px solid #181818;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Invoice ref --}}
                            <div class="inv-ref"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#FF420B;letter-spacing:0.22em;text-transform:uppercase;margin:0 0 12px;">
                                {{ $invoice->number }}
                            </div>

                            {{-- Client name --}}
                            <div class="inv-title"
                                style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:21px;font-weight:800;color:#FAFAF9;letter-spacing:-0.025em;line-height:1.15;margin:0 0 7px;">
                                {{ $invoice->client->name }}
                            </div>

                            {{-- Invoice meta --}}
                            <div class="inv-meta"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#333;letter-spacing:0.04em;margin:0 0 36px;">
                                {{ $invoice->issue_date->translatedFormat('d \d\e F \d\e Y') }}
                                @if ($invoice->client->rfc)
                                    · {{ $invoice->client->rfc }}
                                @endif
                            </div>

                            {{-- Breakdown --}}
                            <div class="breakdown-label"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#2A2A2A;letter-spacing:0.18em;text-transform:uppercase;padding:0 0 10px;border-bottom:1px solid #141414;margin:0 0 4px;">
                                desglose
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:40px;">
                                {{-- Concept row --}}
                                <tr>
                                    <td class="breakdown-row-label"
                                        style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:13px;color:#404040;padding:8px 0;">
                                        {{ $invoice->notes ?: 'Servicios profesionales' }}
                                    </td>
                                    <td class="breakdown-row-value"
                                        style="font-family:'Geist Mono','Courier New',monospace;font-size:13px;color:#404040;text-align:right;padding:8px 0;white-space:nowrap;">
                                        {{ $invoice->currency }} {{ number_format($invoice->total - $invoice->tax, 2) }}
                                    </td>
                                </tr>
                                {{-- IVA row --}}
                                <tr>
                                    <td class="breakdown-row-label"
                                        style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:13px;color:#404040;padding:8px 0;">
                                        IVA 16%
                                    </td>
                                    <td class="breakdown-row-value"
                                        style="font-family:'Geist Mono','Courier New',monospace;font-size:13px;color:#404040;text-align:right;padding:8px 0;white-space:nowrap;">
                                        {{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}
                                    </td>
                                </tr>
                                {{-- Total row --}}
                                <tr>
                                    <td class="breakdown-total-label"
                                        style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;font-weight:700;color:#FAFAF9;padding:12px 0 0;border-top:1px solid #1E1E1E;">
                                        Total
                                    </td>
                                    <td class="breakdown-total-value"
                                        style="font-family:'Geist Mono','Courier New',monospace;font-size:14px;font-weight:700;color:#FAFAF9;text-align:right;padding:12px 0 0;border-top:1px solid #1E1E1E;white-space:nowrap;">
                                        {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Total hero --}}
                            <div class="total-label"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#333;letter-spacing:0.2em;text-transform:uppercase;margin:0 0 10px;">
                                total a pagar
                            </div>

                            <div style="margin:0 0 52px;">
                                <span class="total-currency"
                                    style="font-family:'Geist Mono','Courier New',monospace;font-size:16px;color:#333;font-weight:400;">{{ $invoice->currency }}&nbsp;</span><span
                                    class="total-amount"
                                    style="font-family:'Geist Mono','Courier New',monospace;font-size:46px;font-weight:700;color:#FF420B;letter-spacing:-0.02em;line-height:1;">{{ number_format($invoice->total, 2) }}</span>
                            </div>

                            {{-- Separator --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:24px;">
                                <tr>
                                    <td style="border-top:1px solid #181818;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Download button --}}
                            <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
                                <tr>
                                    <td style="background-color:#FF420B;" align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                                            href="{{ $downloadUrl }}" style="height:44px;width:220px;v-text-anchor:middle;" arcsize="0%" fillcolor="#FF420B">
                                            <w:anchorlock/>
                                            <center style="color:#ffffff;font-family:'Trebuchet MS',Tahoma,sans-serif;font-size:12px;letter-spacing:2px;">DESCARGAR ZIP</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ $downloadUrl }}" target="_blank"
                                            style="display:inline-block;background-color:#FF420B;color:#ffffff;font-family:'Geist Mono','Courier New',monospace;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;text-decoration:none;padding:14px 32px;mso-hide:all;">
                                            ↓ &nbsp;descargar zip
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            {{-- Attachment note --}}
                            <div class="attach-line"
                                style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#363636;letter-spacing:0.1em;margin:0 0 6px;">
                                ↳ ZIP adjunto a este correo (PDF + XML)
                            </div>

                            <div style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#2A2A2A;letter-spacing:0.08em;">
                                enlace válido por 30 días
                            </div>

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
