<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', sans-serif; color: #1f2937; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1866ed; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
        .content { padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 12px 12px; }
        .footer { text-align: center; padding-top: 20px; color: #6b7280; font-size: 12px; }
        .info-row { margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; }
        .label { font-weight: bold; color: #4b5563; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .value { color: #111827; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #ffffff; margin: 0;">New Contact Inquiry</h1>
        </div>
        <div class="content">
            <div class="info-row">
                <span class="label">From</span>
                <span class="value"><strong>{{ $contactData['name'] }}</strong></span>
            </div>
            <div class="info-row">
                <span class="label">Email Address</span>
                <span class="value">{{ $contactData['email'] }}</span>
            </div>
            <div class="info-row">
                <span class="label">Subject</span>
                <span class="value">{{ $contactData['subject'] }}</span>
            </div>
            <div class="info-row" style="border-bottom: none;">
                <span class="label">Message</span>
                <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">{{ $contactData['message'] }}</div>
            </div>
        </div>
        <div class="footer">
            <p>© 2025 Solopark Canada. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
