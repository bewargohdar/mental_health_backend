<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MoodEntryRequest;
use App\Models\MoodEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MoodController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->moodEntries()
            ->orderBy('recorded_at', 'desc');

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('recorded_at', '>=', Carbon::parse($request->start_date));
        }
        if ($request->has('end_date')) {
            $query->where('recorded_at', '<=', Carbon::parse($request->end_date));
        }

        // Filter by mood type
        if ($request->has('mood_type')) {
            $query->where('mood_type', $request->mood_type);
        }

        $entries = $query->paginate($request->per_page ?? 15);

        return $this->success($entries);
    }

    public function store(MoodEntryRequest $request): JsonResponse
    {
        $entry = auth()->user()->moodEntries()->create([
            'mood_type' => $request->mood_type,
            'intensity' => $request->intensity,
            'notes' => $request->notes,
            'factors' => $request->factors,
            'activities' => $request->activities,
            'sleep_hours' => $request->sleep_hours,
            'is_private' => $request->is_private ?? true,
            'recorded_at' => $request->recorded_at ?? now(),
        ]);

        return $this->created($entry, 'Mood entry recorded successfully.');
    }

    public function show(MoodEntry $moodEntry): JsonResponse
    {
        $this->authorize('view', $moodEntry);
        
        return $this->success($moodEntry);
    }

    public function update(MoodEntryRequest $request, MoodEntry $moodEntry): JsonResponse
    {
        $this->authorize('update', $moodEntry);

        $moodEntry->update($request->validated());

        return $this->success($moodEntry, 'Mood entry updated successfully.');
    }

    public function destroy(MoodEntry $moodEntry): JsonResponse
    {
        $this->authorize('delete', $moodEntry);

        $moodEntry->delete();

        return $this->success(null, 'Mood entry deleted successfully.');
    }

    public function statistics(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date ?? now()->subDays(30));
        $endDate = Carbon::parse($request->end_date ?? now());

        $entries = auth()->user()->moodEntries()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get();

        $stats = [
            'total_entries' => $entries->count(),
            'mood_distribution' => $entries->groupBy('mood_type')->map->count(),
            'average_intensity' => round($entries->avg('intensity'), 1),
            'average_sleep' => round($entries->avg('sleep_hours'), 1),
            'most_common_mood' => $entries->groupBy('mood_type')->sortByDesc(fn($g) => $g->count())->keys()->first(),
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];

        // Weekly averages
        $stats['weekly_trends'] = $entries->groupBy(fn($e) => $e->recorded_at->startOfWeek()->toDateString())
            ->map(fn($week) => [
                'count' => $week->count(),
                'avg_intensity' => round($week->avg('intensity'), 1),
            ]);

        return $this->success($stats);
    }
}
