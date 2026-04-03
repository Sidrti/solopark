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
        .details { background: #f9fafb; padding: 20px; border-radius: 12px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 8px;}
        .footer { text-align: center; padding-top: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #ffffff; margin: 0;">Reservation Confirmed</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $booking->user->name }}</strong>,</p>
            <p>Your parking reservation has been successfully confirmed. Please see the details below for your visit.</p>

            <div class="details">
                <h3 style="margin-top: 0; color: #1866ed;">{{ $booking->spot->title }}</h3>
                <p style="color: #6b7280; font-size: 14px;">{{ $booking->spot->address }}, {{ $booking->spot->city }}</p>

                <div class="detail-row">
                    <span><strong>Starts:</strong></span>
                    <span>{{ \Carbon\Carbon::parse($booking->start_time)->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span><strong>Ends:</strong></span>
                    <span>{{ \Carbon\Carbon::parse($booking->end_time)->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span><strong>Vehicle:</strong></span>
                    <span>{{ $booking->vehicle->license_plate }} ({{ $booking->vehicle->make_model }})</span>
                </div>
                <div class="detail-row">
                    <span><strong>Base Cost:</strong></span>
                    <span>CA${{ number_format($booking->subtotal, 0) }}</span>
                </div>
                <div class="detail-row">
                    <span><strong>Service Fee:</strong></span>
                    <span>CA${{ number_format($booking->service_fee, 0) }}</span>
                </div>
                <div class="detail-row">
                    <span><strong>Tax (13%):</strong></span>
                    <span>CA${{ number_format($booking->tax, 0) }}</span>
                </div>
                <div class="detail-row">
                    <span><strong>Gateway (3%):</strong></span>
                    <span>CA${{ number_format($booking->gateway_fee, 0) }}</span>
                </div>
                <div class="detail-row" style="border-bottom: 0;">
                    <span><strong>Total Paid:</strong></span>
                    <span>CA${{ number_format($booking->total_price, 0) }}</span>
                </div>
            </div>

            <p>If you have any questions or need access instructions, please contact the host:</p>
            <p><strong>Host:</strong> {{ $booking->spot->user->name }}<br><strong>Email:</strong> {{ $booking->spot->user->email }}</p>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('bookings.show', $booking->id) }}" class="button">View Booking Details</a>
            </div>
        </div>
        <div class="footer">
            <p>© 2025 Solopark Canada. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
