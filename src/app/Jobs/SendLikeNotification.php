<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\LikeNotification;

class SendLikeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $likedBy;
    protected $noteId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $likedBy, $noteId)
    {
        $this->user = $user;
        $this->likedBy = $likedBy;
        $this->noteId = $noteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new LikeNotification($this->likedBy, $this->noteId));
    }
}
