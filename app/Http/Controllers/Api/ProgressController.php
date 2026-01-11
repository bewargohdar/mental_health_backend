<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\MoodEntry;
use App\Models\Post;
use App\Models\ExerciseCompletion;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProgressController extends BaseApiController
{
    /**
     * Get user's weekly activity summary
     */
    public function weekly(): JsonResponse
    {
        $user = auth()->user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $moodEntries = MoodEntry::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        // Build chart data array
        $data = [];
        $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayEntries = $moodEntries->filter(fn($e) => Carbon::parse($e->created_at)->isSameDay($dayDate));
            
            $data[] = [
                'day' => $daysOfWeek[$i],
                'mood' => round($dayEntries->avg('intensity') ?? 0, 1),
                'entries' => $dayEntries->count(),
            ];
        }

        return $this->success($data, 'Weekly progress retrieved');
    }

    public function weeklyChart(): JsonResponse
    {
        $user = auth()->user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $moodEntries = MoodEntry::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $startOfWeek->copy()->addDays(6)])
            ->get();

        $data = [];
        $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayEntries = $moodEntries->filter(fn($e) => Carbon::parse($e->created_at)->isSameDay($dayDate));
            $data[] = [
                'day' => $daysOfWeek[$i],
                'mood' => round($dayEntries->avg('intensity') ?? 0, 1),
                'entries' => $dayEntries->count(),
            ];
        }

        return $this->success($data, 'Weekly chart data retrieved');
    }

    /**
     * Get overall user progress overview
     */
    public function overview(): JsonResponse
    {
        $user = auth()->user();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Mood trends (last 30 days)
        $moodTrend = MoodEntry::where('user_id', $user->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                'mood_type',
                'intensity'
            )
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Total stats
        $totalMoodEntries = MoodEntry::where('user_id', $user->id)->count();
        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalExercises = ExerciseCompletion::where('user_id', $user->id)->count();
        $totalAppointments = Appointment::where('patient_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Most frequent moods
        $frequentMoods = MoodEntry::where('user_id', $user->id)
            ->select('mood_type', DB::raw('count(*) as count'))
            ->groupBy('mood_type')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Average mood intensity
        $avgIntensity = MoodEntry::where('user_id', $user->id)
            ->avg('intensity');

        return $this->success([
            'total_mood_entries' => $totalMoodEntries,
            'total_posts' => $totalPosts,
            'total_exercises_completed' => $totalExercises,
            'total_appointments_completed' => $totalAppointments,
            'mood_trend' => $moodTrend,
            'frequent_moods' => $frequentMoods,
            'average_mood_intensity' => round($avgIntensity ?? 0, 1),
            'mood_streak' => $this->calculateMoodStreak($user->id),
            'current_streak' => $this->calculateMoodStreak($user->id),
            'total_entries' => $totalMoodEntries,
            'average_mood' => round($avgIntensity ?? 0, 1),
            'mood_change' => 0,
        ], 'Progress overview retrieved');
    }

    /**
     * Calculate consecutive days with mood entries
     */
    private function calculateMoodStreak(int $userId): int
    {
        $dates = MoodEntry::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->orderByDesc('date')
            ->pluck('date')
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::today();
        $checkDate = $today;

        // Check if today or yesterday has an entry (allow for current day)
        $firstDate = Carbon::parse($dates[0]);
        if ($firstDate->lt($today->copy()->subDay())) {
            return 0; // Streak is broken
        }

        foreach ($dates as $date) {
            $entryDate = Carbon::parse($date);
            
            if ($entryDate->isSameDay($checkDate) || $entryDate->isSameDay($checkDate->copy()->subDay())) {
                $streak++;
                $checkDate = $entryDate->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
