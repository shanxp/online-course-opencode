<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .container { max-width: 520px; margin: 0 auto; padding: 32px 16px; }
        .card { background-color: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
        .header { background-color: #982315; padding: 24px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
        .body { padding: 28px 24px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .creds { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .creds .row { display: flex; justify-content: space-between; padding: 6px 0; }
        .creds .label { color: #6b7280; font-size: 14px; padding-right: 10px;}
        .creds .value { color: #111827; font-weight: 600; font-size: 14px; }
        .footer { padding: 16px 24px 24px; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>{{ $appName }}</h1>
            </div>
            <div class="body">
                <p>{{ __('messages.mail_password_reset_greeting', ['name' => $name]) }}</p>
                <p>{{ __('messages.mail_password_reset_intro') }}</p>

                <div class="creds">
                    <div class="row">
                        <span class="label">{{ __('messages.mail_username') }}</span>
                        <span class="value">{{ $username }}</span>
                    </div>
                    <div class="row">
                        <span class="label">{{ __('messages.mail_password') }}</span>
                        <span class="value">{{ $password }}</span>
                    </div>
                </div>

                <p>{{ __('messages.mail_password_reset_outro') }}</p>
            </div>
            <div class="footer">
                <p>{!! __('messages.mail_account_signature') !!}</p>
            </div>
        </div>
    </div>
</body>
</html>
