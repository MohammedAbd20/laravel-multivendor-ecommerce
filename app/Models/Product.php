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
        'name','parent_id','slug','description','image','status','category_id','store_id','price','compare_price'
    ];
    protected $hidden = [
        'created_at','deleted_at','updated_at'
    ];
    protected $appends = [
        'image_url'
    ];

    public function scopeFilter(Builder $builder,$filters) {
        if($name = $filters['name']??false){
            $builder->where('name','LIKE',"%{$name}%");
        }
        if($status = $filters['status']??false){
            $builder->where('status','=',$status);
        }
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
            $product->slug = Str::slug($product->name);
        });
    }

    public static function rules($id = 0) {
        return [
            "name"=>[
                "required", 'string', 'min:3', 'max:255',
                Rule::unique('categories','name')->ignore($id),
            ],
            "store_id"=>[
                'nullable','int','exists:stores,id'
            ],
            "category_id"=>[
                'nullable','int','exists:categories,id'
            ],
            "image"=>[
                'image', 'max:1048576' , 'dimensions:maxheight=70'
            ],
            "status"=>'in:active,archived|required',
        ];
    }


    public function scopeActive(Builder $builder) {
        $builder->where('status','=','active');
    }


    public function getImageUrlAttribute() {
        if(!$this->image){
            return 'https://www.opelgtsource.com/assets/default_product.png';
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


    public function scopeFillter(Builder $builder,$filters) {
        $options = array_merge([
            'store_id' => null,
            'category_id' => null,
            'tag_id' => [],
            'status' => 'active',
        ],$filters);

        $builder->when($options['store_id'],function($builder, $value){
            $builder->where('store_id',$value);
        });
        $builder->when($options['category_id'],function($builder, $value){
            $builder->where('category_id',$value);
        });
        $builder->when($options['status'],function($builder, $value){
            $builder->where('status',$value);
        });

        $builder->when($options['tag_id'],function($builder, $value){
            $builder->whereExists(function($query) use ($value){
                $query->select(1)
                ->from('product_tag')
                ->whereRae('product_id','products.id')
                ->where('tag_id',$value);
            });
        });
    }
}
