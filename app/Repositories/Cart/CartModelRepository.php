<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


class CartModelRepository implements CartRepository{

    protected $items;

    public function __construct()
    {
        $this->items = collect([]);
    }

    public function get() : Collection
    {
        if(!$this->items->count()){
            return $this->items = Cart::with('product')->get();
        }
        return $this->items;
    }

    public function add(Product $product,$quantity=1)
    {
        $item = Cart::where('product_id','=',$product->id)
        ->first();
        if(!$item){
            return $cart = Cart::create([
                'user_id'=>Auth::id(),
                'product_id'=>$product->id,
                'quantity'=>$quantity,
            ]);
            $this->get()->push($cart);
        }
        return $item->increment('quantity',$quantity);
    }

    public function update($id, $quantity)
    {
        Cart::where('id','=',$id)
        ->update([
            'quantity'=> $quantity,
        ]);
    }

    public function delete($id)
    {
        Cart::where('id','=',$id)->delete();
    }

    public function empty()
    {
        Cart::query()->delete();
    }

    public function total() : float
    {
        return $this->get()->sum(function($item){
            if($item ->product){
                return $item->quantity * $item ->product->price;
            }
        });
    }

}
