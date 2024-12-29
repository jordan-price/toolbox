<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
</head>
<body style="
    margin: 0;
    padding: 20px;
    background-color: #f3f4f6;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
">
    <div style="
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        max-width: 600px;
        margin: 40px auto;
        padding: 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        color: #1f2937;
        line-height: 1.8;
    ">
        <div style="
            font-size: 16px;
            color: #374151;
            margin-bottom: 30px;
        ">
            {!! nl2br(e($messageBody)) !!}
        </div>
        
        <div style="
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        ">
            <span style="
                display: inline-block;
                padding: 8px 16px;
                background-color: #f9fafb;
                border-radius: 9999px;
            ">
                Sent via Bestie Toolbox
            </span>
        </div>
    </div>
</body>
</html>
