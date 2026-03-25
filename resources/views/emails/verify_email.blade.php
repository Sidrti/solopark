<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', sans-serif; color: #1f2937; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1866ed; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
        .content { padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 12px 12px; }
        .button { background: #1866ed; padding: 12px 24px; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-block; color: #ffffff !important;}
        .footer { text-align: center; padding-top: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #ffffff; margin: 0;">Verify your Email</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>Welcome to Solopark! Please verify your email address to start booking your parking spots today.</p>

            <div style="text-align: center; margin: 40px 0;">
                <a href="{{ $url }}" class="button">Verify Email Address</a>
            </div>

            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p style="font-size: 12px; word-break: break-all; color: #6b7280;">{{ $url }}</p>

            <p>This verification link will expire in 60 minutes.</p>
        </div>
        <div class="footer">
            <p>© 2025 Solopark Canada. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
