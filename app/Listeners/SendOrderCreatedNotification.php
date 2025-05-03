<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        // dd($order->store_id);
        $user = User::where('store_id', $order->store_id)->first();
        // dd(User::where('store_id', $order->store_id)->first());
        if($user){
            $user->notify(new OrderCreatedNotification($order));
        }

        // $users = User::where('store_id','=',$order->store_id)->get();
        // Notification::send($users,new OrderCreatedNotification($order));
    }
}
