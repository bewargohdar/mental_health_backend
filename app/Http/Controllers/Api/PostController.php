<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends BaseApiController
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        $query = Post::approved()
            ->with(['user:id,name,avatar'])
            ->withCount(['comments', 'likes'])
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $posts = $query->paginate($request->per_page ?? 15);

        // Hide user info for anonymous posts
        $posts->getCollection()->transform(function ($post) {
            if ($post->is_anonymous) {
                $post->user = null;
            }
            return $post;
        });

        return $this->success($posts);
    }

    public function store(PostRequest $request): JsonResponse
    {
        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'is_anonymous' => $request->is_anonymous ?? false,
            'is_approved' => false, // Requires admin approval
        ]);

        return $this->created($post, 'Post submitted successfully. It will be visible after admin approval.');
    }

    public function show(Post $post): JsonResponse
    {
        if (!$post->is_approved && $post->user_id !== auth()->id()) {
            return $this->error('Post not found', 404);
        }

        $post->load(['comments' => function ($query) {
            $query->approved()->topLevel()->with('replies', 'user:id,name,avatar');
        }]);
        $post->loadCount(['comments', 'likes']);

        // Check if current user liked the post
        $post->is_liked = $post->likes()->where('user_id', auth()->id())->exists();

        if ($post->is_anonymous) {
            $post->user = null;
        }

        return $this->success($post);
    }

    public function update(PostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        // Reset approval status when post content is modified
        $needsReapproval = $request->title !== $post->title || $request->content !== $post->content;

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'is_anonymous' => $request->is_anonymous ?? $post->is_anonymous,
            'is_approved' => $needsReapproval ? false : $post->is_approved,
            'approved_at' => $needsReapproval ? null : $post->approved_at,
            'approved_by' => $needsReapproval ? null : $post->approved_by,
        ]);

        $message = $needsReapproval 
            ? 'Post updated and submitted for re-approval.'
            : 'Post updated successfully.';

        return $this->success($post, $message);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return $this->success(null, 'Post deleted successfully.');
    }

    public function like(Post $post): JsonResponse
    {
        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            return $this->success(['liked' => false], 'Post unliked.');
        }

        $post->likes()->create(['user_id' => auth()->id()]);
        $post->increment('likes_count');

        return $this->success(['liked' => true], 'Post liked.');
    }

    public function myPosts(Request $request): JsonResponse
    {
        $posts = auth()->user()->posts()
            ->withCount(['comments', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($posts);
    }
}
