@extends('layouts.app')

@section('title', 'Certificate Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-certificate text-primary"></i> Certificate Templates
    </h1>
    <a href="{{ route('certificates.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Certificates
    </a>
</div>

<div class="row g-4">
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
                    <div style="font-size: 14px; color: #7f8c8d;">
                        Subject Name
                    </div>
                </div>
                <h5 class="mt-3">Default Template</h5>
                <p class="text-muted small">Classic professional certificate design</p>
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
                    <div style="font-size: 14px; color: #2d8659;">
                        Subject Name
                    </div>
                    <div style="position: absolute; top: 10px; left: 10px; font-size: 20px; color: #d4af37; opacity: 0.5;">✪</div>
                    <div style="position: absolute; top: 10px; right: 10px; font-size: 20px; color: #d4af37; opacity: 0.5;">✪</div>
                </div>
                <h5 class="mt-3">Islamic Template</h5>
                <p class="text-muted small">Elegant Islamic design with geometric patterns</p>
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
@endsection
