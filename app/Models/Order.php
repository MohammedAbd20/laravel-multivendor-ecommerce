<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'];
    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed'];

    protected $fillable = [
        'store_id','user_id','payment_method','status','payment_status',
    ];

    /** Registered customer's name, or the billing name for guest checkouts. */
    public function getCustomerNameAttribute(): string {
        if ($this->user_id && $this->user->name !== 'Guest Customer') {
            return $this->user->name;
        }
        $bill = $this->billingAddresses;
        $name = trim(($bill->first_name ?? '') . ' ' . ($bill->last_name ?? ''));

        return $name !== '' ? $name : 'Guest';
    }

    public function items() {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /** Adds a `total` column (sum of price x quantity of the order's items). */
    public function scopeWithTotal(Builder $builder) {
        return $builder->addSelect([
            'orders.*',
            'total' => OrderItem::query()
                ->selectRaw('COALESCE(SUM(price * quantity), 0)')
                ->whereColumn('order_items.order_id', 'orders.id'),
        ]);
    }

    /** Orders that count as revenue. */
    public function scopeRevenue(Builder $builder) {
        return $builder->whereNotIn('status', ['cancelled', 'refunded']);
    }

    public function scopeFilter(Builder $builder, array $filters) {
        return $builder
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('orders.status', $v))
            ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('orders.payment_status', $v))
            ->when($filters['store_id'] ?? null, fn ($q, $v) => $q->where('orders.store_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('orders.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('orders.created_at', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('orders.number', 'LIKE', "%{$v}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'LIKE', "%{$v}%")->orWhere('email', 'LIKE', "%{$v}%"))
                      ->orWhereHas('addresses', fn ($a) => $a->where('email', 'LIKE', "%{$v}%")
                          ->orWhere('first_name', 'LIKE', "%{$v}%")->orWhere('last_name', 'LIKE', "%{$v}%"));
                });
            });
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function user() {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Guest Customer',
        ]);
    }
    public function products() {
        return $this->belongsToMany(Product::class,'order_items','order_id','product_id','id','id')
        ->using(OrderItem::class)
        ->as('order_item')
        ->withPivot([
            'product_name','price','quantity','options'
        ]);
    }

    public function addresses() {
        return $this->hasMany(OrderAddress::class);
    }
    public function billingAddresses() {
        return $this->hasOne(OrderAddress::class,'order_id','id')
        ->where('type','=','billing');
    }
    public function shippingAddresses() {
        return $this->hasOne(OrderAddress::class,'order_id','id')
        ->where('type','=','shipping');
    }


    public static function booted(){
        static::creating(function(Order $order){
            $order->number = Order::getNextOrderNumber();
        });
    }

    public static function getNextOrderNumber(){
        $year = Carbon::now()->year;
        $number = Order::whereYear('created_at',$year)->max('number');
        if($number){
            return $number + 1;
        }
        return $year . '0001';
    }
}
