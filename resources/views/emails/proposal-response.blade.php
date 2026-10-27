@php
    $recipient = $proposal->recipient();
    $money = fn ($amount) => '$'.number_format((float) $amount, 2).' '.$proposal->currency;
    $headline = [
        'accepted' => 'Aceptó la propuesta',
        'changes_requested' => 'Pidió cambios',
        'rejected' => 'Canceló la propuesta',
        'payment_method_chosen' => 'Eligió cómo pagar',
        'paid' => 'Pagó el anticipo',
        'balance_method_chosen' => 'Eligió cómo pagar la liquidación',
        'balance_paid' => 'Liquidó el proyecto',
    ][$event];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $headline }}</title>
</head>

<body style="margin:0;padding:32px 16px;background:#0A0A0A;font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;">
        <tr>
            <td style="padding-bottom:24px;font-family:'Courier New',monospace;font-size:12px;color:#FF420B;letter-spacing:0.04em;">
                noehassiel · {{ $proposal->number }}
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:8px;font-size:32px;font-weight:800;line-height:1.15;color:#FAFAF9;">
                {{ $recipient?->name }} · {{ Str::lower($headline) }}
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;font-size:16px;color:#B0B0B0;">{{ $proposal->title }}</td>
        </tr>
        <tr>
            <td style="padding:24px;background:#2D2D2D;border-radius:8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#FAFAF9;line-height:1.6;">
                    @foreach (array_filter([
                        'Total' => $money($proposal->total),
                        'Factura' => $proposal->requires_invoice ? 'Sí, requiere factura' : 'No',
                        'Anticipo 50%' => in_array($event, ['accepted', 'payment_method_chosen'], true) ? $money($proposal->depositAmount()) : null,
                        'Liquidación 50%' => $event === 'balance_method_chosen' ? $money($proposal->balanceAmount()) : null,
                        'Forma de pago' => in_array($event, ['balance_method_chosen', 'balance_paid'], true) ? $proposal->balance_payment_method?->label() : $proposal->payment_method?->label(),
                        'Pagado' => match ($event) {
                            'paid' => $money($proposal->deposit_paid_amount),
                            'balance_paid' => $money($proposal->balance_paid_amount),
                            default => null,
                        },
                    ]) as $label => $value)
                        <tr>
                            <td style="padding:4px 16px 4px 0;font-family:'Courier New',monospace;font-size:12px;color:#B0B0B0;white-space:nowrap;vertical-align:top;">{{ $label }}</td>
                            <td style="padding:4px 0;">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @if (in_array($event, ['changes_requested', 'rejected'], true) && $proposal->client_response)
            <tr>
                <td style="padding:24px 0 8px;font-family:'Courier New',monospace;font-size:12px;color:#B0B0B0;">
                    {{ $event === 'changes_requested' ? 'Lo que pidió:' : 'Motivo:' }}
                </td>
            </tr>
            <tr>
                <td style="padding-bottom:24px;font-size:16px;line-height:1.6;color:#FAFAF9;white-space:pre-line;">{{ $proposal->client_response }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:24px 0;">
                <a href="{{ in_array($event, ['paid', 'balance_method_chosen', 'balance_paid'], true) && $proposal->project_id ? route('projects.show', $proposal->project_id) : route('proposals.show', $proposal) }}"
                    style="display:inline-block;background:#FF420B;color:#0A0A0A;font-weight:700;padding:12px 20px;border-radius:8px;text-decoration:none;">
                    {{ in_array($event, ['paid', 'balance_method_chosen', 'balance_paid'], true) ? 'Ver el proyecto' : 'Abrir la propuesta' }}
                </a>
            </td>
        </tr>
        <tr>
            <td style="font-family:'Courier New',monospace;font-size:12px;color:#6B6B6B;">
                Responde a este correo para contestarle directamente.
            </td>
        </tr>
    </table>
</body>

</html>
