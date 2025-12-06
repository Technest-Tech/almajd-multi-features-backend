<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Cormorant+Garamond:wght@400;600;700&family=Lato:wght@300;400;600&display=swap" rel="stylesheet">
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
            font-family: 'Lato', sans-serif;
            background: #f5f5f5;
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
            padding: 25px 60px 40px 60px;
            position: relative;
            background: linear-gradient(135deg, #fefefe 0%, #faf8f3 50%, #fefefe 100%);
            display: flex;
            flex-direction: column;
            min-height: fit-content;
            height: auto;
            overflow: hidden;
        }
        /* Ornate border frame */
        .border-frame {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 6px solid #d4af37;
            border-radius: 4px;
            z-index: 1;
            pointer-events: none;
        }
        .border-frame::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 2px solid #8b7355;
            border-radius: 2px;
        }
        .border-frame::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        /* Corner decorative elements */
        .corner-decoration {
            position: absolute;
            width: 50px;
            height: 50px;
            z-index: 2;
            pointer-events: none;
        }
        .corner-top-left {
            top: 20px;
            left: 20px;
            border-top: 3px solid #8b7355;
            border-left: 3px solid #8b7355;
            border-top-left-radius: 6px;
        }
        .corner-top-left::before {
            content: '✦';
            position: absolute;
            top: -10px;
            left: -10px;
            font-size: 18px;
            color: #d4af37;
        }
        .corner-top-right {
            top: 20px;
            right: 20px;
            border-top: 3px solid #8b7355;
            border-right: 3px solid #8b7355;
            border-top-right-radius: 6px;
        }
        .corner-top-right::before {
            content: '✦';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 18px;
            color: #d4af37;
        }
        .corner-bottom-left {
            bottom: 20px;
            left: 20px;
            border-bottom: 3px solid #8b7355;
            border-left: 3px solid #8b7355;
            border-bottom-left-radius: 6px;
        }
        .corner-bottom-left::before {
            content: '✦';
            position: absolute;
            bottom: -10px;
            left: -10px;
            font-size: 18px;
            color: #d4af37;
        }
        .corner-bottom-right {
            bottom: 20px;
            right: 20px;
            border-bottom: 3px solid #8b7355;
            border-right: 3px solid #8b7355;
            border-bottom-right-radius: 6px;
        }
        .corner-bottom-right::before {
            content: '✦';
            position: absolute;
            bottom: -10px;
            right: -10px;
            font-size: 18px;
            color: #d4af37;
        }
        /* Subtle background pattern */
        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.03;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 20px, #8b7355 20px, #8b7355 21px),
                repeating-linear-gradient(-45deg, transparent, transparent 20px, #d4af37 20px, #d4af37 21px);
            z-index: 1;
            pointer-events: none;
        }
        /* Main content wrapper */
        .content-wrapper {
            position: relative;
            z-index: 3;
            flex: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        /* Header section */
        .certificate-header {
            margin-bottom: 25px;
            position: relative;
        }
        .certificate-logo {
            max-width: 90px;
            max-height: 90px;
            margin: 0 auto 15px;
            display: block;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
        }
        .certificate-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 6px;
            margin-bottom: 10px;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
        }
        .certificate-title::before,
        .certificate-title::after {
            content: '❋';
            color: #d4af37;
            font-size: 24px;
            margin: 0 15px;
            vertical-align: middle;
        }
        .title-underline {
            width: 180px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d4af37 20%, #8b7355 50%, #d4af37 80%, transparent);
            margin: 10px auto;
            border-radius: 2px;
        }
        /* Main content area */
        .certificate-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            position: relative;
            padding: 0 30px;
        }
        .certificate-text {
            font-size: 16px;
            line-height: 1.8;
            color: #34495e;
            margin-bottom: 15px;
            font-weight: 400;
            font-style: italic;
        }
        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 700;
            color: #2c3e50;
            margin: 20px 0;
            line-height: 1.2;
            position: relative;
            display: inline-block;
            padding: 10px 30px;
        }
        .student-name::before,
        .student-name::after {
            content: '—';
            color: #d4af37;
            font-size: 28px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 300;
        }
        .student-name::before {
            left: -15px;
        }
        .student-name::after {
            right: -15px;
        }
        .completed-text {
            font-size: 16px;
            color: #34495e;
            margin-top: 20px;
            font-weight: 400;
            font-style: italic;
        }
        /* Decorative divider */
        .divider {
            width: 250px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d4af37 20%, #8b7355 50%, #d4af37 80%, transparent);
            margin: 25px auto;
            position: relative;
        }
        .divider::before,
        .divider::after {
            content: '✧';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #8b7355;
            font-size: 16px;
            background: linear-gradient(135deg, #fefefe 0%, #faf8f3 100%);
            padding: 0 8px;
        }
        .divider::before {
            left: -25px;
        }
        .divider::after {
            right: -25px;
        }
        /* Details section */
        .certificate-details {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .detail-item {
            text-align: center;
            min-width: 180px;
        }
        .detail-label {
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            font-family: 'Lato', sans-serif;
        }
        .detail-label::before,
        .detail-label::after {
            content: '•';
            color: #d4af37;
            margin: 0 6px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            font-family: 'Cormorant Garamond', serif;
            padding: 6px 0;
            border-bottom: 2px solid #d4af37;
            display: inline-block;
            min-width: 130px;
        }
        /* Footer section */
        .certificate-footer {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
            border-top: 2px solid rgba(212, 175, 55, 0.3);
            position: relative;
        }
        .certificate-date-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .certificate-date-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .certificate-date {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 600;
            font-family: 'Cormorant Garamond', serif;
        }
        .website-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }
        .website-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 8px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        .website-url {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 500;
            letter-spacing: 1px;
        }
        .certificate-number {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            color: #95a5a6;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        /* Responsive styles */
        @media screen and (max-width: 768px) {
            .certificate-container {
                padding: 30px 20px;
            }
            .border-frame {
                top: 15px;
                left: 15px;
                right: 15px;
                bottom: 15px;
                border-width: 4px;
            }
            .certificate-title {
                font-size: 24px;
                letter-spacing: 3px;
            }
            .certificate-title::before,
            .certificate-title::after {
                font-size: 18px;
                margin: 0 10px;
            }
            .title-underline {
                width: 140px;
            }
            .student-name {
                font-size: 36px;
                padding: 10px 20px;
            }
            .certificate-details {
                flex-direction: column;
                gap: 30px;
            }
            .certificate-footer {
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }
            .website-section {
                align-items: center;
                text-align: center;
            }
        }
        @media screen and (max-width: 480px) {
            .certificate-container {
                padding: 20px 15px;
            }
            .certificate-title {
                font-size: 18px;
                letter-spacing: 2px;
            }
            .certificate-title::before,
            .certificate-title::after {
                font-size: 14px;
                margin: 0 8px;
            }
            .title-underline {
                width: 100px;
            }
            .student-name {
                font-size: 28px;
                padding: 8px 15px;
            }
            .certificate-text {
                font-size: 14px;
            }
            .completed-text {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Background pattern -->
        <div class="background-pattern"></div>
        
        <!-- Ornate border frame -->
        <div class="border-frame"></div>
        
        <!-- Corner decorations -->
        <div class="corner-decoration corner-top-left"></div>
        <div class="corner-decoration corner-top-right"></div>
        <div class="corner-decoration corner-bottom-left"></div>
        <div class="corner-decoration corner-bottom-right"></div>

        <div class="content-wrapper">
            <!-- Header -->
            <div class="certificate-header">
                <img src="{{ public_path('logo.png') }}" 
                     alt="Logo" 
                     class="certificate-logo"
                     onerror="this.style.display='none'">
                <div class="certificate-title">{{ request('certification_title', 'CERTIFICATION') }}</div>
                <div class="title-underline"></div>
            </div>

            <!-- Main Content -->
            <div class="certificate-content">
                <p class="certificate-text">
                    {{ request('certify_text', 'This is to certify that') }}
                </p>
                <div class="student-name">
                    {{ $certificate->student_name }}
                </div>
                <p class="completed-text">
                    {{ request('completed_text', 'has successfully completed the course') }}
                </p>

                <div class="divider"></div>

                <!-- Details -->
                <div class="certificate-details">
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

            <!-- Footer -->
            <div class="certificate-footer">
                <div class="certificate-date-section">
                    <div class="certificate-date-label">{{ request('date_label', 'Date') }}</div>
                    <div class="certificate-date">
                        @if($certificate->issue_date instanceof \Carbon\Carbon)
                            {{ $certificate->issue_date->format('F d, Y') }}
                        @elseif(is_string($certificate->issue_date))
                            {{ \Carbon\Carbon::parse($certificate->issue_date)->format('F d, Y') }}
                        @else
                            {{ now()->format('F d, Y') }}
                        @endif
                    </div>
                </div>
                <div class="website-section">
                    <img src="{{ public_path('ketm5.png') }}" alt="Almajd Academy" class="website-logo" onerror="this.style.display='none'">
                    <span class="website-url">www.almajdacademy.org</span>
                </div>
            </div>
            
            <div class="certificate-number">{{ $certificate->certificate_number }}</div>
        </div>
    </div>
</body>
</html>
