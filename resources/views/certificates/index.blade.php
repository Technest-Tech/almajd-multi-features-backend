@extends('layouts.app')

@section('title', 'قوالب الشهادات')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    [dir="rtl"] {
        font-family: 'Cairo', 'Tajawal', 'Segoe UI', sans-serif;
        direction: rtl;
        text-align: right;
    }
    [dir="rtl"] .row {
        direction: rtl;
    }
    [dir="rtl"] .text-center {
        text-align: center !important;
    }
    .certificate-preview {
        direction: ltr !important;
        text-align: center !important;
    }
    [dir="rtl"] .card-body {
        text-align: center;
    }
    [dir="rtl"] h5, [dir="rtl"] p {
        text-align: center;
    }
</style>
<div dir="rtl">
<h1 class="h3 mb-4 text-center">
    <i class="fas fa-certificate text-primary"></i> قوالب الشهادات
</h1>

<div class="row g-4 justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card certificate-template-card h-100 shadow-sm" style="cursor: pointer; transition: transform 0.2s;" 
             onclick="selectTemplate('modern')"
             onmouseover="this.style.transform='translateY(-5px)'" 
             onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center p-4">
                <div class="certificate-preview mb-3" style="
                    background: linear-gradient(135deg, #fefefe 0%, #faf8f3 50%, #fefefe 100%);
                    border: 6px solid #d4af37;
                    padding: 25px 20px;
                    border-radius: 4px;
                    min-height: 300px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    position: relative;
                    overflow: hidden;
                ">
                    <!-- Traditional certificate preview -->
                    <div style="position: absolute; top: 10px; left: 10px; right: 10px; bottom: 10px; border: 3px solid #8b7355; border-radius: 2px; pointer-events: none;"></div>
                    <div style="position: absolute; top: 5px; left: 5px; width: 40px; height: 40px; border-top: 3px solid #8b7355; border-left: 3px solid #8b7355; pointer-events: none;"></div>
                    <div style="position: absolute; top: 5px; right: 5px; width: 40px; height: 40px; border-top: 3px solid #8b7355; border-right: 3px solid #8b7355; pointer-events: none;"></div>
                    <div style="position: absolute; bottom: 5px; left: 5px; width: 40px; height: 40px; border-bottom: 3px solid #8b7355; border-left: 3px solid #8b7355; pointer-events: none;"></div>
                    <div style="position: absolute; bottom: 5px; right: 5px; width: 40px; height: 40px; border-bottom: 3px solid #8b7355; border-right: 3px solid #8b7355; pointer-events: none;"></div>
                    
                    <div style="position: relative; z-index: 2; text-align: center;">
                        <div style="font-size: 20px; font-weight: 700; color: #2c3e50; letter-spacing: 4px; margin-bottom: 15px; text-transform: uppercase;">
                            CERTIFICATION
                        </div>
                        <div style="width: 120px; height: 2px; background: linear-gradient(90deg, transparent, #d4af37 20%, #8b7355 50%, #d4af37 80%, transparent); margin: 10px auto 20px;"></div>
                        <div style="font-size: 14px; color: #34495e; margin-bottom: 15px; font-style: italic;">
                            This is to certify that
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #2c3e50; margin: 15px 0; font-family: 'Playfair Display', serif;">
                            Student Name
                        </div>
                        <div style="font-size: 12px; color: #34495e; margin-bottom: 20px; font-style: italic;">
                            has successfully completed
                        </div>
                        <div style="display: flex; gap: 20px; justify-content: center; margin-top: 20px;">
                            <div style="text-align: center;">
                                <div style="font-size: 8px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 2px;">Manager</div>
                                <div style="font-size: 11px; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37; padding-bottom: 3px; display: inline-block;">Manager Name</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 8px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 2px;">Teacher</div>
                                <div style="font-size: 11px; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37; padding-bottom: 3px; display: inline-block;">Teacher Name</div>
                            </div>
                        </div>
                    </div>
                </div>
                <h5 class="mt-3">القالب الحديث</h5>
                <p class="text-muted small">تصميم شهادة تقليدي أنيق</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card certificate-template-card h-100 shadow-sm" style="cursor: pointer; transition: transform 0.2s;" 
             onclick="selectTemplate('default')"
             onmouseover="this.style.transform='translateY(-5px)'" 
             onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center p-4">
                <div class="certificate-preview mb-3" style="
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                    border: 4px solid #2c3e50;
                    padding: 30px 20px;
                    border-radius: 8px;
                    min-height: 300px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    position: relative;
                ">
                    <div style="height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <i class="fas fa-certificate fa-3x text-muted"></i>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #2c3e50; letter-spacing: 4px; margin-bottom: 20px;">
                        CERTIFICATION
                    </div>
                    <div style="font-size: 18px; color: #34495e; margin-bottom: 15px;">
                        Student Name
                    </div>
                    <div style="font-size: 14px; color: #7f8c8d; margin-bottom: 10px;">
                        Subject Name
                    </div>
                    <div style="position: absolute; bottom: 15px; left: 0; right: 0; font-size: 11px; color: #95a5a6;">
                        Manager Name • Teacher Name
                    </div>
                </div>
                <h5 class="mt-3">القالب الافتراضي</h5>
                <p class="text-muted small">تصميم احترافي كلاسيكي للشهادة</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card certificate-template-card h-100 shadow-sm" style="cursor: pointer; transition: transform 0.2s;" 
             onclick="selectTemplate('islamic')"
             onmouseover="this.style.transform='translateY(-5px)'" 
             onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center p-4">
                <div class="certificate-preview mb-3" style="
                    background: linear-gradient(135deg, #fef9e7 0%, #faf5e6 100%);
                    border: 4px solid #1a5f3f;
                    padding: 30px 20px;
                    border-radius: 8px;
                    min-height: 300px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    position: relative;
                ">
                    <div style="height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <i class="fas fa-star-and-crescent fa-3x" style="color: #d4af37;"></i>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #1a5f3f; letter-spacing: 4px; margin-bottom: 20px;">
                        CERTIFICATION
                    </div>
                    <div style="font-size: 18px; color: #2c3e50; margin-bottom: 15px;">
                        Student Name
                    </div>
                    <div style="font-size: 14px; color: #2d8659; margin-bottom: 10px;">
                        Subject Name
                    </div>
                    <div style="position: absolute; bottom: 15px; left: 0; right: 0; font-size: 11px; color: #1a5f3f;">
                        Manager Name • Teacher Name
                    </div>
                    <div style="position: absolute; top: 10px; left: 10px; font-size: 20px; color: #d4af37; opacity: 0.5;">✪</div>
                    <div style="position: absolute; top: 10px; right: 10px; font-size: 20px; color: #d4af37; opacity: 0.5;">✪</div>
                </div>
                <h5 class="mt-3">القالب الإسلامي</h5>
                <p class="text-muted small">تصميم إسلامي أنيق بأنماط هندسية</p>
            </div>
        </div>
    </div>
</div>

<script>
function selectTemplate(templateName) {
    // Redirect to certificate editor with template
    window.location.href = '{{ url("certificates/new") }}?template=' + templateName;
}
</script>

<style>
.certificate-template-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.certificate-template-card:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
}

.certificate-preview {
    transition: all 0.3s ease;
}

.certificate-template-card:nth-child(1):hover .certificate-preview {
    border-color: #d4af37 !important;
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.3) !important;
}

.certificate-template-card:nth-child(2):hover .certificate-preview {
    border-color: #3498db !important;
}

.certificate-template-card:nth-child(3):hover .certificate-preview {
    border-color: #2d8659 !important;
}
</style>
</div>
@endsection
