<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MoodEntryRequest;
use App\Models\MoodEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MoodController extends BaseApiController
{
    use AuthorizesRequests;
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

        // Weekly averages for chart
        $weeklyAverages = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            $weekEntries = $entries->filter(fn($e) => $e->recorded_at->between($weekStart, $weekEnd));
            $weeklyAverages[] = round($weekEntries->avg('intensity') ?? 0, 1);
        }
        $stats['weekly_averages'] = $weeklyAverages;

        // Calculate average mood (intensity)
        $stats['average_mood'] = round($entries->avg('intensity'), 1);

        return $this->success($stats);
    }

    public function overview(Request $request): JsonResponse
    {
        $entries = auth()->user()->moodEntries()->orderBy('recorded_at', 'desc')->get();

        // Calculate streak
        $streak = 0;
        $currentDate = now()->startOfDay();
        $dates = $entries->pluck('recorded_at')->map(fn($d) => Carbon::parse($d)->startOfDay()->toDateString())->unique()->sort()->reverse()->values();
        
        foreach ($dates as $date) {
            $dateCarbon = Carbon::parse($date);
            if ($dateCarbon->isSameDay($currentDate) || $dateCarbon->isSameDay($currentDate->subDay())) {
                $streak++;
                $currentDate = $dateCarbon->copy()->subDay();
            } else {
                break;
            }
        }

        // Calculate mood change (last entry vs average)
        $lastEntry = $entries->first();
        $avgMood = round($entries->avg('intensity'), 1);
        $moodChange = $lastEntry ? round($lastEntry->intensity - $avgMood, 1) : 0;

        return $this->success([
            'current_streak' => $streak,
            'total_entries' => $entries->count(),
            'average_mood' => $avgMood,
            'mood_change' => $moodChange,
        ]);
    }

    public function weekly(Request $request): JsonResponse
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $entries = auth()->user()->moodEntries()
            ->whereBetween('recorded_at', [$startOfWeek, $endOfWeek])
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Group by day
        $weeklyData = [];
        $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayEntries = $entries->filter(fn($e) => Carbon::parse($e->recorded_at)->isSameDay($dayDate));
            
            $weeklyData[] = [
                'day' => $daysOfWeek[$i],
                'date' => $dayDate->toDateString(),
                'average_mood' => round($dayEntries->avg('intensity') ?? 0, 1),
                'count' => $dayEntries->count(),
            ];
        }

        return $this->success($weeklyData);
    }
}
