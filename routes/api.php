<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\MoodController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\WellnessTipController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('register/doctor', [RegisterController::class, 'registerDoctor']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

// Public content routes
Route::prefix('content')->group(function () {
    Route::get('articles', [ContentController::class, 'articles']);
    Route::get('articles/{article}', [ContentController::class, 'article']);
    Route::get('videos', [ContentController::class, 'videos']);
    Route::get('videos/{video}', [ContentController::class, 'video']);
    Route::get('exercises', [ContentController::class, 'exercises']);
    Route::get('exercises/{exercise}', [ContentController::class, 'exercise']);
});

// Public posts (community)
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);

// Public wellness tips
Route::prefix('wellness-tips')->group(function () {
    Route::get('/', [WellnessTipController::class, 'index']);
    Route::get('random', [WellnessTipController::class, 'random']);
    Route::get('category/{category}', [WellnessTipController::class, 'byCategory']);
});

// Public doctors listing
Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('specializations', [DoctorController::class, 'specializations']);
    Route::get('{doctor}', [DoctorController::class, 'show']);
    Route::get('{doctor}/slots', [AppointmentController::class, 'availableSlots']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('logout-all', [LoginController::class, 'logoutAll']);
        Route::get('me', [LoginController::class, 'me']);
        Route::get('email/verify', [EmailVerificationController::class, 'notice']);
        Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1');
    });

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('avatar', [ProfileController::class, 'deleteAvatar']);
        Route::post('password', [ProfileController::class, 'changePassword']);
        Route::post('language/{language}', [ProfileController::class, 'updateLanguage']);
    });

    // User Progress (for Home page)
    Route::prefix('progress')->group(function () {
        Route::get('weekly', [ProgressController::class, 'weekly']);
        Route::get('overview', [ProgressController::class, 'overview']);
    });

    // Mood Tracking
    Route::prefix('moods')->group(function () {
        Route::get('/', [MoodController::class, 'index']);
        Route::post('/', [MoodController::class, 'store']);
        Route::get('statistics', [MoodController::class, 'statistics']);
        Route::get('{moodEntry}', [MoodController::class, 'show']);
        Route::put('{moodEntry}', [MoodController::class, 'update']);
        Route::delete('{moodEntry}', [MoodController::class, 'destroy']);
    });

    // Posts (Community)
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::get('my-posts', [PostController::class, 'myPosts']);
        Route::put('{post}', [PostController::class, 'update']);
        Route::delete('{post}', [PostController::class, 'destroy']);
        Route::post('{post}/like', [PostController::class, 'like']);
    });

    // Comments
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::put('{comment}', [CommentController::class, 'update']);
        Route::delete('{comment}', [CommentController::class, 'destroy']);
        Route::get('{comment}/replies', [CommentController::class, 'replies']);
    });

    // Appointments
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('{appointment}', [AppointmentController::class, 'show']);
        Route::post('{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::post('{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::post('{appointment}/complete', [AppointmentController::class, 'complete']);
    });

    // Doctor availability (protected - for booking)
    Route::get('doctors/{doctor}/availability', [AppointmentController::class, 'doctorAvailability']);
    Route::get('doctors/{doctor}/slots', [AppointmentController::class, 'availableSlots']);

    // Content interactions (protected)
    Route::post('content/bookmark', [ContentController::class, 'bookmark']);
    Route::get('content/bookmarks', [ContentController::class, 'bookmarks']);
    Route::post('exercises/{exercise}/complete', [ContentController::class, 'completeExercise']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clear']);
    });
});

