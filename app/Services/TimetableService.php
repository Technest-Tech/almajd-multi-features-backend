<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TimetableService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Timetable::with(['student', 'teacher', 'createdBy']);

        // Filter by student
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Filter by teacher
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        // Filter by period (from-to dates)
        if (isset($filters['from_date'])) {
            $query->where('end_date', '>=', $filters['from_date']);
        }
        if (isset($filters['to_date'])) {
            $query->where('start_date', '<=', $filters['to_date']);
        }

        // Filter by active status (only show active by default, unless explicitly filtered)
        if (!isset($filters['include_inactive'])) {
            // Check if is_active column exists, if not, show all (backward compatibility)
            $columns = \Schema::getColumnListing('timetables');
            if (in_array('is_active', $columns)) {
                $query->where(function($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                });
            }
        }

        return $query->orderBy('start_date', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?Timetable
    {
        return Timetable::with(['student', 'teacher', 'createdBy', 'events'])
            ->find($id);
    }

    public function create(array $data): Timetable
    {
        DB::beginTransaction();
        try {
            $timetable = Timetable::create($data);
            
            // Generate events for all selected days in date range
            $this->generateEvents($timetable);
            
            DB::commit();
            return $timetable->load(['student', 'teacher', 'createdBy']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Timetable $timetable, array $data): Timetable
    {
        DB::beginTransaction();
        try {
            $timetable->update($data);
            
            // Delete existing events and regenerate
            $timetable->events()->delete();
            $this->generateEvents($timetable);
            
            DB::commit();
            return $timetable->fresh()->load(['student', 'teacher', 'createdBy']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAll(Timetable $timetable): bool
    {
        // Events will be deleted via cascade, but we can be explicit
        $timetable->events()->delete();
        return $timetable->delete();
    }

    public function getEvents(array $filters = []): \Illuminate\Support\Collection
    {
        $query = TimetableEvent::with(['timetable.student', 'teacher']);

        // Filter by student
        if (isset($filters['student_id'])) {
            $query->whereHas('timetable', function ($q) use ($filters) {
                $q->where('student_id', $filters['student_id']);
            });
        }

        // Filter by teacher
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        // Filter by date range
        if (isset($filters['from_date'])) {
            $query->where('event_date', '>=', $filters['from_date']);
        }
        if (isset($filters['to_date'])) {
            $query->where('event_date', '<=', $filters['to_date']);
        }

        // Filter by specific date
        if (isset($filters['date'])) {
            $query->where('event_date', $filters['date']);
        }

        // Only show events from active timetables (if is_active column exists)
        $columns = \Schema::getColumnListing('timetables');
        if (in_array('is_active', $columns)) {
            $query->whereHas('timetable', function ($q) {
                $q->where(function($q2) {
                    $q2->where('is_active', true)->orWhereNull('is_active');
                });
            });
        }

        return $query->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($event) {
                $startTime = is_string($event->start_time) ? $event->start_time : $event->start_time->format('H:i:s');
                $endTime = is_string($event->end_time) ? $event->end_time : $event->end_time->format('H:i:s');
                
                // Check if event crosses midnight (end_time < start_time)
                $startTimeObj = is_string($event->start_time) ? Carbon::parse($event->start_time) : $event->start_time;
                $endTimeObj = is_string($event->end_time) ? Carbon::parse($event->end_time) : $event->end_time;
                
                // If end time is less than start time, it means it crosses midnight
                $crossesMidnight = false;
                if (is_string($event->start_time)) {
                    $startHour = (int)substr($event->start_time, 0, 2);
                    $endHour = (int)substr($event->end_time, 0, 2);
                    $crossesMidnight = $endHour < $startHour || ($endHour === $startHour && substr($event->end_time, 3, 2) < substr($event->start_time, 3, 2));
                } else {
                    $crossesMidnight = $endTimeObj->lt($startTimeObj);
                }
                
                // Calculate end date - if crosses midnight, add one day
                $endDate = $event->event_date->copy();
                if ($crossesMidnight) {
                    $endDate->addDay();
                }
                
                return [
                    'id' => $event->id,
                    'timetable_id' => $event->timetable_id,
                    'title' => $event->course_name,
                    'student_name' => $event->timetable->student->name ?? '',
                    'teacher_name' => $event->teacher->name ?? '',
                    'start' => $event->event_date->format('Y-m-d') . 'T' . $startTime,
                    'end' => $endDate->format('Y-m-d') . 'T' . $endTime,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'start_time' => substr($startTime, 0, 5), // Get HH:mm format
                    'end_time' => substr($endTime, 0, 5), // Get HH:mm format
                    'course_name' => $event->course_name,
                    'status' => $event->status,
                    'notes' => $event->notes,
                ];
            });
    }

    public function updateEvent(TimetableEvent $event, array $data): TimetableEvent
    {
        $event->update($data);
        return $event->fresh()->load(['timetable.student', 'teacher']);
    }

    public function deleteEventById(int $eventId): bool
    {
        $event = TimetableEvent::findOrFail($eventId);
        return $event->delete();
    }

    public function deleteEvent(TimetableEvent $event): bool
    {
        return $event->delete();
    }

    public function getEventById(int $id): ?TimetableEvent
    {
        return TimetableEvent::with(['timetable.student', 'teacher'])->find($id);
    }

    /**
     * Create a single event or multiple events based on recurrence
     */
    public function createEvent(array $data): TimetableEvent|SupportCollection
    {
        $recurrence = $data['recurrence'] ?? 'single'; // single, day, week, month, year
        $startDate = Carbon::parse($data['event_date']);
        $endDate = $startDate->copy();
        
        // Calculate end date based on recurrence
        switch ($recurrence) {
            case 'day':
                // Same day only
                break;
            case 'week':
                $endDate = $startDate->copy()->endOfWeek();
                break;
            case 'month':
                $endDate = $startDate->copy()->endOfMonth();
                break;
            case 'year':
                $endDate = $startDate->copy()->endOfYear();
                break;
            default:
                // single - just one event
                break;
        }
        
        // Get timetable if provided, otherwise create standalone events
        $timetable = null;
        $timetableId = null;
        if (!empty($data['timetable_id'])) {
            $timetable = Timetable::find($data['timetable_id']);
            $timetableId = $timetable ? $timetable->id : null;
        }
        
        // Use provided values or fallback to timetable values
        $startTime = $data['start_time'] ?? ($timetable ? $timetable->start_time : '09:00:00');
        $endTime = $data['end_time'] ?? ($timetable ? $timetable->end_time : '10:00:00');
        $teacherId = $data['teacher_id'] ?? ($timetable ? $timetable->teacher_id : null);
        $courseName = $data['course_name'] ?? ($timetable ? $timetable->course_name : 'Event');
        
        if (!$teacherId) {
            throw new \Exception('Teacher is required to create an event.');
        }
        
        $events = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // For single event, only create on the specified date
            if ($recurrence === 'single') {
                if ($currentDate->format('Y-m-d') === $startDate->format('Y-m-d')) {
                    $events[] = [
                        'timetable_id' => $timetableId,
                        'event_date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'teacher_id' => $teacherId,
                        'course_name' => $courseName,
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                // For recurrence, check if current day matches timetable's days_of_week (if timetable exists)
                if ($timetable && !empty($timetable->days_of_week)) {
                    $currentDayOfWeek = $currentDate->dayOfWeek;
                    $currentOurDayNumber = $currentDayOfWeek === 0 ? 7 : $currentDayOfWeek;
                    
                    if (in_array($currentOurDayNumber, $timetable->days_of_week)) {
                        $events[] = [
                            'timetable_id' => $timetableId,
                            'event_date' => $currentDate->format('Y-m-d'),
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'teacher_id' => $teacherId,
                            'course_name' => $courseName,
                            'status' => 'scheduled',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                } else {
                    // No timetable or no days_of_week - create for all days in the range
                    $events[] = [
                        'timetable_id' => $timetableId,
                        'event_date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'teacher_id' => $teacherId,
                        'course_name' => $courseName,
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            $currentDate->addDay();
        }
        
        if (empty($events)) {
            throw new \Exception('No events could be created for the selected recurrence.');
        }
        
        TimetableEvent::insert($events);
        
        // Return the first event if single, or all events if multiple
        if ($recurrence === 'single') {
            return TimetableEvent::with(['timetable.student', 'teacher'])
                ->where('event_date', $startDate->format('Y-m-d'))
                ->where('teacher_id', $teacherId)
                ->where('start_time', $startTime)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        return TimetableEvent::with(['timetable.student', 'teacher'])
            ->whereBetween('event_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('teacher_id', $teacherId)
            ->orderBy('event_date')
            ->get();
    }


    /**
     * Delete all events for a timetable
     */
    public function deleteAllEvents(int $timetableId): int
    {
        return TimetableEvent::where('timetable_id', $timetableId)->delete();
    }

    /**
     * Reschedule an event
     */
    public function rescheduleEvent(int $eventId, array $data): TimetableEvent
    {
        $event = TimetableEvent::findOrFail($eventId);
        
        $updateData = [];
        if (isset($data['event_date'])) {
            $updateData['event_date'] = $data['event_date'];
        }
        if (isset($data['start_time'])) {
            $updateData['start_time'] = $data['start_time'];
        }
        if (isset($data['end_time'])) {
            $updateData['end_time'] = $data['end_time'];
        }
        
        $event->update($updateData);
        return $event->fresh()->load(['timetable.student', 'teacher']);
    }

    /**
     * Generate individual events from timetable pattern
     */
    protected function generateEvents(Timetable $timetable): void
    {
        $startDate = Carbon::parse($timetable->start_date);
        $endDate = Carbon::parse($timetable->end_date);
        $daysOfWeek = $timetable->days_of_week ?? [];
        
        // Map day numbers: 1=Monday, 7=Sunday
        // Carbon uses: 0=Sunday, 1=Monday, ..., 6=Saturday
        $carbonDayMap = [
            1 => Carbon::MONDAY,    // Monday
            2 => Carbon::TUESDAY,   // Tuesday
            3 => Carbon::WEDNESDAY, // Wednesday
            4 => Carbon::THURSDAY,  // Thursday
            5 => Carbon::FRIDAY,    // Friday
            6 => Carbon::SATURDAY,  // Saturday
            7 => Carbon::SUNDAY,    // Sunday
        ];

        $events = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Convert our day number (1-7) to Carbon day constant
            $dayNumber = $currentDate->dayOfWeek;
            // Carbon uses 0=Sunday, so we need to map: 0->7, 1->1, 2->2, ..., 6->6
            $ourDayNumber = $dayNumber === 0 ? 7 : $dayNumber;
            
            if (in_array($ourDayNumber, $daysOfWeek)) {
                // The event_date is set to the current date (start day)
                // If end_time < start_time, the event crosses midnight
                // This will be handled in getEvents() when formatting for display
                $events[] = [
                    'timetable_id' => $timetable->id,
                    'event_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'teacher_id' => $timetable->teacher_id,
                    'course_name' => $timetable->course_name,
                    'status' => 'scheduled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $currentDate->addDay();
        }

        if (!empty($events)) {
            TimetableEvent::insert($events);
        }
    }
}

