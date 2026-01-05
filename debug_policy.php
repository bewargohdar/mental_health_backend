<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

$user = User::first();
if (!$user) {
    echo "No users found.\n";
    exit;
}

$post = Post::where('user_id', $user->id)->first();
if (!$post) {
    // Create a dummy post for the user
    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Test Post',
        'content' => 'Content',
        'category' => 'Anxiety',
        'is_anonymous' => false,
        'is_approved' => true
    ]);
}

echo "User ID: " . $user->id . "\n";
echo "Post User ID: " . $post->user_id . "\n";

try {
    $result = Gate::forUser($user)->authorize('update', $post);
    echo "Authorization successful.\n";
} catch (\Exception $e) {
    echo "Authorization failed: " . $e->getMessage() . "\n";
}

// Check if policy is registered
$policy = Gate::getPolicyFor(Post::class);
echo "Policy for Post: " . ($policy ? get_class($policy) : 'None') . "\n";
