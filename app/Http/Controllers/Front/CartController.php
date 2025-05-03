<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Cart\CartModelRepository;
use App\Repositories\Cart\CartRepository;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cart;

    public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd($this->cart->get()[0]->quantity);
        return view('front.cart',[
            'cart' => $this->cart,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required','int','exists:products,id'],
            'quantity' => ['nullable','int','min:1']
        ]);
        $product = Product::findOrFail($request->post('product_id'));
        // dd($request->post('quantity'));
        $this->cart->add($product,$request->post('quantity'));

        return redirect()->route('cart.index')->with('success','Done');
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => ['required','int','min:1']
        ]);
        $this->cart->update($id,$request->post('quantity'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->cart->delete($id);
    }
}
