<x-emails.layout :title="__('portal.invitation.mail_subject', ['client' => $invitation->client->name])">
    <h1 class="greeting" style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:40px;font-weight:900;color:#FAFAF9;letter-spacing:-0.04em;line-height:1.02;margin:0 0 30px;">
        {{ __('portal.invitation.mail_greeting') }}<br>
        <span style="color:#FF420B;">{{ $invitation->client->name }}.</span>
    </h1>

    <p style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#8A8A8A;line-height:1.8;margin:0 0 40px;">
        {{ __('portal.invitation.mail_body', ['client' => $invitation->client->name]) }}
    </p>

    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
        <tr>
            <td style="background-color:#FF420B;" align="center">
                <a href="{{ $acceptUrl }}" target="_blank" style="display:inline-block;background-color:#FF420B;color:#ffffff;font-family:'Geist Mono','Courier New',monospace;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;text-decoration:none;padding:14px 32px;">
                    → &nbsp;{{ __('portal.invitation.mail_action') }}
                </a>
            </td>
        </tr>
    </table>

    <div style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#4A4A4A;letter-spacing:0.08em;line-height:1.7;">
        {{ __('portal.invitation.mail_expiry', ['days' => \App\Models\ClientInvitation::EXPIRES_IN_DAYS]) }}
    </div>
</x-emails.layout>
