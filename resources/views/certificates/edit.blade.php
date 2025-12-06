@extends('layouts.app')

@section('title', 'Edit Certificate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-edit text-primary"></i> Edit Certificate
    </h1>
    <a href="{{ route('certificates.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<form action="{{ route('certificates.update', $certificate) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="student_name" class="form-label">Student Name <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('student_name') is-invalid @enderror" 
                   id="student_name" 
                   name="student_name" 
                   value="{{ old('student_name', $certificate->student_name) }}" 
                   required>
            @error('student_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('subject') is-invalid @enderror" 
                   id="subject" 
                   name="subject" 
                   value="{{ old('subject', $certificate->subject) }}" 
                   required>
            @error('subject')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="manager_name" class="form-label">Manager Name <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('manager_name') is-invalid @enderror" 
                   id="manager_name" 
                   name="manager_name" 
                   value="{{ old('manager_name', $certificate->manager_name) }}" 
                   required>
            @error('manager_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="teacher_name" class="form-label">Teacher Name <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('teacher_name') is-invalid @enderror" 
                   id="teacher_name" 
                   name="teacher_name" 
                   value="{{ old('teacher_name', $certificate->teacher_name) }}" 
                   required>
            @error('teacher_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="certificate_number" class="form-label">Certificate Number <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('certificate_number') is-invalid @enderror" 
                   id="certificate_number" 
                   name="certificate_number" 
                   value="{{ old('certificate_number', $certificate->certificate_number) }}" 
                   required>
            @error('certificate_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
            <input type="date" 
                   class="form-control @error('issue_date') is-invalid @enderror" 
                   id="issue_date" 
                   name="issue_date" 
                   value="{{ old('issue_date', $certificate->issue_date->format('Y-m-d')) }}" 
                   required>
            @error('issue_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="logo" class="form-label">Logo</label>
        @if($certificate->logo_path)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $certificate->logo_path) }}" 
                     alt="Current Logo" 
                     style="max-height: 100px; max-width: 200px;" 
                     class="img-thumbnail">
                <p class="text-muted small mt-1">Current logo</p>
            </div>
        @endif
        <input type="file" 
               class="form-control @error('logo') is-invalid @enderror" 
               id="logo" 
               name="logo" 
               accept="image/*">
        <small class="form-text text-muted">Upload a new logo to replace the current one (max 2MB, jpeg/png/jpg/gif)</small>
        @error('logo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('certificates.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Certificate
        </button>
    </div>
</form>
@endsection

