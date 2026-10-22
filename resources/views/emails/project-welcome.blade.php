<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenido a tu proyecto</title>
</head>

<body style="margin:0;padding:32px 16px;background:#0A0A0A;font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;">
        <tr>
            <td style="padding-bottom:24px;font-family:'Courier New',monospace;font-size:12px;color:#FF420B;letter-spacing:0.04em;">
                noehassiel · artisan developer
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:16px;font-size:32px;font-weight:800;line-height:1.15;color:#FAFAF9;">
                Arrancamos, {{ $project->client->contact_name ?: $project->client->name }}.
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;font-size:16px;line-height:1.6;color:#B0B0B0;">
                Recibí tu anticipo y <strong style="color:#FAFAF9;">{{ $project->name }}</strong> ya es oficialmente un proyecto.
                Gracias por la confianza. Desde tu portal vas a poder ver en qué etapa vamos, subir lo que te pida y descargar tus facturas.
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;">
                <a href="{{ $portalUrl }}"
                    style="display:inline-block;background:#FF420B;color:#0A0A0A;font-weight:700;padding:14px 24px;border-radius:8px;text-decoration:none;">
                    {{ $invitationToken ? 'Crear mi acceso al portal' : 'Entrar a mi portal' }}
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:24px;font-size:16px;line-height:1.6;color:#B0B0B0;">
                En los próximos días te escribo para agendar el arranque. Si tienes cualquier duda, responde a este correo.
            </td>
        </tr>
        <tr>
            <td style="font-size:14px;font-weight:800;color:#FAFAF9;">noehassiel</td>
        </tr>
    </table>
</body>

</html>
