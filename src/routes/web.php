<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline');
  
    Route::get('/users/user/{user}', [UserController::class, 'user'])->name('users.user');
    Route::get('/users/users', [UserController::class, 'users'])->name('users.users');
    Route::get('/users/followers/{user}', [UserController::class, 'followers'])->name('users.followers');
    Route::get('/users/followees/{user}', [UserController::class, 'followees'])->name('users.followees');

    Route::post('/likes/like/{note}', [LikeController::class, 'like'])->name('likes.like');
    Route::delete('/likes/unlike/{note}', [LikeController::class, 'unlike'])->name('likes.unlike');

    Route::post('/follows/follow/{user}', [FollowController::class, 'follow'])->name('follows.follow');
    Route::delete('/follows/unfollow/{user}', [FollowController::class, 'unfollow'])->name('follows.unfollow');
    
    Route::resource('/notes', NoteController::class)->except(['create', 'edit', 'show']);
    Route::get('/notes/note/{note}', [NoteController::class, 'note'])->name('notes.note');
    Route::get('/notes/posts', [NoteController::class, 'posts'])->name('notes.posts');
    Route::put('/notes/archive/{note}', [NoteController::class, 'archive'])->name('notes.archive');
    Route::put('/notes/retrieve/{note}', [NoteController::class, 'retrieve'])->name('notes.retrieve');
    Route::get('/notes/schedule', [NoteController::class, 'schedule'])->name('notes.schedule');
    Route::get('/notes/likes/{note}', [NoteController::class, 'likes'])->name('notes.likes');

    Route::resource('/tags', TagController::class)->except(['create', 'edit', 'show']);
    Route::get('/tags/items/select', [TagController::class, 'selectItems'])->name('tags.items.select');
    Route::get('/tags/items/datatable', [TagController::class, 'datatableItems'])->name('tags.items.datatable');

    Route::get('/comments/{note}', [CommentController::class, 'index'])->name('comments');
    Route::get('/comments/comment/{comment}', [CommentController::class, 'comment'])->name('comments.comment');
    Route::post('/comments/{note}', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    Route::get('/replies/{comment}', [ReplyController::class, 'index'])->name('replies');
    Route::post('/replies/{comment}', [ReplyController::class, 'store'])->name('replies.store');
    Route::delete('/replies/{reply}', [ReplyController::class, 'destroy'])->name('replies.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::put('/notifications/read/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
    Route::patch('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/image', [ProfileController::class, 'upload'])->name('profile.upload');
    Route::delete('/profile/image', [ProfileController::class, 'deleteImage'])->name('profile.delete.image');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
