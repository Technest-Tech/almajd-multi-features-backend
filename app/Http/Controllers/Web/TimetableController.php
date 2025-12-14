<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Models\User;
use App\Models\Timetable;
use App\Models\TimetableEvent;
use App\Services\TimetableService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    /**
     * Helper method to create JSON response with proper headers for WebView
     */
    protected function jsonResponse($data, $status = 200): JsonResponse
    {
        return response()->json($data, $status)
            ->header('Content-Type', 'application/json')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Display the timetable calendar view.
     */
    public function index(Request $request)
    {
        // Get filter parameters from query string
        $filters = [
            'student_id' => $request->query('student_id'),
            'teacher_id' => $request->query('teacher_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        // Remove null values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        // Get students and teachers for filter dropdowns
        $students = User::where('user_type', UserType::Student)
            ->orderBy('name')
            ->get(['id', 'name']);

        $teachers = User::where('user_type', UserType::Teacher)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get all timetables for the list view (including inactive)
        $timetables = $this->timetableService->getAll([
            'per_page' => 50, // Get more timetables for the list
            'include_inactive' => true, // Show all timetables in the list view
        ]);

        // If AJAX request for timetables list only
        if ($request->ajax() && $request->query('section') === 'timetables') {
            return view('timetable.timetables-list-partial', compact('timetables'));
        }

        // If checking affected count for timezone adjustment
        if ($request->ajax() && $request->has('check_country')) {
            $count = Timetable::where('timezone', $request->query('check_country'))->count();
            return response()->json(['count' => $count]);
        }

        return view('timetable.index', compact('filters', 'students', 'teachers', 'timetables'));
    }

    /**
     * Display the teacher view-only timetable calendar.
     */
    public function teacherView(Request $request)
    {
        // Get teacher_id from query parameter
        $teacherId = $request->query('teacher_id');
        
        if (!$teacherId) {
            // If no teacher_id provided, return error or redirect
            return redirect()->route('timetable.index')
                ->with('error', 'Teacher ID is required');
        }

        // Verify teacher exists
        $teacher = User::where('id', $teacherId)
            ->where('user_type', UserType::Teacher)
            ->first();

        if (!$teacher) {
            return redirect()->route('timetable.index')
                ->with('error', 'Teacher not found');
        }

        return view('timetable.teacher-view', compact('teacherId'));
    }

    /**
     * Get events as JSON for FullCalendar.
     */
    public function events(Request $request): JsonResponse
    {
        $filters = $request->only(['student_id', 'teacher_id', 'from_date', 'to_date', 'date']);
        
        // Remove null values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $events = $this->timetableService->getEvents($filters);

        // Format events for FullCalendar with color coding by status
        $formattedEvents = $events->map(function ($event) {
            $color = $this->getStatusColor($event['status'] ?? 'scheduled');
            
            return [
                'id' => $event['id'],
                'title' => $event['course_name'],
                'start' => $event['start'],
                'end' => $event['end'],
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'timetable_id' => $event['timetable_id'] ?? null,
                    'student_name' => $event['student_name'],
                    'teacher_name' => $event['teacher_name'],
                    'course_name' => $event['course_name'],
                    'status' => $event['status'] ?? 'scheduled',
                    'notes' => $event['notes'] ?? '',
                    'start_time' => $event['start_time'],
                    'end_time' => $event['end_time'],
                    'event_date' => $event['event_date'],
                ],
            ];
        });

        return $this->jsonResponse($formattedEvents);
    }

    /**
     * Show the form for creating a new timetable.
     */
    public function create()
    {
        $students = User::where('user_type', UserType::Student)
            ->orderBy('name')
            ->get(['id', 'name']);

        $teachers = User::where('user_type', UserType::Teacher)
            ->orderBy('name')
            ->get(['id', 'name']);

        $timezones = ['Canada', 'America', 'United Kingdom', 'Egypt', 'France', 'Australia'];

        // Check if this is an AJAX request (for modal)
        if (request()->ajax()) {
            return view('timetable.create-modal', compact('students', 'teachers', 'timezones'));
        }

        return view('timetable.create', compact('students', 'teachers', 'timezones'));
    }

    /**
     * Store a newly created timetable.
     */
    public function store(StoreTimetableRequest $request)
    {
        try {
            $data = $request->validated();
            // For web, we'll use a default created_by (or you can implement auth later)
            // For now, we'll use the first admin or the teacher
            $data['created_by'] = $data['teacher_id']; // Using teacher as creator for web
            
            $timetable = $this->timetableService->create($data);
            
            // If AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timetable created successfully! Events have been generated.',
                    'timetable' => $timetable
                ]);
            }
            
            return redirect()->route('timetable.index')
                ->with('success', 'Timetable created successfully! Events have been generated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // If AJAX request, return JSON validation errors
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create timetable: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create timetable: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a timetable.
     */
    public function edit($id)
    {
        $timetable = $this->timetableService->getById((int) $id);

        if (!$timetable) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Timetable not found'], 404);
            }
            return redirect()->route('timetable.index')
                ->with('error', 'Timetable not found');
        }

        $students = User::where('user_type', UserType::Student)
            ->orderBy('name')
            ->get(['id', 'name']);

        $teachers = User::where('user_type', UserType::Teacher)
            ->orderBy('name')
            ->get(['id', 'name']);

        $timezones = ['Canada', 'America', 'United Kingdom', 'Egypt', 'France', 'Australia'];

        // Check if this is an AJAX request (for modal)
        if (request()->ajax()) {
            return view('timetable.edit-modal', compact('timetable', 'students', 'teachers', 'timezones'));
        }

        return view('timetable.edit', compact('timetable', 'students', 'teachers', 'timezones'));
    }

    /**
     * Update a timetable.
     */
    public function update(\App\Http\Requests\UpdateTimetableRequest $request, $id)
    {
        try {
            $timetable = $this->timetableService->getById((int) $id);

            if (!$timetable) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Timetable not found'], 404);
                }
                return redirect()->route('timetable.index')
                    ->with('error', 'Timetable not found');
            }

            $data = $request->validated();
            $timetable = $this->timetableService->update($timetable, $data);

            // If AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timetable updated successfully!',
                    'timetable' => $timetable
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', 'Timetable updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update timetable: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update timetable: ' . $e->getMessage());
        }
    }

    /**
     * Delete a timetable.
     */
    public function destroy($id)
    {
        try {
            $timetable = $this->timetableService->getById((int) $id);

            if (!$timetable) {
                if (request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Timetable not found'], 404);
                }
                return redirect()->route('timetable.index')
                    ->with('error', 'Timetable not found');
            }

            $this->timetableService->deleteAll($timetable);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timetable deleted successfully!'
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', 'Timetable deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete timetable: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('timetable.index')
                ->with('error', 'Failed to delete timetable: ' . $e->getMessage());
        }
    }

    /**
     * Toggle timetable active status.
     */
    public function toggleStatus($id)
    {
        try {
            $timetable = $this->timetableService->getById((int) $id);

            if (!$timetable) {
                return response()->json(['success' => false, 'message' => 'Timetable not found'], 404);
            }

            // Toggle is_active status (default to true if not set)
            $isActive = !($timetable->is_active ?? true);
            $timetable->is_active = $isActive;
            $timetable->save();

            return response()->json([
                'success' => true,
                'message' => $isActive ? 'Timetable activated successfully!' : 'Timetable deactivated successfully!',
                'is_active' => $isActive
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timetable status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get color for event status.
     */
    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'completed' => '#2ecc71',
            'cancelled' => '#e74c3c',
            default => '#3498db', // scheduled
        };
    }

    /**
     * Store a single event
     */
    public function storeEvent(Request $request)
    {
        try {
            $validated = $request->validate([
                'timetable_id' => 'nullable|exists:timetables,id',
                'event_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'student_id' => 'required|exists:users,id',
                'teacher_id' => 'required|exists:users,id',
                'course_name' => 'required|string',
                'recurrence' => 'nullable|in:single,day,week,month,year',
            ]);

            $event = $this->timetableService->createEvent($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event(s) created successfully!',
                    'event' => $event
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', 'Event(s) created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create event: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single event
     */
    public function destroyEvent($id)
    {
        try {
            $this->timetableService->deleteEventById((int) $id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event deleted successfully!'
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', 'Event deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete event: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }

    /**
     * Delete all events for a timetable
     */
    public function destroyAllEvents($id)
    {
        try {
            $count = $this->timetableService->deleteAllEvents((int) $id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "All events ({$count}) deleted successfully!"
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', "All events ({$count}) deleted successfully!");
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete events: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to delete events: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule an event
     */
    public function rescheduleEvent(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'event_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
            ]);

            $event = $this->timetableService->rescheduleEvent((int) $id, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event rescheduled successfully!',
                    'event' => $event
                ]);
            }

            return redirect()->route('timetable.index')
                ->with('success', 'Event rescheduled successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reschedule event: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to reschedule event: ' . $e->getMessage());
        }
    }

    /**
     * Adjust timezone for all timetables in a country
     */
    public function adjustTimezone(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'country' => 'required|string|in:Canada,America,United Kingdom,Egypt,France,Australia',
                'hours' => 'required|integer|min:-23|max:23',
            ]);

            $country = $validated['country'];
            $hours = $validated['hours'];

            // Find all timetables with matching timezone
            $timetables = Timetable::where('timezone', $country)->get();

            if ($timetables->isEmpty()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'No timetables found for the selected country'
                ], 404);
            }

            DB::beginTransaction();

            foreach ($timetables as $timetable) {
                // Update timetable start_time and end_time
                $startTime = Carbon::parse($timetable->start_time);
                $endTime = Carbon::parse($timetable->end_time);

                $newStartTime = $startTime->copy()->addHours($hours);
                $newEndTime = $endTime->copy()->addHours($hours);

                // Handle day rollover
                if ($newStartTime->format('H:i') < $startTime->format('H:i') && $hours > 0) {
                    // Time went past midnight forward
                } elseif ($newStartTime->format('H:i') > $startTime->format('H:i') && $hours < 0) {
                    // Time went before midnight backward
                }

                $timetable->start_time = $newStartTime->format('H:i:s');
                $timetable->end_time = $newEndTime->format('H:i:s');
                $timetable->save();

                // Update all related events
                $events = TimetableEvent::where('timetable_id', $timetable->id)->get();
                foreach ($events as $event) {
                    $eventStartTime = Carbon::parse($event->start_time);
                    $eventEndTime = Carbon::parse($event->end_time);

                    $newEventStartTime = $eventStartTime->copy()->addHours($hours);
                    $newEventEndTime = $eventEndTime->copy()->addHours($hours);

                    $event->start_time = $newEventStartTime->format('H:i:s');
                    $event->end_time = $newEventEndTime->format('H:i:s');
                    $event->save();
                }
            }

            DB::commit();

            return $this->jsonResponse([
                'success' => true,
                'message' => "Successfully adjusted time by {$hours} hour(s) for {$timetables->count()} timetable(s) in {$country}",
                'affected_count' => $timetables->count()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to adjust timezone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp reminder
     */
    public function sendWhatsAppReminder(Request $request, WhatsAppService $whatsAppService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'from_time' => 'required|date_format:H:i',
                'to_time' => 'required|date_format:H:i|after:from_time',
            ]);

            // Get default phone number from config
            $phone = config('services.whatsapp.default_phone', '201554134201');
            
            // Clean phone number - remove any non-digit characters
            $phone = preg_replace('/\D/', '', $phone);
            
            // Validate cleaned phone number has at least 10 digits
            if (strlen($phone) < 10) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid default phone number configuration. Please check WhatsApp settings.'
                ], 500);
            }

            $date = Carbon::parse($validated['date']);
            $fromTime = Carbon::parse($validated['from_time']);
            $toTime = Carbon::parse($validated['to_time']);

            // Query events for the date and time range
            $events = TimetableEvent::with(['timetable.student', 'teacher'])
                ->whereDate('event_date', $date->format('Y-m-d'))
                ->whereTime('start_time', '>=', $fromTime->format('H:i:s'))
                ->whereTime('start_time', '<=', $toTime->format('H:i:s'))
                ->orderBy('start_time')
                ->orderBy('teacher_id')
                ->get();

            if ($events->isEmpty()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'No events found for the selected date and time range'
                ], 404);
            }

            // Group events by time (30-minute intervals) and then by teacher
            $groupedEvents = [];
            foreach ($events as $event) {
                $time = Carbon::parse($event->start_time);
                // Round to nearest 30 minutes
                $minutes = $time->minute;
                $roundedMinutes = round($minutes / 30) * 30;
                $time->minute($roundedMinutes);
                $time->second(0);
                
                $timeKey = $time->format('H:i');
                $teacherName = $event->teacher->name ?? 'Unknown Teacher';
                $studentName = $event->timetable->student->name ?? 'Unknown Student';
                
                if (!isset($groupedEvents[$timeKey])) {
                    $groupedEvents[$timeKey] = [];
                }
                
                if (!isset($groupedEvents[$timeKey][$teacherName])) {
                    $groupedEvents[$timeKey][$teacherName] = [];
                }
                
                $groupedEvents[$timeKey][$teacherName][] = $studentName;
            }

            // Format message with modern and professional design
            $formattedDate = $date->format('l, F j, Y'); // e.g., "Monday, December 1, 2025"
            
            // Header
            $message = "📅 *Daily Schedule Report*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📆 *Date:* {$formattedDate}\n";
            $message .= "⏰ *Time Range:* " . $fromTime->format('g:i A') . " - " . $toTime->format('g:i A') . "\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            
            $timeSlots = array_keys($groupedEvents);
            sort($timeSlots);
            
            $totalEvents = $events->count();
            $message .= "📋 *Total Sessions: {$totalEvents}*\n\n";

            foreach ($timeSlots as $index => $timeSlot) {
                // Format time in 12-hour format with AM/PM
                $timeParts = explode(':', $timeSlot);
                $hour = (int)$timeParts[0];
                $minute = $timeParts[1];
                $timeCarbon = Carbon::createFromTime($hour, $minute);
                $formattedTime = $timeCarbon->format('g:i A'); // e.g., "9:00 AM"
                
                // Time slot header
                $message .= "🕐 *{$formattedTime}*\n";
                $message .= "─────────────────────\n";
                
                $teachers = $groupedEvents[$timeSlot];
                $teacherNames = array_keys($teachers);
                
                foreach ($teacherNames as $teacherIndex => $teacherName) {
                    // Teacher section
                    $message .= "\n👨‍🏫 *{$teacherName}*\n";
                    
                    $students = $teachers[$teacherName];
                    
                    // Students list
                    if (count($students) === 1) {
                        $message .= "   👤 {$students[0]}\n";
                    } else {
                        $message .= "   👥 " . implode("\n   👥 ", $students) . "\n";
                    }
                }
                
                // Add separator between time groups (except for last one)
                if ($index < count($timeSlots) - 1) {
                    $message .= "\n━━━━━━━━━━━━━━━━━━━━\n\n";
                }
            }
            
            // Footer
            $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "✅ *Report Generated Successfully*\n";
            $message .= "📱 Almajd Academy";

            // Send via WhatsApp
            $result = $whatsAppService->sendMessage($phone, $message);

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'WhatsApp reminder sent successfully!',
                    'events_count' => $events->count()
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send WhatsApp message'
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to send WhatsApp reminder: ' . $e->getMessage()
            ], 500);
        }
    }
}

