<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CommentNotification extends Notification
{
    use Queueable;
    protected $commenter;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($commenter, $comment)
    {
        $this->commenter = $commenter;
        $this->comment = $comment;
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
            return $item->type == 'comment';
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
        $url = config('app.url') . '/?note=' . $this->comment->note_id;

        $message = (new MailMessage)
            ->subject(Lang::get('You have a new comment'))
            ->line(Lang::get($this->commenter->name . ' commented on your note.'))
            ->line(Lang::get("\"{$this->comment->content}\""))
            ->line(Lang::get("Please click the button below to check comments on your note."))
            ->action(Lang::get('Check Comments'), $url);

        $message->viewData['data'] = [
            'image_path' => $this->commenter->image_path
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
            'user' => $this->commenter,
            'type' => 'comment',
            'comment' => $this->comment
        ];
    }
}
