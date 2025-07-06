<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Better Lives United</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 40px;
            height: 40px;
            background-color: #2563eb;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            letter-spacing: 4px;
            margin: 20px 0;
        }
        .content {
            text-align: center;
            color: #374151;
            line-height: 1.6;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">b</div>
            <h1 style="color: #2563eb; margin: 0;">Password Reset Code</h1>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>You requested to reset your password for your Better Lives United account.</p>
            <p>Enter this code in the app to reset your password:</p>
            
            <div class="otp-code">{{ $otp }}</div>
            
            <p><strong>This code will expire in 15 minutes.</strong></p>
            <p>If you didn't request this password reset, please ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>Better Lives United - Real Impact. Real People.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>