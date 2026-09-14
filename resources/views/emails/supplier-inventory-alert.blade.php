<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $alertType }} - Lourdes College Laboratory</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; overflow:hidden;">
            <div style="padding:28px 32px; background:linear-gradient(135deg, #2f1636, #d91c77); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; opacity:.9; margin-bottom:8px;">Lourdes College Laboratory</div>
                <h1 style="margin:0; font-size:25px; line-height:1.2;">{{ $alertType }} notice</h1>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $recipientName }},</p>
                <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">Lourdes College Laboratory is contacting you because one of its inventory records needs attention. You may be able to offer the laboratory this product or a suitable replacement.</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border-collapse:collapse; border:1px solid #e5e7eb;">
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:38%;">Item</td><td style="padding:12px 16px;">{{ $itemName }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Item code</td><td style="padding:12px 16px;">{{ $itemCode }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Laboratory</td><td style="padding:12px 16px;">{{ $laboratoryName }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Trigger</td><td style="padding:12px 16px;">{{ $triggerSummary }}</td></tr>
                </table>

                <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">{{ $requestMessage }}</p>
                <p style="margin:24px 0 0; padding:14px 16px; background:#fff7ed; color:#9a3412; font-size:13px; line-height:1.6; border-radius:10px;"><strong>Automated message:</strong> This email was generated automatically by the Lourdes College Laboratory inventory system. Please do not reply to this message.</p>
            </div>

            @include('emails.partials.privacy-footer')
        </div>
    </div>
</body>
</html>
