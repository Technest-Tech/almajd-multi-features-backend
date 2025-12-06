<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Http\Requests\UpdateTimetableEventRequest;
use App\Models\Timetable;
use App\Models\TimetableEvent;
use App\Services\TimetableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimetableController extends Controller
{
    public function __construct(
        private TimetableService $timetableService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['student_id', 'teacher_id', 'from_date', 'to_date', 'per_page']);
        $timetables = $this->timetableService->getAll($filters);

        return response()->json($timetables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimetableRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $timetable = $this->timetableService->create($data);
            return response()->json($timetable, 201);
        } catch (\Exception $e) {
            Log::error('Error creating timetable: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create timetable'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $timetable = $this->timetableService->getById((int) $id);

        if (!$timetable) {
            return response()->json(['error' => 'Timetable not found'], 404);
        }

        return response()->json($timetable);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimetableRequest $request, string $id): JsonResponse
    {
        $timetable = $this->timetableService->getById((int) $id);

        if (!$timetable) {
            return response()->json(['error' => 'Timetable not found'], 404);
        }

        try {
            $timetable = $this->timetableService->update($timetable, $request->validated());
            return response()->json($timetable);
        } catch (\Exception $e) {
            Log::error('Error updating timetable: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update timetable'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $timetable = $this->timetableService->getById((int) $id);

        if (!$timetable) {
            return response()->json(['error' => 'Timetable not found'], 404);
        }

        $this->timetableService->deleteAll($timetable);

        return response()->json(['message' => 'Timetable and all events deleted successfully']);
    }

    /**
     * Get calendar events with filters
     */
    public function getEvents(Request $request): JsonResponse
    {
        $filters = $request->only(['student_id', 'teacher_id', 'from_date', 'to_date', 'date']);
        $events = $this->timetableService->getEvents($filters);

        return response()->json($events);
    }

    /**
     * Update a single timetable event
     */
    public function updateEvent(UpdateTimetableEventRequest $request, string $id): JsonResponse
    {
        $event = $this->timetableService->getEventById((int) $id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        try {
            $event = $this->timetableService->updateEvent($event, $request->validated());
            return response()->json($event);
        } catch (\Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update event'], 500);
        }
    }

    /**
     * Delete a single timetable event
     */
    public function deleteEvent(string $id): JsonResponse
    {
        $event = $this->timetableService->getEventById((int) $id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $this->timetableService->deleteEvent($event);

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * Export timetable events to PDF
     */
    public function exportPdf(Request $request): JsonResponse
    {
        // This will be handled by the Flutter app, but we can return the filtered events
        $filters = $request->only(['student_id', 'teacher_id', 'from_date', 'to_date']);
        $events = $this->timetableService->getEvents($filters);

        return response()->json([
            'events' => $events,
            'filters' => $filters,
        ]);
    }
}
