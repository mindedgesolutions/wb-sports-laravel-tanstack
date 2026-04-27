<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
</head>

<body>
    <p>Hi Admin,</p>
    <p>Below is the feedback we have received dated: {{ date('d/m/Y H:i:s') }}</p>
    <p><strong>Feedback Type:</strong> {{ $feedbackType }}</p>
    <p><strong>Name:</strong> {{ $fromName }}</p>
    <p><strong>Mobile:</strong> {{ $fromMobile }}</p>
    <p><strong>Email:</strong> {{ $fromEmail }}</p>
    <p><strong>Address:</strong> {{ $fromAddress ?? 'NA' }}</p>
    <p><strong>Subject:</strong> {{ $subject }}</p>
    <p><strong>Message:</strong> {{ $userMessage }}</p>
    <p>Thank you!</p>
    <p>Best regards,</p>
    <p>NIC Dev Team</p>
</body>

</html>
