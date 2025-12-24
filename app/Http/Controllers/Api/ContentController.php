<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Video;
use App\Models\Exercise;
use App\Models\ExerciseCompletion;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends BaseApiController
{
    // Articles
    public function articles(Request $request): JsonResponse
    {
        $query = Article::published()
            ->with('author:id,name');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $articles = $query->orderBy('published_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($articles);
    }

    public function article(Article $article): JsonResponse
    {
        if (!$article->is_published) {
            return $this->error('Article not found', 404);
        }

        $article->increment('views_count');
        $article->load(['author:id,name', 'comments' => function ($q) {
            $q->approved()->with('user:id,name,avatar');
        }]);

        // Check bookmark status
        $article->is_bookmarked = auth()->user()
            ->bookmarks()
            ->where('bookmarkable_type', Article::class)
            ->where('bookmarkable_id', $article->id)
            ->exists();

        return $this->success($article);
    }

    // Videos
    public function videos(Request $request): JsonResponse
    {
        $query = Video::published()
            ->with('author:id,name');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $videos = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($videos);
    }

    public function video(Video $video): JsonResponse
    {
        if (!$video->is_published) {
            return $this->error('Video not found', 404);
        }

        $video->increment('views_count');
        $video->load('author:id,name');

        $video->is_bookmarked = auth()->user()
            ->bookmarks()
            ->where('bookmarkable_type', Video::class)
            ->where('bookmarkable_id', $video->id)
            ->exists();

        return $this->success($video);
    }

    // Exercises
    public function exercises(Request $request): JsonResponse
    {
        $query = Exercise::published()
            ->with('author:id,name');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $exercises = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($exercises);
    }

    public function exercise(Exercise $exercise): JsonResponse
    {
        if (!$exercise->is_published) {
            return $this->error('Exercise not found', 404);
        }

        $exercise->load('author:id,name');

        // Check if user completed this exercise
        $exercise->is_completed = auth()->user()
            ? ExerciseCompletion::where('user_id', auth()->id())
                ->where('exercise_id', $exercise->id)
                ->exists()
            : false;

        $exercise->is_bookmarked = auth()->user()
            ->bookmarks()
            ->where('bookmarkable_type', Exercise::class)
            ->where('bookmarkable_id', $exercise->id)
            ->exists();

        return $this->success($exercise);
    }

    public function completeExercise(Request $request, Exercise $exercise): JsonResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $completion = ExerciseCompletion::create([
            'user_id' => auth()->id(),
            'exercise_id' => $exercise->id,
            'completed_at' => now(),
            'notes' => $request->notes,
            'rating' => $request->rating,
        ]);

        $exercise->increment('completions_count');

        return $this->created($completion, 'Exercise completed!');
    }

    // Bookmarks
    public function bookmark(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:article,video,exercise'],
            'id' => ['required', 'integer'],
        ]);

        $type = match ($request->type) {
            'article' => Article::class,
            'video' => Video::class,
            'exercise' => Exercise::class,
        };

        $model = $type::findOrFail($request->id);

        $existing = Bookmark::where('user_id', auth()->id())
            ->where('bookmarkable_type', $type)
            ->where('bookmarkable_id', $request->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return $this->success(['bookmarked' => false], 'Bookmark removed.');
        }

        Bookmark::create([
            'user_id' => auth()->id(),
            'bookmarkable_type' => $type,
            'bookmarkable_id' => $request->id,
        ]);

        return $this->success(['bookmarked' => true], 'Bookmarked successfully.');
    }

    public function bookmarks(Request $request): JsonResponse
    {
        $query = auth()->user()->bookmarks()->with('bookmarkable');

        if ($request->has('type')) {
            $type = match ($request->type) {
                'article' => Article::class,
                'video' => Video::class,
                'exercise' => Exercise::class,
                default => null,
            };
            if ($type) {
                $query->where('bookmarkable_type', $type);
            }
        }

        $bookmarks = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($bookmarks);
    }
}
