<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ReplyNotification extends Notification
{
    use Queueable;
    protected $commenter;
    protected $comment;
    protected $reply;

    /**
     * Create a new notification instance.
     */
    public function __construct($commenter, $comment, $reply)
    {
        $this->commenter = $commenter;
        $this->comment = $comment;
        $this->reply = $reply;
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
            return $item->type == 'reply';
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
        $url = config('app.url') . '/timeline?note=' . $this->comment->note_id;

        $message = (new MailMessage)
            ->subject(Lang::get($this->commenter->name . ' replied to your comment'))
            ->line(Lang::get($this->commenter->name . ' replied to your comment.'))
            ->line(Lang::get("\"{$this->reply->content}\""))
            ->line(Lang::get("Please click the button below to check the reply to your comment."))
            ->action(Lang::get('Check Reply'), $url);

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
            'type' => 'reply',
            'comment' => $this->comment
        ];
    }
}
