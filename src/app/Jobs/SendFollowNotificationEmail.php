<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\FollowNotification;

class SendFollowNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $follower;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $follower)
    {
        $this->user = $user;
        $this->follower = $follower;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new FollowNotification($this->follower));
    }
}
