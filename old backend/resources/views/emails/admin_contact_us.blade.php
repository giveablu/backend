<!doctype html>
<html lang="en-US">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>New Contact Form Submission</title>
</head>
<body>
    <table width="100%" bgcolor="#f2f3f8">
        <tr>
            <td>
                <table style="max-width:670px; margin:0 auto;" width="100%" bgcolor="#ffffff">
                    <tr>
                        <td style="padding:20px;">
                            <p>You have received a new contact form submission:</p>
                            <p><strong>Name:</strong> {{ $details['name'] }}</p>
                            <p><strong>Email:</strong> {{ $details['email'] }}</p>
                            <p><strong>Phone:</strong> {{ $details['phone'] }}</p>
                            <p><strong>Message:</strong> {{ $details['message'] }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
