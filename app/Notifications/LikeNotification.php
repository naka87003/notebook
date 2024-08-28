<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class LikeNotification extends Notification
{
    use Queueable;
    protected $likedBy;
    protected $noteId;

    /**
     * Create a new notification instance.
     */
    public function __construct($likedBy, $noteId)
    {
        $this->likedBy = $likedBy;
        $this->noteId = $noteId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = ['database'];
        $preference = $notifiable->emailPreferences->filter(function ($item) {
            return $item->type == 'like';
        })->first();

        if ($preference !== null && $preference->value) {
            array_push($via, 'mail');
        }
        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.url') . '/?note=' . $this->noteId;

        $message = (new MailMessage)
            ->subject(Lang::get($this->likedBy->name . ' liked your note'))
            ->line(Lang::get($this->likedBy->name . ' liked your note.'))
            ->line(Lang::get("Please click the button below to check likes on your note."))
            ->action(Lang::get('Check Likes'), $url);

        $message->viewData['data'] = [
            'image_path' => $this->likedBy->image_path
        ];

        return $message;
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user' => $this->likedBy,
            'type' => 'like',
            'note_id' => $this->noteId
        ];
    }
}
