<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $project->name }} aprobado</title>
</head>

<body style="margin:0;padding:32px 16px;background:#0A0A0A;font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;">
        <tr>
            <td style="padding-bottom:24px;font-family:'Courier New',monospace;font-size:12px;color:#FF420B;letter-spacing:0.04em;">
                noehassiel · proyecto aprobado
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:8px;font-size:28px;font-weight:800;line-height:1.2;color:#FAFAF9;">{{ $project->client->name }} aprobó {{ $project->name }}</td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;font-size:14px;color:#B0B0B0;">
                {{ $project->approver?->name }} · {{ $project->approved_at->format('d/m/Y H:i') }} · ronda {{ min($project->current_round, $project->revision_rounds) }} de {{ $project->revision_rounds }}
            </td>
        </tr>
        <tr>
            <td style="padding:24px;background:#2D2D2D;border-radius:8px;font-size:16px;line-height:1.6;color:#FAFAF9;">
                Ya puede pagar la liquidación desde su portal. En cuanto se registre el pago, el proyecto queda listo para lanzarse.
            </td>
        </tr>
        <tr>
            <td style="padding:24px 0;">
                <a href="{{ route('projects.show', $project) }}"
                    style="display:inline-block;background:#FF420B;color:#0A0A0A;font-weight:700;padding:12px 20px;border-radius:8px;text-decoration:none;">Abrir el proyecto</a>
            </td>
        </tr>
    </table>
</body>

</html>
