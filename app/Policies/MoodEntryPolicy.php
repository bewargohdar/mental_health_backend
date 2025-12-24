<?php

namespace App\Policies;

use App\Models\MoodEntry;
use App\Models\User;

class MoodEntryPolicy
{
    public function view(User $user, MoodEntry $moodEntry): bool
    {
        return $user->id === $moodEntry->user_id || $user->isAdmin();
    }

    public function update(User $user, MoodEntry $moodEntry): bool
    {
        return $user->id === $moodEntry->user_id;
    }

    public function delete(User $user, MoodEntry $moodEntry): bool
    {
        return $user->id === $moodEntry->user_id || $user->isAdmin();
    }
}
