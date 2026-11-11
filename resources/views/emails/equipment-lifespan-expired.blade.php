<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Equipment lifespan reached - Lourdes College Laboratory</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; overflow:hidden;">
            <div style="padding:28px 32px; background:linear-gradient(135deg, #2f1636, #d91c77); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; opacity:.9; margin-bottom:8px;">Lourdes College Laboratory</div>
                <h1 style="margin:0; font-size:25px; line-height:1.2;">Equipment Lifespan Reached</h1>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $supplierName }},</p>
                <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">The recorded lifespan of the following equipment supplied to Lourdes College has been reached and may require replacement, maintenance, or further assessment.</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border-collapse:collapse; border:1px solid #e5e7eb;">
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:38%;">Equipment name</td><td style="padding:12px 16px;">{{ $equipmentName }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Supplier name</td><td style="padding:12px 16px;">{{ $supplierName }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Brand</td><td style="padding:12px 16px;">{{ $brand ?: 'Not specified' }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Model</td><td style="padding:12px 16px;">{{ $model ?: 'Not specified' }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Serial number</td><td style="padding:12px 16px;">{{ $serialNumber ?: 'Not specified' }}</td></tr>
                    <tr><td style="padding:12px 16px; background:#f9fafb; font-weight:bold;">Manufacturing date</td><td style="padding:12px 16px;">{{ $manufacturingDate ?: 'Not specified' }}</td></tr>
                </table>

                @if (count($campusContactEmails) > 0)
                    <p style="margin:0 0 12px; font-size:15px; line-height:1.7;">For follow-up, contact the campus laboratory staff:</p>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border-collapse:collapse; border:1px solid #e5e7eb;">
                        @foreach ($campusContactEmails as $index => $email)
                            <tr>
                                <td style="padding:12px 16px; {{ $loop->odd ? 'background:#f9fafb;' : '' }} font-size:14px; color:#1f2937;">{{ $email }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">Please contact the campus laboratory staff if replacement, maintenance, or further assessment is required.</p>
                <p style="margin:24px 0 0; padding:14px 16px; background:#fff7ed; color:#9a3412; font-size:13px; line-height:1.6; border-radius:10px;"><strong>Automated message:</strong> This email was generated automatically by the Lourdes College Laboratory inventory system.</p>
            </div>

            @include('emails.partials.privacy-footer')
        </div>
    </div>
</body>
</html>