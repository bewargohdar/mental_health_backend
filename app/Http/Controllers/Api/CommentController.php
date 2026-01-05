<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends BaseApiController
{
    use AuthorizesRequests;
    public function store(CommentRequest $request): JsonResponse
    {
        $commentableType = $request->commentable_type;
        $commentableId = $request->commentable_id;

        // Find the commentable model
        $commentable = match ($commentableType) {
            'post' => Post::findOrFail($commentableId),
            'article' => Article::findOrFail($commentableId),
            default => abort(422, 'Invalid commentable type'),
        };

        $comment = $commentable->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_anonymous' => $request->is_anonymous ?? false,
            'parent_id' => $request->parent_id,
            'is_approved' => true, // Auto-approve for now
        ]);

        // Update comment count
        if ($commentableType === 'post') {
            $commentable->increment('comments_count');
        }

        $comment->load('user:id,name,avatar');

        return $this->created($comment, 'Comment added successfully.');
    }

    public function update(CommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->content,
            'is_anonymous' => $request->is_anonymous ?? $comment->is_anonymous,
        ]);

        return $this->success($comment, 'Comment updated successfully.');
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        // Update parent count
        if ($comment->commentable_type === Post::class) {
            $comment->commentable->decrement('comments_count');
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted successfully.');
    }

    public function replies(Comment $comment): JsonResponse
    {
        $replies = $comment->replies()
            ->with('user:id,name,avatar')
            ->approved()
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->success($replies);
    }
}
