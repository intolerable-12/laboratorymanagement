<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isApproved ? 'Account approved' : 'Account created' }}</title>
</head>
<body style="margin:0; padding:0; background:#fdf3f8; font-family:Arial, Helvetica, sans-serif; color:#35152f;">
    @php
        $recipientName = trim(collect([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])->filter()->implode(' '));
        $headline = $isApproved ? 'Account approved' : 'Account created';
        $subheading = $isApproved ? 'Your registration request has been reviewed.' : 'Your LabCentral access is ready.';
        $message = $isApproved
            ? 'Your student account registration has been approved by the laboratory coordinator. You may now sign in using your @lccdo.edu.ph Google account.'
            : 'A LabCentral account has been created for you by the laboratory coordinator. You may now sign in using the email address registered to your account.';
    @endphp

    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #f1c9dd; border-radius:20px; overflow:hidden; box-shadow:0 12px 32px rgba(91,16,66,.12);">
            <div style="padding:32px; background:linear-gradient(135deg, #2f1636 0%, #8f1d61 52%, #d91c77 100%); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.1em; opacity:.88; margin-bottom:10px;">LabCentral account</div>
                <h1 style="margin:0; font-size:28px; line-height:1.2;">{{ $headline }}</h1>
                <p style="margin:12px 0 0; font-size:15px; opacity:.92;">{{ $subheading }}</p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $recipientName ?: 'LabCentral user' }},</p>

                <div style="padding:20px; border:1px solid #f4d7e5; border-radius:16px; background:#fff8fc;">
                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:#9d174d; font-weight:bold; margin-bottom:8px;">Account notification</div>
                    <p style="margin:0; font-size:15px; line-height:1.75; color:#4b1640;">{{ $message }}</p>
                </div>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border-collapse:collapse; border:1px solid #f4d7e5;">
                    <tr>
                        <td style="padding:12px 16px; background:#fff8fc; color:#6b7280; font-weight:bold; width:38%;">User ID</td>
                        <td style="padding:12px 16px;">{{ $user->userID }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px; background:#fff8fc; color:#6b7280; font-weight:bold;">Role</td>
                        <td style="padding:12px 16px;">{{ $user->role?->role_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px; background:#fff8fc; color:#6b7280; font-weight:bold;">Department</td>
                        <td style="padding:12px 16px;">{{ $user->department?->department_name ?? '—' }}</td>
                    </tr>
                </table>

                <div style="margin:28px 0 8px;">
                    <a href="{{ route('login') }}" style="display:inline-block; background:#d91c77; color:#fff; text-decoration:none; padding:13px 22px; border-radius:999px; font-weight:bold;">Sign in to LabCentral</a>
                </div>

                <p style="margin:18px 0 0; font-size:14px; line-height:1.7; color:#6b7280;">For your security, never share your password by email. If you need assistance, please contact the laboratory coordinator or system administrator.</p>
            </div>

            @include('emails.partials.privacy-footer')
        </div>
    </div>
</body>
</html>
