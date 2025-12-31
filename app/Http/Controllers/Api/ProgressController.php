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

        // Mood entries this week
        $moodEntries = MoodEntry::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        // Posts this week
        $postsCount = Post::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Exercises completed this week
        $exercisesCompleted = ExerciseCompletion::where('user_id', $user->id)
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Appointments this week
        $appointmentsCount = Appointment::where('patient_id', $user->id)
            ->whereBetween('scheduled_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Daily mood breakdown
        $dailyMoods = $moodEntries->groupBy(function ($entry) {
            return $entry->created_at->format('l'); // Day name
        })->map(function ($entries) {
            return [
                'count' => $entries->count(),
                'moods' => $entries->pluck('mood_type')->unique()->values(),
            ];
        });

        // Streak calculation (consecutive days with mood entry)
        $streak = $this->calculateMoodStreak($user->id);

        return $this->success([
            'week_start' => $startOfWeek->toDateString(),
            'week_end' => $endOfWeek->toDateString(),
            'mood_entries_count' => $moodEntries->count(),
            'posts_count' => $postsCount,
            'exercises_completed' => $exercisesCompleted,
            'appointments_count' => $appointmentsCount,
            'daily_moods' => $dailyMoods,
            'mood_streak' => $streak,
        ], 'Weekly progress retrieved');
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
