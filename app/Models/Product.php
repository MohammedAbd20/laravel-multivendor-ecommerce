<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name','parent_id','slug','description','image','status','category_id','store_id','price','compare_price','quantity','featured'
    ];
    protected $hidden = [
        'created_at','deleted_at','updated_at'
    ];
    protected $appends = [
        'image_url'
    ];

    public const LOW_STOCK = 5;
    public const STATUSES = ['active', 'draft', 'archived'];

    /** Dashboard / API filter. Accepts an array or a Request. */
    public function scopeFilter(Builder $builder, $filters) {
        $f = is_array($filters) ? $filters : $filters->all();

        return $builder
            ->when($f['q'] ?? $f['name'] ?? null, fn ($q, $v) => $q->where('products.name', 'LIKE', "%{$v}%"))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('products.status', $v))
            ->when($f['store_id'] ?? null, fn ($q, $v) => $q->where('products.store_id', $v))
            ->when($f['category_id'] ?? null, fn ($q, $v) => $q->where('products.category_id', $v))
            ->when($f['tag_id'] ?? null, fn ($q, $v) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $v)))
            ->when($f['stock'] ?? null, function ($q, $v) {
                return match ($v) {
                    'out' => $q->where('products.quantity', '<=', 0),
                    'low' => $q->where('products.quantity', '>', 0)->where('products.quantity', '<=', self::LOW_STOCK),
                    'in' => $q->where('products.quantity', '>', self::LOW_STOCK),
                    default => $q,
                };
            });
    }

    public function category(){
        return $this->belongsTo(Category::class,'category_id','id');
    }
    public function store(){
        return $this->belongsTo(Store::class,'store_id','id');
    }

    public function tags(){
        return $this->belongsToMany(
            Tag::class,
            'product_tag',
            'product_id',
            'tag_id',
            'id',
            'id'
        );
    }


    public static function booted() {
        static::addGlobalScope('store',function(Builder $builder){
            $user = Auth::user();
            if($user && $user->store_id){
                $builder->where('store_id','=',$user->store_id);
            }
        });

        static::creating(function(Product $product) {
            $product->slug = static::uniqueSlug($product->name);
        });

        static::updating(function(Product $product) {
            if ($product->isDirty('name')) {
                $product->slug = static::uniqueSlug($product->name, $product->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 2;
        while (static::withoutGlobalScopes()->withTrashed()->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public static function rules($id = 0) {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** Products shoppers can see: active product in an active store. */
    public function scopeActive(Builder $builder) {
        $builder->where('products.status','=','active')
            ->whereHas('store', fn ($q) => $q->where('status', 'active'));
    }


    public function getImageUrlAttribute() {
        if(!$this->image){
            return asset('assets/images/default-product.svg');
        }
        if(Str::startsWith($this->image,['http://','https://'])){
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
    public function getSalePercentAttribute() {
        if(!$this->compare_price){
            return 0;
        }
        return number_format(100 - (100* $this->price / $this->compare_price),1);
    }
}
