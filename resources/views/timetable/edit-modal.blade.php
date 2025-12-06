<form method="POST" action="{{ route('timetable.update', $timetable->id) }}" id="timetableEditFormModal">
    @csrf
    @method('PUT')

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-group">
        <label class="form-label">
            Student <span class="required">*</span>
        </label>
        <select name="student_id" class="form-select" required>
            <option value="">Select a student</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ old('student_id', $timetable->student_id) == $student->id ? 'selected' : '' }}>
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
                <option value="{{ $teacher->id }}" {{ old('teacher_id', $timetable->teacher_id) == $teacher->id ? 'selected' : '' }}>
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
               value="{{ old('course_name', $timetable->course_name) }}" 
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
                <option value="{{ $tz }}" {{ old('timezone', $timetable->timezone) == $tz ? 'selected' : '' }}>
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
                       value="{{ old('start_time', \Carbon\Carbon::parse($timetable->start_time)->format('H:i')) }}" required>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label class="form-label">
                    End Time <span class="required">*</span>
                </label>
                <input type="time" name="end_time" class="form-control" 
                       value="{{ old('end_time', \Carbon\Carbon::parse($timetable->end_time)->format('H:i')) }}" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">
            Days of Week <span class="required">*</span>
        </label>
        <div class="days-of-week-group">
            @php
                $selectedDays = old('days_of_week', $timetable->days_of_week ?? []);
            @endphp
            <input type="checkbox" name="days_of_week[]" value="1" id="edit_modal_day1" class="day-checkbox" 
                   {{ in_array(1, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day1" class="day-label">Monday</label>

            <input type="checkbox" name="days_of_week[]" value="2" id="edit_modal_day2" class="day-checkbox"
                   {{ in_array(2, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day2" class="day-label">Tuesday</label>

            <input type="checkbox" name="days_of_week[]" value="3" id="edit_modal_day3" class="day-checkbox"
                   {{ in_array(3, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day3" class="day-label">Wednesday</label>

            <input type="checkbox" name="days_of_week[]" value="4" id="edit_modal_day4" class="day-checkbox"
                   {{ in_array(4, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day4" class="day-label">Thursday</label>

            <input type="checkbox" name="days_of_week[]" value="5" id="edit_modal_day5" class="day-checkbox"
                   {{ in_array(5, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day5" class="day-label">Friday</label>

            <input type="checkbox" name="days_of_week[]" value="6" id="edit_modal_day6" class="day-checkbox"
                   {{ in_array(6, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day6" class="day-label">Saturday</label>

            <input type="checkbox" name="days_of_week[]" value="7" id="edit_modal_day7" class="day-checkbox"
                   {{ in_array(7, $selectedDays) ? 'checked' : '' }}>
            <label for="edit_modal_day7" class="day-label">Sunday</label>
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
                       value="{{ old('start_date', $timetable->start_date->format('Y-m-d')) }}" required>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label class="form-label">
                    End Date <span class="required">*</span>
                </label>
                <input type="date" name="end_date" class="form-control" 
                       value="{{ old('end_date', $timetable->end_date->format('Y-m-d')) }}" required>
            </div>
        </div>
    </div>

    <div class="modal-footer-buttons">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-submit-modal">
            <i class="fas fa-save"></i>
            <span>Update Timetable</span>
        </button>
    </div>
</form>

