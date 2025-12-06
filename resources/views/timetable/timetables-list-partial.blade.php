@if($timetables->count() > 0)
    @foreach($timetables as $timetable)
        <div class="timetable-card {{ ($timetable->is_active ?? 1) ? '' : 'inactive' }}">
            <div class="timetable-header">
                <h3 class="timetable-title">{{ $timetable->course_name }}</h3>
                <span class="timetable-badge">{{ $timetable->timezone }}</span>
            </div>
            
            <div class="timetable-info">
                <div class="info-item">
                    <i class="fas fa-user-graduate"></i>
                    <div>
                        <div class="info-label">Student</div>
                        <div class="info-value">{{ $timetable->student->name ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>
                        <div class="info-label">Teacher</div>
                        <div class="info-value">{{ $timetable->teacher->name ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <div class="info-label">Time</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($timetable->start_time)->format('g:i A') }} - 
                            {{ \Carbon\Carbon::parse($timetable->end_time)->format('g:i A') }}
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <div class="info-label">Date Range</div>
                        <div class="info-value">
                            {{ $timetable->start_date->format('M d, Y') }} - 
                            {{ $timetable->end_date->format('M d, Y') }}
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar-week"></i>
                    <div>
                        <div class="info-label">Days</div>
                        <div class="info-value">
                            @php
                                $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                $days = $timetable->days_of_week ?? [];
                            @endphp
                            @foreach($days as $day)
                                <span class="days-badge">{{ $dayNames[$day] ?? $day }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <div class="info-label">Events</div>
                        <div class="info-value">{{ $timetable->events->count() }} scheduled</div>
                    </div>
                </div>
            </div>

            <div class="timetable-actions">
                <button type="button" class="btn-action btn-edit" onclick="openEditModal({{ $timetable->id }})" title="Edit Timetable">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button type="button" class="btn-action btn-deactivate" onclick="toggleTimetableStatus({{ $timetable->id }}, {{ $timetable->is_active ?? 1 }})" title="{{ ($timetable->is_active ?? 1) ? 'Deactivate' : 'Activate' }} Timetable">
                    <i class="fas fa-{{ ($timetable->is_active ?? 1) ? 'pause' : 'play' }}"></i> {{ ($timetable->is_active ?? 1) ? 'Deactivate' : 'Activate' }}
                </button>
                <button type="button" class="btn-action btn-delete" onclick="deleteTimetable({{ $timetable->id }})" title="Delete Timetable">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    @endforeach

    @if($timetables->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $timetables->links() }}
        </div>
    @endif
@else
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3>No Timetables Found</h3>
        <p>Create your first timetable to get started.</p>
    </div>
@endif

