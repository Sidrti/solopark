<h1>New parking request</h1>
<p>This is a request to find a host. No booking or payment has been made.</p>
<dl>
    <dt>Preferred location or postal code</dt><dd>{{ $details['location'] }}</dd>
    <dt>Parking type</dt><dd>{{ ucfirst($details['parking_type']) }}</dd>
    <dt>Required start</dt><dd>{{ $details['start_date'] }} {{ $details['start_time'] }} ({{ $details['timezone'] }})</dd>
    <dt>Approximate budget (CAD)</dt><dd>{{ isset($details['budget']) ? '$'.number_format($details['budget'], 2) : 'Not provided' }}</dd>
    <dt>Name</dt><dd>{{ $details['name'] }}</dd>
    <dt>Email</dt><dd>{{ $details['email'] }}</dd>
    <dt>Phone</dt><dd>{{ $details['phone'] }}</dd>
</dl>
