<!-- resources/views/emails/late_arrival.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Late Arrival Notification</title>
</head>
<body>
    <h1>Hello, {{ $employee->full_name }}</h1>
    <p>You were marked as late on {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}.</p>
    <p>You were {{ $lateMinutes }} minutes late.</p>
    <p>Please ensure to adhere to the scheduled work timings.</p>
</body>
</html>
