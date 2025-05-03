<?php

namespace App\Observers;

use App\Models\Cart;
use Illuminate\Support\Str;

class CartObserve
{
    /**
     * Handle the cart "created" event.
     */
    public function creating(Cart $cart): void
    {
        $cart->id =  Str::uuid();
        $cart->cookie_id = $cart->getCookieId();
    }

    /**
     * Handle the cart "updated" event.
     */
    public function updated(Cart $cart): void
    {
        //
    }

    /**
     * Handle the cart "deleted" event.
     */
    public function deleted(Cart $cart): void
    {
        //
    }

    /**
     * Handle the cart "restored" event.
     */
    public function restored(Cart $cart): void
    {
        //
    }

    /**
     * Handle the cart "force deleted" event.
     */
    public function forceDeleted(Cart $cart): void
    {
        //
    }
}
