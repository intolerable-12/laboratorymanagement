<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $decisionLabel }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08);">
            <div style="padding:28px 32px; background:linear-gradient(135deg, #0f172a, #1d4ed8); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; opacity:.85; margin-bottom:8px;">LabCentral</div>
                <h1 style="margin:0; font-size:26px; line-height:1.2;">{{ $decisionLabel }}</h1>
                <p style="margin:10px 0 0; font-size:15px; opacity:.92;">Borrow request {{ $borrowTransaction->borrow_no }}</p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $recipientName }},</p>

                <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">{{ $bodyMessage }}</p>

                @if ($actorName)
                    <p style="margin:0 0 16px; font-size:14px; color:#4b5563;">Handled by {{ $reviewerRole }} {{ $actorName }}.</p>
                @endif

                @if ($reason)
                    <div style="margin:24px 0; padding:18px 20px; background:#fef2f2; border:1px solid #fecaca; border-radius:14px;">
                        <div style="font-size:13px; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; color:#991b1b; margin-bottom:8px;">Reason</div>
                        <div style="font-size:15px; line-height:1.7; color:#7f1d1d;">{{ $reason }}</div>
                    </div>
                @endif

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0; border-collapse:collapse; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:40%;">Borrowed at</td>
                        <td style="padding:12px 16px;">{{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:40%;">Due at</td>
                        <td style="padding:12px 16px;">{{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:40%;">Status</td>
                        <td style="padding:12px 16px;">{{ $borrowTransaction->status }}</td>
                    </tr>
                </table>

                <p style="margin:0; font-size:14px; line-height:1.7; color:#4b5563;">You can log in to the system to review the latest status of this request.</p>
            </div>
        </div>
    </div>
</body>
</html>