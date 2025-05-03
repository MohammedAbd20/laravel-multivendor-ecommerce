<x-front-layout title="Cart">
    <x-slot:breadcrump>
        <div class="breadcrumbs">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="breadcrumbs-content">
                            <h1 class="page-title">Cart</h1>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <ul class="breadcrumb-nav">
                            <li><a href="/"><i class="lni lni-home"></i> Home</a></li>
                            <li><a href="{{ route('productsF.index') }}">Shop</a></li>
                            <li>cart</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="shopping-cart section">
        <div class="container">
            <div class="cart-list-head">
                <div id="message-box" class="alert alert-success" style="display: none; padding: 10px; margin-bottom: 20px;">

                </div>
                <!-- Cart List Title -->
                <div class="cart-list-title">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-12">

                        </div>
                        <div class="col-lg-4 col-md-3 col-12">
                            <p>Product Name</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>Quantity</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>Subtotal</p>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <p>Discount</p>
                        </div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <p>Remove</p>
                        </div>
                    </div>
                </div>
                <!-- End Cart List Title -->
                <!-- Cart Single List list -->
                @foreach ($cart->get() as $item)
                    <div class="cart-single-list" data-id="{{ $item->id }}">
                        <div class="row align-items-center">
                            <div class="col-lg-1 col-md-1 col-12">
                                <a href="{{ route('productsF.show',$item->product->slug) }}"><img src="{{ $item->product->image_url }}" alt="#"></a>
                            </div>
                            <div class="col-lg-4 col-md-3 col-12">
                                <h5 class="product-name"><a href="{{ route('productsF.show',$item->product->slug) }}">
                                        <h5>{{ $item->product->name }}  </h5></a></h5>
                                <p class="product-des">
                                    <span><em>Type:</em> Mirrorless</span>
                                    <span><em>Color:</em> Black</span>
                                </p>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <div class="count-input">
                                    <input class="form-control quantity-item" data-id="{{ $item->id }}" value="{{ $item->quantity }}" />
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <p>{{ Currency::format($item->quantity * $item->product->price) }}</p>
                            </div>
                            <div class="col-lg-2 col-md-2 col-12">
                                <p>$29.00</p>
                            </div>
                            <div class="col-lg-1 col-md-2 col-12">
                                <a class="remove-item" data-id="{{ $item->id }}" href="javascript:void(0)"><i class="lni lni-close"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
                <!-- End Single List list -->
            </div>
            <div class="row">
                <div class="col-12">
                    <!-- Total Amount -->
                    <div class="total-amount">
                        <div class="row">
                            <div class="col-lg-8 col-md-6 col-12">
                                <div class="left">
                                    <div class="coupon">
                                        <form action="#" target="_blank">
                                            <input name="Coupon" placeholder="Enter Your Coupon">
                                            <div class="button">
                                                <button class="btn">Apply Coupon</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="right">
                                    <ul>
                                        <li>Cart Subtotal<span>{{ Currency::format($cart->total()) }}</span></li>
                                        <li>Shipping<span>Free</span></li>
                                        <li>You Save<span>$29.00</span></li>
                                        <li class="last">You Pay<span>$2531.00</span></li>
                                    </ul>
                                    <div class="button">
                                        <a href="{{ route('checkout') }}" class="btn">Checkout</a>
                                        <a href="product-grids.html" class="btn btn-alt">Continue shopping</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ End Total Amount -->
                </div>
            </div>
        </div>
    </div>



    @push('script')
        <script type="text/javascript">
            $(document).ready(function() {
                $('.quantity-item').on('change', function(e) {
                    $.ajax({
                        url: "/cart/" + $(this).data('id'),
                        method: 'put',
                        data: {
                            quantity: $(this).val(),
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#message-box').text('تم تحديث عدد المنتج بنجاح').show();

                            // إخفاء الرسالة بعد 3 ثوانٍ
                            setTimeout(() => {
                                $('#message-box').fadeOut();
                            }, 3000);
                        },
                        error: function(error) {
                            console.error('خطأ في البيانات:', error.responseJSON);
                        }
                    });

                });

                $('.remove-item').on('click', function(e) {
                    let id =$(this).data('id')

                    $.ajax({
                        url: "/cart/" + id,
                        method: 'delete',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#message-box').text('تم حذف المنتج بنجاح').show();

                            $(`.cart-single-list[data-id="${id}"]`).remove();

                            setTimeout(() => {
                                $('#message-box').fadeOut();
                            }, 3000);
                        },
                        error: function(error) {
                            console.error('خطأ في البيانات:', error.responseJSON);
                        }
                    });

                });
            });
        </script>
    @endpush
</x-front-layout>
