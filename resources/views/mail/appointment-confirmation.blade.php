<!DOCTYPE html>
<html>
<head>
    <title>Appointment Confirmation</title>
</head>
<body>
    <h2>Hello {{ $appointment->user->name }}!</h2>
    <p>Your appointment for the service <strong>{{ $appointment->service->name }}</strong> has been confirmed.</p>
    <p><strong>Appointment Time:</strong> {{ $appointment->start_time }} to {{ $appointment->end_time }}</p>
    <p>Thank you for choosing our service. We look forward to seeing you!</p>
</body>
</html>
