<?php

namespace App\Notifications;

use App\Models\OcrMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OcrMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param OcrMatch $match
     * @param string $type One of: invite, accepted, rejected, result_submitted, confirmed, disputed
     */
    public function __construct(
        public OcrMatch $match,
        public string $type
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            'invite' => 'You have a new OCR match invitation',
            'accepted' => 'Your OCR match invitation was accepted',
            'rejected' => 'Your OCR match invitation was rejected',
            'result_submitted' => 'OCR match result submitted - please confirm',
            'confirmed' => 'OCR match confirmed - Elo updated',
            'disputed' => 'OCR match result disputed',
            default => 'OCR Match Update',
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->getMessageBody());

        if (in_array($this->type, ['invite', 'result_submitted', 'disputed'])) {
            $message->action('View Match', url('/ocr/matches/' . $this->match->id));
        }

        return $message->line('Thank you for playing!');
    }

    /**
     * Get the message body based on notification type.
     */
    private function getMessageBody(): string
    {
        $challenger = $this->match->challenger?->name ?? 'Unknown';
        $opponent = $this->match->opponent?->name ?? 'Unknown';

        return match ($this->type) {
            'invite' => "{$challenger} has challenged you to a match!",
            'accepted' => "{$opponent} accepted your match invitation.",
            'rejected' => "{$opponent} rejected your match invitation.",
            'result_submitted' => "A result has been submitted for your match. Please confirm or dispute within 24 hours.",
            'confirmed' => "Match confirmed! Your Elo rating has been updated.",
            'disputed' => "The match result has been disputed and is under admin review.",
            default => "Your match has been updated.",
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'type' => $this->type,
            'challenger_id' => $this->match->challenger_id,
            'challenger_name' => $this->match->challenger?->name,
            'opponent_id' => $this->match->opponent_id,
            'opponent_name' => $this->match->opponent?->name,
            'status' => $this->match->status,
        ];
    }
}
