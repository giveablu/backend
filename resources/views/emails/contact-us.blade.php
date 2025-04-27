<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Contact Us</title>
    <meta name="description" content="Contact Us Email Notification">
    <style type="text/css">
        a:hover {
            text-decoration: underline !important;
        }

        .header {
            background-color: #0044cc;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .footer {
            font-size: 12px;
            color: rgba(69, 80, 86, 0.741);
            text-align: center;
            padding: 20px;
            background-color: #f2f3f8;
        }

        .content {
            font-size: 16px;
            color: #455056;
            line-height: 24px;
            text-align: center;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body style="margin: 0; background-color: #f2f3f8;" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
    <!--100% body table-->
    <table style="font-family: 'Open Sans', sans-serif; width: 100%;" cellspacing="0" border="0" cellpadding="0" bgcolor="#f2f3f8">
        <tr>
            <td>
                <table class="container" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header">
                            <h1>Thank You for Contacting Us!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            <p>Dear {{ $name }},</p>
                            <p>We’ve received your message and our team is currently reviewing it. We will get back to you as soon as possible.</p>
                            <p>In the meantime, feel free to browse our website or reach out to us with any other questions.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>&copy; <strong>BlueCharities</strong>. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>