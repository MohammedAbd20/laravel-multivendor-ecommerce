<?php

namespace App\View\Components;

use App\Repositories\Cart\CartRepository;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function Pest\Laravel\get;

class CartMenu extends Component
{
    public $items;
    public $total;
    /**
     * Create a new component instance.
     */
    public function __construct(CartRepository $cart)
    {
        $this->items = $cart->get();
        $this->total = $cart->total();
        // \Log::info('Items:', ['items' => $this->items]);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // dd($this->items);
        return view('components.cart-menu');
    }
}
