<x-emails.layout title="Por revisar: {{ $requirement->title }}">
    <div style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#FF420B;letter-spacing:0.22em;text-transform:uppercase;margin:0 0 12px;">
        {{ $requirement->project->client->name }} · {{ $requirement->project->name }}
    </div>

    <h1 class="greeting" style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:32px;font-weight:900;color:#FAFAF9;letter-spacing:-0.04em;line-height:1.1;margin:0 0 24px;">
        {{ $requirement->title }}
    </h1>

    <p style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#8A8A8A;line-height:1.8;margin:0 0 36px;">
        {{ $markedBy->name }} ({{ $markedBy->email }}) dice que ya lo subió a su carpeta compartida. Revísalo y márcalo como recibido o regrésalo a pendiente.
    </p>

    <table cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="background-color:#FF420B;" align="center">
                <a href="{{ route('projects.show', $requirement->project) }}" target="_blank" style="display:inline-block;background-color:#FF420B;color:#ffffff;font-family:'Geist Mono','Courier New',monospace;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;text-decoration:none;padding:14px 32px;">
                    → &nbsp;revisar en el proyecto
                </a>
            </td>
        </tr>
    </table>
</x-emails.layout>
