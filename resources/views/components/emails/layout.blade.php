@props(['title'])

{{-- Branded frame for portal emails: Obsidian background, Ember accent bar, logo and footer --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@400,700,800,900&display=swap" rel="stylesheet">
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #0A0A0A; -webkit-text-size-adjust: 100%; }
        table { border-collapse: collapse; }
        img { border: 0; display: block; }
        a { text-decoration: none; }

        @media only screen and (max-width: 600px) {
            .greeting { font-size: 28px !important; margin-bottom: 22px !important; }
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
                        <td width="2" bgcolor="#FF420B" style="background-color:#FF420B;" valign="top">
                            <div style="width:2px;min-height:360px;font-size:0;line-height:0;">&nbsp;</div>
                        </td>

                        <td class="content-pad" style="padding:0 0 0 40px;" valign="top">
                            <div style="margin-bottom:48px;">
                                <img src="{{ asset('logo/isotipo_dark_ember_animated.svg') }}" width="40" height="40" alt="noehassiel" />
                            </div>

                            {{ $slot }}

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:60px;border-top:1px solid #141414;">
                                <tr>
                                    <td style="padding-top:22px;" valign="middle">
                                        <div style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:11px;font-weight:800;color:#FAFAF9;letter-spacing:-0.02em;">noehassiel</div>
                                        <div style="font-family:'Geist Mono','Courier New',monospace;font-size:8px;color:#303030;letter-spacing:0.12em;text-transform:uppercase;margin-top:3px;">Puebla, México</div>
                                    </td>
                                    <td align="right" style="padding-top:22px;" valign="middle">
                                        <div style="font-family:'Geist Mono','Courier New',monospace;font-size:8px;color:#FF420B;letter-spacing:0.18em;text-transform:uppercase;">artisan developer</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
