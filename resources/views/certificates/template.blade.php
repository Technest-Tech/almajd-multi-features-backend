<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: A4;
            margin: 0;
        }
        html {
            height: auto;
            overflow: visible;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background: white;
            padding: 0;
            margin: 0;
            height: auto;
            min-height: auto;
            max-height: none;
            overflow: visible;
            page-break-after: avoid;
            display: block;
        }
        .certificate-container {
            width: 100%;
            padding: 25px 60px 25px 60px;
            position: relative;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            display: flex;
            flex-direction: column;
            min-height: fit-content;
            height: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1) inset;
        }
        /* Modern layered border effect */
        .certificate-container::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            z-index: -1;
            opacity: 0.8;
        }
        .certificate-container::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            z-index: -1;
        }
        /* Islamic decorative vectors */
        .islamic-decoration {
            position: absolute;
            z-index: 1;
            pointer-events: none;
            opacity: 0.6;
        }
        /* Top corners - Islamic geometric patterns */
        .islamic-corner-top-left {
            top: 20px;
            left: 20px;
            width: 100px;
            height: 100px;
        }
        .islamic-corner-top-left::before {
            content: '◈';
            position: absolute;
            font-size: 60px;
            color: #667eea;
            top: 0;
            left: 0;
            transform: rotate(-45deg);
        }
        .islamic-corner-top-left::after {
            content: '✦';
            position: absolute;
            font-size: 40px;
            color: #f093fb;
            top: 20px;
            left: 20px;
        }
        .islamic-corner-top-right {
            top: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
        }
        .islamic-corner-top-right::before {
            content: '◈';
            position: absolute;
            font-size: 60px;
            color: #667eea;
            top: 0;
            right: 0;
            transform: rotate(45deg);
        }
        .islamic-corner-top-right::after {
            content: '✦';
            position: absolute;
            font-size: 40px;
            color: #f093fb;
            top: 20px;
            right: 20px;
        }
        /* Bottom corners - Islamic geometric patterns */
        .islamic-corner-bottom-left {
            bottom: 20px;
            left: 20px;
            width: 100px;
            height: 100px;
        }
        .islamic-corner-bottom-left::before {
            content: '◈';
            position: absolute;
            font-size: 60px;
            color: #667eea;
            bottom: 0;
            left: 0;
            transform: rotate(45deg);
        }
        .islamic-corner-bottom-left::after {
            content: '✦';
            position: absolute;
            font-size: 40px;
            color: #f093fb;
            bottom: 20px;
            left: 20px;
        }
        .islamic-corner-bottom-right {
            bottom: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
        }
        .islamic-corner-bottom-right::before {
            content: '◈';
            position: absolute;
            font-size: 60px;
            color: #667eea;
            bottom: 0;
            right: 0;
            transform: rotate(-45deg);
        }
        .islamic-corner-bottom-right::after {
            content: '✦';
            position: absolute;
            font-size: 40px;
            color: #f093fb;
            bottom: 20px;
            right: 20px;
        }
        /* Side decorative patterns */
        .islamic-side-left {
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 200px;
        }
        .islamic-side-left::before,
        .islamic-side-left::after {
            content: '◊';
            position: absolute;
            font-size: 35px;
            color: #667eea;
            opacity: 0.4;
        }
        .islamic-side-left::before {
            top: 0;
            left: 0;
        }
        .islamic-side-left::after {
            bottom: 0;
            left: 0;
        }
        .islamic-side-right {
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 200px;
        }
        .islamic-side-right::before,
        .islamic-side-right::after {
            content: '◊';
            position: absolute;
            font-size: 35px;
            color: #667eea;
            opacity: 0.4;
        }
        .islamic-side-right::before {
            top: 0;
            right: 0;
        }
        .islamic-side-right::after {
            bottom: 0;
            right: 0;
        }
        /* Top and bottom decorative lines */
        .islamic-line-top {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            width: 400px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea 20%, #f093fb 50%, #667eea 80%, transparent);
        }
        .islamic-line-top::before,
        .islamic-line-top::after {
            content: '✱';
            position: absolute;
            font-size: 20px;
            color: #667eea;
            top: 50%;
            transform: translateY(-50%);
        }
        .islamic-line-top::before {
            left: -30px;
        }
        .islamic-line-top::after {
            right: -30px;
        }
        .islamic-line-bottom {
            position: absolute;
            bottom: 120px;
            left: 50%;
            transform: translateX(-50%);
            width: 400px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea 20%, #f093fb 50%, #667eea 80%, transparent);
        }
        .islamic-line-bottom::before,
        .islamic-line-bottom::after {
            content: '✱';
            position: absolute;
            font-size: 20px;
            color: #667eea;
            top: 50%;
            transform: translateY(-50%);
        }
        .islamic-line-bottom::before {
            left: -30px;
        }
        .islamic-line-bottom::after {
            right: -30px;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 15px;
            margin-top: 0;
            padding-top: 0;
            position: relative;
            z-index: 3;
        }
        .certificate-logo {
            max-width: 90px;
            max-height: 90px;
            margin: 0 auto 8px;
            display: block;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            position: relative;
            z-index: 3;
        }
        .certificate-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 900;
            color: #2c3e50;
            letter-spacing: 12px;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            position: relative;
            display: inline-block;
            padding: 0 40px;
        }
        .certificate-title::before,
        .certificate-title::after {
            content: '✦';
            color: #e74c3c;
            font-size: 28px;
            margin: 0 20px;
            vertical-align: middle;
            text-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }
        .certificate-content {
            text-align: center;
            margin: 20px 0 10px 0;
            position: relative;
            z-index: 3;
            flex: 1;
        }
        .certificate-text {
            font-size: 18px;
            line-height: 1.8;
            color: #34495e;
            margin-bottom: 20px;
            font-weight: 300;
            position: relative;
            padding: 0 60px;
        }
        .certificate-text::before,
        .certificate-text::after {
            content: '❋';
            color: #3498db;
            font-size: 16px;
            margin: 0 10px;
            opacity: 0.6;
        }
        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin: 30px 0;
            text-decoration: underline;
            text-decoration-color: #e74c3c;
            text-decoration-thickness: 4px;
            text-underline-offset: 10px;
            letter-spacing: 3px;
            position: relative;
            padding: 10px 0;
        }
        .student-name::before,
        .student-name::after {
            content: '◊';
            color: #3498db;
            font-size: 24px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .student-name::before {
            left: -40px;
        }
        .student-name::after {
            right: -40px;
        }
        .certificate-details {
            margin-top: 15px;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            position: relative;
            z-index: 3;
        }
        .detail-item {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 12px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.9) 100%);
            border-radius: 12px;
            border: 2px solid #3498db;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: relative;
        }
        .detail-item::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 1px solid #e74c3c;
            border-radius: 12px;
            opacity: 0.5;
        }
        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            position: relative;
        }
        .detail-label::before,
        .detail-label::after {
            content: '▸';
            color: #3498db;
            font-size: 10px;
            margin: 0 5px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            font-family: 'Playfair Display', serif;
        }
        .certificate-footer {
            margin-top: 20px;
            margin-bottom: 15px;
            padding: 15px 80px 15px 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            border-top: 2px solid rgba(52, 152, 219, 0.2);
            position: relative;
            z-index: 3;
            flex-shrink: 0;
        }
        .certificate-date-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .certificate-footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .certificate-date {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 5px;
            font-family: 'Playfair Display', serif;
        }
        .certificate-date-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .website-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #2c3e50;
        }
        .website-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 2px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        .website-url {
            font-size: 14px;
            color: #3498db;
            font-weight: 500;
        }
        /* Responsive styles */
        @media screen and (max-width: 768px) {
            .certificate-container {
                padding: 15px 20px 20px 20px;
            }
            .certificate-logo {
                max-width: 60px;
                max-height: 60px;
            }
            .certificate-title {
                font-size: 24px;
                letter-spacing: 4px;
                padding: 0 20px;
            }
            .certificate-title::before,
            .certificate-title::after {
                font-size: 18px;
                margin: 0 10px;
            }
            .certificate-text {
                font-size: 14px;
                padding: 0 20px;
            }
            .student-name {
                font-size: 28px;
                letter-spacing: 2px;
            }
            .certificate-details {
                flex-direction: column;
                gap: 10px;
            }
            .detail-item {
                min-width: 100%;
            }
            .certificate-footer {
                padding: 10px 20px 10px 20px;
            }
        }
        @media screen and (max-width: 480px) {
            .certificate-container {
                padding: 10px 15px 15px 15px;
            }
            .certificate-title {
                font-size: 18px;
                letter-spacing: 2px;
                padding: 0 10px;
            }
            .certificate-title::before,
            .certificate-title::after {
                font-size: 14px;
                margin: 0 8px;
            }
            .student-name {
                font-size: 24px;
            }
            .certificate-text {
                font-size: 12px;
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Islamic decorative elements -->
        <div class="islamic-decoration islamic-corner-top-left"></div>
        <div class="islamic-decoration islamic-corner-top-right"></div>
        <div class="islamic-decoration islamic-corner-bottom-left"></div>
        <div class="islamic-decoration islamic-corner-bottom-right"></div>
        <div class="islamic-decoration islamic-side-left"></div>
        <div class="islamic-decoration islamic-side-right"></div>
        <div class="islamic-decoration islamic-line-bottom"></div>

        <div class="certificate-header">
            <img src="{{ public_path('logo.png') }}" 
                 alt="Logo" 
                 class="certificate-logo">
            <div class="certificate-title">{{ request('certification_title', 'CERTIFICATION') }}</div>
        </div>

        <div class="certificate-content">
            <p class="certificate-text">
                {{ request('certify_text', 'This is to certify that') }}
            </p>
            <div class="student-name">
                {{ $certificate->student_name }}
            </div>
            <p class="certificate-text">
                {{ request('completed_text', 'has successfully completed the course') }}
            </p>
            <div class="certificate-details" style="margin-top: 15px; margin-bottom: 0;">
                <div class="detail-item">
                    <div class="detail-label">{{ request('manager_label', 'Manager') }}</div>
                    <div class="detail-value">{{ $certificate->manager_name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">{{ request('teacher_label', 'Teacher') }}</div>
                    <div class="detail-value">{{ $certificate->teacher_name }}</div>
                </div>
            </div>
        </div>

        <div class="certificate-footer">
            <div class="certificate-footer-content">
                <div class="certificate-date-section">
                    <div class="certificate-date-label">{{ request('date_label', 'Date') }}</div>
                    <div class="certificate-date">{{ $certificate->issue_date->format('F d, Y') }}</div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <img src="{{ public_path('ketm5.png') }}" alt="Almajd Academy" class="website-logo">
                    <span class="website-url">www.almajdacademy.org</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
