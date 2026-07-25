<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $letterhead->subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .email-header {
            background-color: #17a2b8;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header h2 {
            margin: 0;
            font-size: 20px;
        }
        .email-body {
            padding: 25px;
            line-height: 1.6;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #17a2b8;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>{{ $letterhead->company->name ?? 'Company Letter' }}</h2>
        </div>
        <div class="email-body">
            <p>Dear {{ $letterhead->recipient_name }},</p>
            <p>Please find attached the official letter <strong>#{{ $letterhead->letter_no }}</strong> regarding <strong>"{{ $letterhead->subject }}"</strong>.</p>
            
            <p><strong>Letter Summary:</strong></p>
            <div style="background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 12px; margin: 15px 0;">
                {!! nl2br(e(Str::limit(strip_tags($letterhead->content), 300))) !!}
            </div>

            <p>The complete official document is attached to this email as a PDF file.</p>
            <p>Regards,<br><strong>{{ $letterhead->company->name ?? 'Management' }}</strong></p>
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} {{ $letterhead->company->name ?? 'Company' }}. All rights reserved.</p>
            @if(!empty($letterhead->company->address))
                <p>{{ $letterhead->company->address }}</p>
            @endif
        </div>
    </div>
</body>
</html>
