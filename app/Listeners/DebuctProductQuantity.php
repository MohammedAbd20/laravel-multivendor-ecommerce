<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DebuctProductQuantity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    // public function handle(OrderCreated $event): void
    // {
    //     $order = $event->order;
    //     // dd($event->order);
    //     foreach ($order as $products => $product) {
    //         $product->decrement('quantity',$product->order_item->quantity);
    //     }
    // }


    public function handle(OrderCreated $event)
{
    $order = $event->order;
    // الوصول إلى المنتجات من خلال العلاقة
    foreach ($order->products as $product) {
        // التأكد من أن المنتج و order_item موجودان
        if ($product && $product->order_item) {
            // تقليل الكمية المتاحة في المخزون بالكمية المطلوبة
            $product->decrement('quantity', $product->order_item->quantity);
        } else {
            // التعامل مع حالة عدم وجود المنتج أو order_item
            return response()->json(['error' => 'Product or order item not found'], 404);
        }
    }
}

}
