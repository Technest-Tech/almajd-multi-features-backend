<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Timetable</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', 'Cairo', 'Tajawal', sans-serif;
        }

        .embedded-dashboard-wrapper {
            width: 100%;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border: none;
            overflow: hidden;
        }

        .embedded-dashboard-wrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Embedded Dashboard -->
    <div class="embedded-dashboard-wrapper">
        <iframe 
            id="dashboardIframe"
            src="https://reminders.maccaacademy.com/" 
            frameborder="0" 
            allowfullscreen
            allow="fullscreen"
            scrolling="yes">
        </iframe>
    </div>
</body>
</html>
















