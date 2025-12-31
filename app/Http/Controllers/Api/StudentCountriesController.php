<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarTeacherTimetable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCountriesController extends Controller
{
    /**
     * Add one hour to all timetables for a specific country
     */
    public function plus(Request $request, string $country): JsonResponse
    {
        try {
            // Normalize country code
            $country = strtolower($country);
            
            // Validate country
            $validCountries = ['canada', 'uk', 'eg'];
            if (!in_array($country, $validCountries)) {
                return response()->json([
                    'error' => 'Invalid country. Must be one of: canada, uk, eg'
                ], 400);
            }

            $timetables = CalendarTeacherTimetable::where('country', $country)
                ->where('status', 'active')
                ->get();

            if ($timetables->isEmpty()) {
                return response()->json([
                    'message' => 'No active timetables found for ' . $country,
                    'updated_count' => 0
                ]);
            }

            $updatedCount = 0;
            foreach ($timetables as $timetable) {
                // Parse times (they're stored as time strings like '14:30:00')
                $startTime = Carbon::createFromFormat('H:i:s', $timetable->start_time);
                $finishTime = Carbon::createFromFormat('H:i:s', $timetable->finish_time);

                // Add one hour
                $newStartTime = $startTime->copy()->addHour();
                $newFinishTime = $finishTime->copy()->addHour();

                // Check if day needs to change (crossed midnight)
                $day = $timetable->day;
                if ($newStartTime->format('H:i:s') < $startTime->format('H:i:s')) {
                    $day = $this->getNextDay($day);
                }

                // Update the timetable
                $timetable->update([
                    'start_time' => $newStartTime->format('H:i:s'),
                    'finish_time' => $newFinishTime->format('H:i:s'),
                    'day' => $day
                ]);
                
                $updatedCount++;
            }

            return response()->json([
                'message' => "1 Hour successfully added to {$country}",
                'updated_count' => $updatedCount,
                'country' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subtract one hour from all timetables for a specific country
     */
    public function minus(Request $request, string $country): JsonResponse
    {
        try {
            // Normalize country code
            $country = strtolower($country);
            
            // Validate country
            $validCountries = ['canada', 'uk', 'eg'];
            if (!in_array($country, $validCountries)) {
                return response()->json([
                    'error' => 'Invalid country. Must be one of: canada, uk, eg'
                ], 400);
            }

            $timetables = CalendarTeacherTimetable::where('country', $country)
                ->where('status', 'active')
                ->get();

            if ($timetables->isEmpty()) {
                return response()->json([
                    'message' => 'No active timetables found for ' . $country,
                    'updated_count' => 0
                ]);
            }

            $updatedCount = 0;
            foreach ($timetables as $timetable) {
                // Parse times
                $startTime = Carbon::createFromFormat('H:i:s', $timetable->start_time);
                $finishTime = Carbon::createFromFormat('H:i:s', $timetable->finish_time);

                // Subtract one hour
                $newStartTime = $startTime->copy()->subHour();
                $newFinishTime = $finishTime->copy()->subHour();

                // Check if day needs to change (crossed midnight backward)
                $day = $timetable->day;
                if ($newStartTime->format('H:i:s') > $startTime->format('H:i:s')) {
                    $day = $this->getPreviousDay($day);
                }

                // Update the timetable
                $timetable->update([
                    'start_time' => $newStartTime->format('H:i:s'),
                    'finish_time' => $newFinishTime->format('H:i:s'),
                    'day' => $day
                ]);
                
                $updatedCount++;
            }

            return response()->json([
                'message' => "1 Hour successfully subtracted from {$country}",
                'updated_count' => $updatedCount,
                'country' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getNextDay(string $day): string
    {
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $index = array_search($day, $daysOfWeek);
        $nextIndex = ($index + 1) % 7;
        return $daysOfWeek[$nextIndex];
    }

    private function getPreviousDay(string $day): string
    {
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $index = array_search($day, $daysOfWeek);
        $previousIndex = ($index - 1 + 7) % 7;
        return $daysOfWeek[$previousIndex];
    }
}


