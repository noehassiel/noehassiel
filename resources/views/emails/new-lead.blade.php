<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nuevo proyecto</title>
</head>

<body style="margin:0;padding:32px 16px;background:#0A0A0A;font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;">
        <tr>
            <td style="padding-bottom:24px;font-family:'Courier New',monospace;font-size:12px;color:#FF420B;letter-spacing:0.04em;">
                noehassiel · nuevo proyecto
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;font-size:32px;font-weight:800;line-height:1.15;color:#FAFAF9;">
                {{ $lead->name }}@if ($lead->company) <span style="color:#6B6B6B;">· {{ $lead->company }}</span>@endif
            </td>
        </tr>
        <tr>
            <td style="padding:24px;background:#2D2D2D;border-radius:8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#FAFAF9;line-height:1.6;">
                    @foreach ([
                        'Email' => $lead->email,
                        'Teléfono' => $lead->phone ?? '—',
                        'Tipo' => __('landing.form.types.'.$lead->project_type, locale: 'es'),
                        'Presupuesto' => __('landing.form.budgets.'.$lead->budget, locale: 'es'),
                        'Tiempo' => __('landing.form.timelines.'.$lead->timeline, locale: 'es'),
                        'Idioma' => strtoupper($lead->language),
                    ] as $label => $value)
                        <tr>
                            <td style="padding:4px 16px 4px 0;font-family:'Courier New',monospace;font-size:12px;color:#B0B0B0;white-space:nowrap;vertical-align:top;">{{ $label }}</td>
                            <td style="padding:4px 0;">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:24px 0;font-size:16px;line-height:1.6;color:#FAFAF9;white-space:pre-line;">{{ $lead->message }}</td>
        </tr>
        <tr>
            <td style="font-family:'Courier New',monospace;font-size:12px;color:#6B6B6B;">
                Responde a este correo para contestarle directamente.
            </td>
        </tr>
    </table>
</body>

</html>
