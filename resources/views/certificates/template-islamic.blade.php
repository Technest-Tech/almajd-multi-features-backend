<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600&family=Amiri:wght@400;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
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
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background: white;
            padding: 0;
            margin: 0;
            height: auto;
            overflow: visible;
            page-break-after: avoid;
        }
        .certificate-container {
            width: 100%;
            padding: 25px 60px 25px 60px;
            position: relative;
            border: 6px solid #1a5f3f;
            border-radius: 4px;
            background: linear-gradient(135deg, #fef9e7 0%, #faf5e6 100%);
            display: flex;
            flex-direction: column;
            min-height: fit-content;
            height: auto;
        }
        /* Islamic geometric pattern background */
        .ornamental-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            opacity: 0.08;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(26, 95, 63, 0.1) 10px, rgba(26, 95, 63, 0.1) 20px),
                repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(212, 175, 55, 0.1) 10px, rgba(212, 175, 55, 0.1) 20px),
                radial-gradient(circle at 50% 50%, rgba(45, 134, 89, 0.05) 1px, transparent 1px);
            background-size: 40px 40px, 40px 40px, 30px 30px;
        }
        /* Additional Islamic geometric patterns */
        .geometric-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            opacity: 0.06;
            z-index: 1;
        }
        .geometric-pattern::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(0deg, transparent, transparent 15px, rgba(212, 175, 55, 0.08) 15px, rgba(212, 175, 55, 0.08) 16px),
                repeating-linear-gradient(90deg, transparent, transparent 15px, rgba(26, 95, 63, 0.08) 15px, rgba(26, 95, 63, 0.08) 16px);
        }
        /* 8-pointed star decorations */
        .star-8-point {
            position: absolute;
            width: 40px;
            height: 40px;
            z-index: 2;
            opacity: 0.08;
        }
        .star-8-point::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(45deg, transparent 40%, #d4af37 40%, #d4af37 60%, transparent 60%),
                linear-gradient(-45deg, transparent 40%, #d4af37 40%, #d4af37 60%, transparent 60%),
                linear-gradient(0deg, transparent 40%, #2d8659 40%, #2d8659 60%, transparent 60%),
                linear-gradient(90deg, transparent 40%, #2d8659 40%, #2d8659 60%, transparent 60%);
            transform: rotate(22.5deg);
        }
        .star-8-point-top-left {
            top: 80px;
            left: 100px;
        }
        .star-8-point-top-right {
            top: 80px;
            right: 100px;
        }
        .star-8-point-bottom-left {
            bottom: 100px;
            left: 100px;
        }
        .star-8-point-bottom-right {
            bottom: 100px;
            right: 100px;
        }
        /* Hexagonal pattern decorations */
        .hexagon-pattern {
            position: absolute;
            width: 50px;
            height: 50px;
            z-index: 2;
            opacity: 0.06;
        }
        .hexagon-pattern::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: #2d8659;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        }
        .hexagon-pattern::after {
            content: '';
            position: absolute;
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            background: #d4af37;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        }
        .hexagon-top-left {
            top: 120px;
            left: 150px;
        }
        .hexagon-top-right {
            top: 120px;
            right: 150px;
        }
        .hexagon-bottom-left {
            bottom: 120px;
            left: 150px;
        }
        .hexagon-bottom-right {
            bottom: 120px;
            right: 150px;
        }
        /* Crescent moon decorations */
        .crescent-moon {
            position: absolute;
            width: 35px;
            height: 35px;
            z-index: 2;
            opacity: 0.1;
        }
        .crescent-moon::before {
            content: '🌙';
            font-size: 30px;
            color: #d4af37;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .crescent-top-left {
            top: 60px;
            left: 200px;
        }
        .crescent-top-right {
            top: 60px;
            right: 200px;
        }
        .crescent-bottom-left {
            bottom: 70px;
            left: 200px;
        }
        .crescent-bottom-right {
            bottom: 70px;
            right: 200px;
        }
        /* Arabesque border pattern */
        .arabesque-border {
            position: absolute;
            pointer-events: none;
            z-index: 2;
            opacity: 0.08;
        }
        .arabesque-border-top {
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            width: 400px;
            height: 20px;
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 10px,
                #2d8659 10px,
                #2d8659 12px,
                transparent 12px,
                transparent 20px,
                #d4af37 20px,
                #d4af37 22px
            );
        }
        .arabesque-border-bottom {
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            width: 400px;
            height: 20px;
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 10px,
                #2d8659 10px,
                #2d8659 12px,
                transparent 12px,
                transparent 20px,
                #d4af37 20px,
                #d4af37 22px
            );
        }
        /* Side decorative elements */
        .side-decoration {
            position: absolute;
            width: 30px;
            height: 200px;
            z-index: 2;
            opacity: 0.06;
        }
        .side-decoration::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                #2d8659 0px,
                #2d8659 2px,
                transparent 2px,
                transparent 8px,
                #d4af37 8px,
                #d4af37 10px,
                transparent 10px,
                transparent 16px
            );
        }
        .side-decoration-left {
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
        }
        .side-decoration-right {
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
        }
        /* Additional small star decorations */
        .small-star {
            position: absolute;
            width: 20px;
            height: 20px;
            z-index: 2;
            opacity: 0.1;
        }
        .small-star::before {
            content: '✦';
            font-size: 18px;
            color: #d4af37;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .small-star-1 { top: 150px; left: 250px; }
        .small-star-2 { top: 150px; right: 250px; }
        .small-star-3 { bottom: 150px; left: 250px; }
        .small-star-4 { bottom: 150px; right: 250px; }
        .small-star-5 { top: 200px; left: 180px; }
        .small-star-6 { top: 200px; right: 180px; }
        .small-star-7 { bottom: 200px; left: 180px; }
        .small-star-8 { bottom: 200px; right: 180px; }
        /* Ornate outer border with Islamic design */
        .ornate-border {
            position: absolute;
            top: 3px;
            left: 3px;
            right: 3px;
            bottom: 3px;
            border: 2px solid #2d8659;
            pointer-events: none;
            z-index: 1;
        }
        .ornate-border::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border: 1px solid rgba(212, 175, 55, 0.6);
        }
        /* Islamic geometric corner designs */
        .corner-flourish {
            position: absolute;
            width: 120px;
            height: 120px;
            z-index: 2;
        }
        .corner-flourish::before,
        .corner-flourish::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            border: 3px solid #2d8659;
        }
        .corner-flourish-top-left {
            top: 15px;
            left: 15px;
        }
        .corner-flourish-top-left::before {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
            border-radius: 0;
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }
        .corner-flourish-top-left::after {
            bottom: 0;
            right: 0;
            border-left: none;
            border-top: none;
            border-radius: 0;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }
        .corner-flourish-top-right {
            top: 15px;
            right: 15px;
        }
        .corner-flourish-top-right::before {
            top: 0;
            right: 0;
            border-left: none;
            border-bottom: none;
            border-radius: 0;
            clip-path: polygon(0 0, 100% 0, 100% 100%);
        }
        .corner-flourish-top-right::after {
            bottom: 0;
            left: 0;
            border-right: none;
            border-top: none;
            border-radius: 0;
            clip-path: polygon(0 0, 0 100%, 100% 100%);
        }
        .corner-flourish-bottom-left {
            bottom: 15px;
            left: 15px;
        }
        .corner-flourish-bottom-left::before {
            bottom: 0;
            left: 0;
            border-right: none;
            border-top: none;
            border-radius: 0;
            clip-path: polygon(0 0, 0 100%, 100% 100%);
        }
        .corner-flourish-bottom-left::after {
            top: 0;
            right: 0;
            border-left: none;
            border-bottom: none;
            border-radius: 0;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }
        .corner-flourish-bottom-right {
            bottom: 15px;
            right: 15px;
        }
        .corner-flourish-bottom-right::before {
            bottom: 0;
            right: 0;
            border-left: none;
            border-top: none;
            border-radius: 0;
            clip-path: polygon(0 0, 100% 0, 100% 100%);
        }
        .corner-flourish-bottom-right::after {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
            border-radius: 0;
            clip-path: polygon(0 0, 0 100%, 100% 0);
        }
        /* Islamic star pattern decoration */
        .islamic-star {
            position: absolute;
            width: 80px;
            height: 80px;
            z-index: 2;
        }
        .islamic-star::before {
            content: '✪';
            font-size: 60px;
            color: #d4af37;
            opacity: 0.15;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
        }
        .islamic-star-left {
            left: 50px;
            top: 50%;
            transform: translateY(-50%);
        }
        .islamic-star-right {
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
        }
        /* Decorative divider lines with Islamic motifs */
        .divider-line {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 3px;
            z-index: 2;
        }
        .divider-line::before,
        .divider-line::after {
            content: '✪';
            position: absolute;
            color: #2d8659;
            font-size: 18px;
            top: 50%;
            transform: translateY(-50%);
        }
        .divider-line::before {
            left: -30px;
        }
        .divider-line::after {
            right: -30px;
        }
        .divider-line-top {
            top: 70px;
            background: linear-gradient(90deg, transparent, #2d8659 20%, #d4af37 50%, #2d8659 80%, transparent);
        }
        .divider-line-bottom {
            bottom: 80px;
            background: linear-gradient(90deg, transparent, #2d8659 20%, #d4af37 50%, #2d8659 80%, transparent);
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
            color: #1a5f3f;
            letter-spacing: 12px;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            position: relative;
            display: inline-block;
            padding: 0 40px;
        }
        .certificate-title::before,
        .certificate-title::after {
            content: '✪';
            color: #d4af37;
            font-size: 28px;
            margin: 0 20px;
            vertical-align: middle;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.6);
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
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 300;
            position: relative;
            padding: 0 60px;
        }
        .certificate-text::before,
        .certificate-text::after {
            content: '✪';
            color: #2d8659;
            font-size: 14px;
            margin: 0 10px;
            opacity: 0.7;
        }
        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #1a5f3f;
            margin: 30px 0;
            text-decoration: underline;
            text-decoration-color: #d4af37;
            text-decoration-thickness: 4px;
            text-underline-offset: 10px;
            letter-spacing: 3px;
            position: relative;
            padding: 10px 0;
        }
        .student-name::before,
        .student-name::after {
            content: '✪';
            color: #2d8659;
            font-size: 22px;
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
            background: linear-gradient(135deg, rgba(254, 249, 231, 0.95) 0%, rgba(250, 245, 230, 0.95) 100%);
            border-radius: 8px;
            border: 2px solid #2d8659;
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
            border: 1px solid #d4af37;
            border-radius: 8px;
            opacity: 0.6;
        }
        .detail-label {
            font-size: 12px;
            color: #1a5f3f;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            position: relative;
        }
        .detail-label::before,
        .detail-label::after {
            content: '✪';
            color: #2d8659;
            font-size: 8px;
            margin: 0 5px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #1a5f3f;
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
            border-top: 2px solid rgba(45, 134, 89, 0.3);
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
            color: #1a5f3f;
            font-weight: 600;
            margin-bottom: 5px;
            font-family: 'Playfair Display', serif;
        }
        .certificate-date-label {
            font-size: 11px;
            color: #2d8659;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .website-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #1a5f3f;
        }
        .website-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 2px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        .website-url {
            font-size: 14px;
            color: #2d8659;
            font-weight: 500;
        }
        /* Responsive styles for mobile/webview */
        @media screen and (max-width: 768px) {
            .certificate-container {
                padding: 15px 20px 20px 20px;
                border-width: 4px;
            }
            .certificate-logo {
                max-width: 60px;
                max-height: 60px;
            }
            .certificate-title {
                font-size: 24px;
                letter-spacing: 4px;
                padding: 0 20px;
                word-break: break-word;
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
                font-size: 24px;
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
            .website-logo {
                max-width: 80px;
                max-height: 40px;
            }
            .website-url {
                font-size: 12px;
            }
            /* Hide or scale decorative elements on mobile */
            .star-8-point,
            .hexagon-pattern,
            .crescent-moon,
            .small-star {
                opacity: 0.2;
                transform: scale(0.7);
            }
            .islamic-star {
                width: 50px;
                height: 50px;
            }
            .islamic-star::before {
                font-size: 40px;
            }
            .side-decoration {
                width: 15px;
                height: 150px;
            }
            .arabesque-border-top,
            .arabesque-border-bottom {
                width: 250px;
                height: 15px;
            }
        }
        @media screen and (max-width: 480px) {
            .certificate-container {
                padding: 10px 15px 15px 15px;
                border-width: 3px;
            }
            .certificate-title {
                font-size: 18px;
                letter-spacing: 2px;
                padding: 0 10px;
                word-break: break-word;
            }
            .certificate-title::before,
            .certificate-title::after {
                font-size: 14px;
                margin: 0 8px;
            }
            .student-name {
                font-size: 20px;
            }
            .certificate-text {
                font-size: 12px;
                padding: 0 10px;
            }
            .detail-value {
                font-size: 14px;
            }
            .detail-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Decorative elements -->
        <div class="ornamental-pattern"></div>
        <div class="geometric-pattern"></div>
        <div class="ornate-border"></div>
        <!-- 8-pointed stars (minimal) -->
        <div class="star-8-point star-8-point-top-left"></div>
        <!-- Hexagonal patterns (minimal) -->
        <div class="hexagon-pattern hexagon-top-left"></div>
        <!-- Crescent moons (minimal) -->
        <div class="crescent-moon crescent-top-left"></div>
        <!-- Small star decorations (minimal) -->
        <div class="small-star small-star-1"></div>

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

