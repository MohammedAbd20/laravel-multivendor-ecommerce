<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    protected $order;
    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];

        $channel = ['database'];
        if ($notifiable->notification_preferences['order_created']['sms'] ?? false) {
            $channel[] = 'vonage';
        }
        if ($notifiable->notification_preferences['order_created']['mail'] ?? false) {
            $channel[] = 'mail';
        }
        if ($notifiable->notification_preferences['order_created']['broadcast'] ?? false) {
            $channel[] = 'broadcast';
        }

        return $channel;

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $addr = $this->order->billingAddresses;
        return (new MailMessage)
            ->subject("New order # {$this->order->number}")
            ->greeting("Hi {$notifiable->name}")
            ->line("A new order # {$this->order->number} has been created by {$addr->name} from {$addr->country_name}")
            ->action('View', url('/dashboard'))
            ->line('Thank you for using our application!');
    }

    public function toDatabase(object $notifiable)
    {
        $addr = $this->order->billingAddresses;
        return [
            'body' => "A new order # {$this->order->number} has been created by {$addr->name} from {$addr->country_name}",
            'icon' => "fas fa-file mr-2",
            'url' => url('/dashboard'),
            'order_id'=>$this->order->id,
        ];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
