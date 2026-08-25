<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08);">
            <div style="padding:28px 32px; background:linear-gradient(135deg, #2f1636, #d91c77); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.08em; opacity:.9; margin-bottom:8px;">LabCentral</div>
                <h1 style="margin:0; font-size:26px; line-height:1.2;">{{ $headline }}</h1>
                <p style="margin:10px 0 0; font-size:15px; opacity:.92;">{{ $requestType }} {{ $requestNumber }}</p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $recipientName }},</p>

                <p style="margin:0 0 18px; font-size:15px; line-height:1.7;">{{ $bodyMessage }}</p>

                @if (! empty($summaryRows))
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border-collapse:collapse; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden;">
                        @foreach ($summaryRows as $row)
                            <tr>
                                <td style="padding:12px 16px; background:#f9fafb; font-weight:bold; width:38%;">{{ $row['label'] }}</td>
                                <td style="padding:12px 16px;">{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                <div style="margin:28px 0 20px;">
                    <a href="{{ $actionUrl }}" style="display:inline-block; background:#d91c77; color:#fff; text-decoration:none; padding:12px 20px; border-radius:999px; font-weight:bold;">{{ $actionLabel }}</a>
                </div>

                <p style="margin:0; font-size:14px; line-height:1.7; color:#4b5563;">You can also review the request by logging in to the system.</p>
            </div>

            @include('emails.partials.privacy-footer')
        </div>
    </div>
</body>
</html>
