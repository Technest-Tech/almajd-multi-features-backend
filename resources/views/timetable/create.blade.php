<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Timetable</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin: 0 auto;
            max-width: 800px;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-title i {
            color: #667eea;
        }

        .btn-back {
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .form-label .required {
            color: #e74c3c;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .days-of-week-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .day-checkbox {
            display: none;
        }

        .day-label {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #495057;
            user-select: none;
        }

        .day-checkbox:checked + .day-label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .day-label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .row {
            display: flex;
            gap: 15px;
        }

        .col {
            flex: 1;
        }

        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-container {
                padding: 20px;
            }

            .form-title {
                font-size: 22px;
            }

            .form-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .row {
                flex-direction: column;
            }

            .days-of-week-group {
                justify-content: center;
            }

            .day-label {
                padding: 8px 16px;
                font-size: 14px;
            }
        }

        @media screen and (max-width: 480px) {
            .form-container {
                padding: 15px;
            }

            .form-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1 class="form-title">
                <i class="fas fa-calendar-plus"></i>
                <span>Create New Timetable</span>
            </h1>
            <a href="{{ route('timetable.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Calendar
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('timetable.store') }}" id="timetableForm">
            @csrf

            <div class="form-group">
                <label class="form-label">
                    Student <span class="required">*</span>
                </label>
                <select name="student_id" class="form-select" required>
                    <option value="">Select a student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Teacher <span class="required">*</span>
                </label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">Select a teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Course Name <span class="required">*</span>
                </label>
                <input type="text" name="course_name" class="form-control" 
                       value="{{ old('course_name') }}" 
                       placeholder="e.g., Mathematics, English, Science" 
                       required maxlength="255">
            </div>

            <div class="form-group">
                <label class="form-label">
                    Timezone <span class="required">*</span>
                </label>
                <select name="timezone" class="form-select" required>
                    <option value="">Select timezone</option>
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone') == $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            Start Time <span class="required">*</span>
                        </label>
                        <input type="time" name="start_time" class="form-control" 
                               value="{{ old('start_time') }}" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            End Time <span class="required">*</span>
                        </label>
                        <input type="time" name="end_time" class="form-control" 
                               value="{{ old('end_time') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Days of Week <span class="required">*</span>
                </label>
                <div class="days-of-week-group">
                    <input type="checkbox" name="days_of_week[]" value="1" id="day1" class="day-checkbox" 
                           {{ in_array(1, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day1" class="day-label">Monday</label>

                    <input type="checkbox" name="days_of_week[]" value="2" id="day2" class="day-checkbox"
                           {{ in_array(2, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day2" class="day-label">Tuesday</label>

                    <input type="checkbox" name="days_of_week[]" value="3" id="day3" class="day-checkbox"
                           {{ in_array(3, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day3" class="day-label">Wednesday</label>

                    <input type="checkbox" name="days_of_week[]" value="4" id="day4" class="day-checkbox"
                           {{ in_array(4, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day4" class="day-label">Thursday</label>

                    <input type="checkbox" name="days_of_week[]" value="5" id="day5" class="day-checkbox"
                           {{ in_array(5, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day5" class="day-label">Friday</label>

                    <input type="checkbox" name="days_of_week[]" value="6" id="day6" class="day-checkbox"
                           {{ in_array(6, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day6" class="day-label">Saturday</label>

                    <input type="checkbox" name="days_of_week[]" value="7" id="day7" class="day-checkbox"
                           {{ in_array(7, old('days_of_week', [])) ? 'checked' : '' }}>
                    <label for="day7" class="day-label">Sunday</label>
                </div>
                <div class="help-text">Select at least one day</div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            Start Date <span class="required">*</span>
                        </label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ old('start_date') }}" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            End Date <span class="required">*</span>
                        </label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ old('end_date') }}" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                Create Timetable
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('timetableForm').addEventListener('submit', function(e) {
            // Validate days of week
            const checkedDays = document.querySelectorAll('input[name="days_of_week[]"]:checked');
            if (checkedDays.length === 0) {
                e.preventDefault();
                alert('Please select at least one day of the week.');
                return false;
            }

            // Validate end time is after start time
            const startTime = document.querySelector('input[name="start_time"]').value;
            const endTime = document.querySelector('input[name="end_time"]').value;
            if (startTime && endTime && endTime <= startTime) {
                e.preventDefault();
                alert('End time must be after start time.');
                return false;
            }

            // Validate end date is after or equal to start date
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            if (startDate && endDate && endDate < startDate) {
                e.preventDefault();
                alert('End date must be after or equal to start date.');
                return false;
            }

            // Disable submit button to prevent double submission
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        });
    </script>
</body>
</html>

