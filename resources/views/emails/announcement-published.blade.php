<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New announcement</title>
</head>
<body style="margin:0; padding:0; background:#fdf3f8; font-family:Arial, Helvetica, sans-serif; color:#35152f;">
    <div style="max-width:720px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #f1c9dd; border-radius:20px; overflow:hidden; box-shadow:0 12px 32px rgba(91,16,66,.12);">
            <div style="padding:32px; background:linear-gradient(135deg, #2f1636 0%, #8f1d61 52%, #d91c77 100%); color:#fff;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:.1em; opacity:.88; margin-bottom:10px;">LabCentral announcement</div>
                <h1 style="margin:0; font-size:28px; line-height:1.2;">{{ $announcement->title }}</h1>
                <p style="margin:12px 0 0; font-size:15px; opacity:.92;">A new notice is available for you.</p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $recipientName }},</p>

                @if ($announcement->coverImageUrl())
                    <img src="{{ $announcement->coverImageUrl() }}" alt="{{ $announcement->title }}" style="display:block; width:100%; max-height:280px; object-fit:cover; border-radius:16px; margin:0 0 24px;">
                @endif

                <div style="padding:20px; border:1px solid #f4d7e5; border-radius:16px; background:#fff8fc;">
                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:#9d174d; font-weight:bold; margin-bottom:8px;">Announcement</div>
                    <p style="margin:0; font-size:15px; line-height:1.75; color:#4b1640;">{{ $announcementSummary ?: 'A new announcement is available on LabCentral.' }}</p>
                </div>

                @if ($announcement->start_date || $announcement->end_date)
                    <p style="margin:20px 0 0; font-size:14px; line-height:1.7; color:#6b7280;">
                        Available {{ $announcement->start_date?->format('M d, Y') ?? 'now' }} through {{ $announcement->end_date?->format('M d, Y') ?? 'ongoing' }}.
                    </p>
                @endif

                <div style="margin:28px 0 8px;">
                    <a href="{{ $actionUrl }}" style="display:inline-block; background:#d91c77; color:#fff; text-decoration:none; padding:13px 22px; border-radius:999px; font-weight:bold;">View announcement</a>
                </div>

                <p style="margin:18px 0 0; font-size:14px; line-height:1.7; color:#6b7280;">Log in to LabCentral to read the complete announcement and any attached images.</p>
            </div>

            @include('emails.partials.privacy-footer')
        </div>
    </div>
</body>
</html>
