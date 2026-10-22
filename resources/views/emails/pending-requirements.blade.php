<x-emails.layout :title="trans_choice('portal.reminder.subject', $pendingCount, ['count' => $pendingCount])">
    <h1 class="greeting" style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:40px;font-weight:900;color:#FAFAF9;letter-spacing:-0.04em;line-height:1.02;margin:0 0 30px;">
        {{ __('portal.reminder.greeting') }}<br>
        <span style="color:#FF420B;">{{ str($user->name)->before(' ') }}.</span>
    </h1>

    <p style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#8A8A8A;line-height:1.8;margin:0 0 36px;">
        {{ __('portal.reminder.body') }}
    </p>

    @foreach ($projects as $project)
        <div style="font-family:'Geist Mono','Courier New',monospace;font-size:9px;color:#FF420B;letter-spacing:0.22em;text-transform:uppercase;margin:0 0 10px;">
            {{ $project->name }}
        </div>

        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 32px;">
            @foreach ($project->requirements as $requirement)
                <tr>
                    <td style="font-family:'Cabinet Grotesk',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#FAFAF9;padding:9px 0;border-top:1px solid #1A1A1A;">
                        {{ $requirement->title }}
                    </td>
                    <td align="right" style="font-family:'Geist Mono','Courier New',monospace;font-size:11px;color:#6B6B6B;padding:9px 0;border-top:1px solid #1A1A1A;white-space:nowrap;">
                        @if ($requirement->due_on)
                            {{ __('portal.project.due_on', ['date' => $requirement->due_on->translatedFormat('j M')]) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <table cellpadding="0" cellspacing="0" role="presentation" style="margin:8px 0 20px;">
        <tr>
            <td style="background-color:#FF420B;" align="center">
                <a href="{{ route('portal.home') }}" target="_blank" style="display:inline-block;background-color:#FF420B;color:#ffffff;font-family:'Geist Mono','Courier New',monospace;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;text-decoration:none;padding:14px 32px;">
                    → &nbsp;{{ __('portal.reminder.action') }}
                </a>
            </td>
        </tr>
    </table>

    <div style="font-family:'Geist Mono','Courier New',monospace;font-size:10px;color:#4A4A4A;letter-spacing:0.08em;line-height:1.7;">
        {{ __('portal.reminder.footnote') }}
    </div>
</x-emails.layout>
