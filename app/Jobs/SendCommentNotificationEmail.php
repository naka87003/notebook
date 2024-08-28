<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\CommentNotification;

class SendCommentNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $commenter;
    protected $comment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $commenter, $comment)
    {
        $this->user = $user;
        $this->commenter = $commenter;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new CommentNotification($this->commenter, $this->comment));
    }
}