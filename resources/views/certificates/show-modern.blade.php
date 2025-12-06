<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Cormorant+Garamond:wght@400;600;700&family=Lato:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(135deg, #e8e8e8 0%, #d4d4d4 100%);
            padding: 20px;
        }
        .toolbar {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .certificate-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
        }
        .certificate-container {
            width: 100%;
            max-width: 1000px;
            position: relative;
            background: linear-gradient(135deg, #fefefe 0%, #faf8f3 50%, #fefefe 100%);
            display: flex;
            flex-direction: column;
            padding: 25px 60px 40px 60px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            min-height: fit-content;
            height: auto;
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
        /* Editable styles */
        .editable {
            border: 2px dashed transparent;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
            cursor: text;
            display: inline-block;
            min-width: 100px;
        }
        .editable:hover {
            border-color: rgba(212, 175, 55, 0.5);
            background: rgba(212, 175, 55, 0.1);
        }
        .editable:focus {
            outline: none;
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.15);
        }
        /* Responsive styles */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }
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
            body {
                padding: 5px;
            }
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
        @media print {
            body {
                background: #f5f5f5;
                padding: 0;
            }
            .toolbar {
                display: none;
            }
            .editable {
                border: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <a href="{{ route('certificates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Certificates
            </a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-success" onclick="downloadCertificate()">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
    </div>

    <div class="certificate-wrapper">
        <div class="certificate-container" id="certificateContainer">
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
                    <img src="{{ asset('logo.png') }}" 
                         alt="Logo" 
                         class="certificate-logo"
                         id="certificateLogo">
                    <div class="certificate-title editable" contenteditable="true" id="certificationTitle">CERTIFICATION</div>
                    <div class="title-underline"></div>
                </div>

                <!-- Main Content -->
                <div class="certificate-content">
                    <p class="certificate-text editable" contenteditable="true" id="certifyText">
                        This is to certify that
                    </p>
                    <div class="student-name">
                        <span class="editable" 
                              contenteditable="true" 
                              data-field="student_name"
                              id="studentName">{{ $certificate->student_name }}</span>
                    </div>
                    <p class="completed-text editable" contenteditable="true" id="completedText">
                        has successfully completed the course
                    </p>

                    <div class="divider"></div>

                    <!-- Details -->
                    <div class="certificate-details">
                        <div class="detail-item">
                            <div class="detail-label editable" contenteditable="true" id="managerLabel">Manager</div>
                            <div class="detail-value">
                                <span class="editable" 
                                      contenteditable="true" 
                                      data-field="manager_name"
                                      id="managerName">{{ $certificate->manager_name }}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label editable" contenteditable="true" id="teacherLabel">Teacher</div>
                            <div class="detail-value">
                                <span class="editable" 
                                      contenteditable="true" 
                                      data-field="teacher_name"
                                      id="teacherName">{{ $certificate->teacher_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="certificate-footer">
                    <div class="certificate-date-section">
                        <div class="certificate-date-label editable" contenteditable="true" id="dateLabel">Date</div>
                        <div class="certificate-date">
                            <span class="editable" 
                                  contenteditable="true" 
                                  data-field="issue_date"
                                  data-type="date"
                                  id="issueDate">{{ $certificate->issue_date->format('F d, Y') }}</span>
                        </div>
                    </div>
                    <div class="website-section">
                        <img src="{{ asset('ketm5.png') }}" alt="Almajd Academy" class="website-logo">
                        <span class="website-url">www.almajdacademy.org</span>
                    </div>
                </div>
                
                <div class="certificate-number">{{ $certificate->certificate_number }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const certificateId = '{{ $certificate->id }}';

        // Handle editable fields (no saving - live preview only)
        document.querySelectorAll('.editable').forEach(element => {
            element.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });
        });

        function downloadCertificate() {
            // Collect all certificate data including all editable text
            const certificateData = {
                student_name: document.getElementById('studentName').textContent.trim(),
                manager_name: document.getElementById('managerName').textContent.trim(),
                teacher_name: document.getElementById('teacherName').textContent.trim(),
                certificate_number: '{{ $certificate->certificate_number }}',
                issue_date: (() => {
                    const issueDateEl = document.getElementById('issueDate');
                    if (issueDateEl) {
                        const dateText = issueDateEl.textContent.trim();
                        const date = new Date(dateText);
                        if (!isNaN(date.getTime())) {
                            return date.toISOString().split('T')[0];
                        }
                    }
                    return '{{ $certificate->issue_date->format("Y-m-d") }}';
                })(),
                // Additional editable fields
                certification_title: document.getElementById('certificationTitle')?.textContent.trim() || 'CERTIFICATION',
                certify_text: document.getElementById('certifyText')?.textContent.trim() || 'This is to certify that',
                completed_text: document.getElementById('completedText')?.textContent.trim() || 'has successfully completed the course',
                manager_label: document.getElementById('managerLabel')?.textContent.trim() || 'Manager',
                teacher_label: document.getElementById('teacherLabel')?.textContent.trim() || 'Teacher',
                date_label: document.getElementById('dateLabel')?.textContent.trim() || 'Date',
                template: 'modern'
            };

            // Build download URL with all parameters for Flutter WebView compatibility
            // Flutter intercepts GET requests, so we need everything in the URL
            const baseUrl = '{{ route("certificates.download", "new") }}';
            const params = new URLSearchParams();
            
            // Add all certificate data as query parameters
            Object.keys(certificateData).forEach(key => {
                params.append(key, certificateData[key]);
            });
            
            // Add CSRF token
            params.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            // Construct full URL with template parameter
            const downloadUrl = baseUrl + '?template=modern&' + params.toString();
            
            // Use window.location for GET request (works better with Flutter WebView interception)
            window.location.href = downloadUrl;
        }
    </script>
</body>
</html>
