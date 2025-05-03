<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','parent_id','slug','description','image','status'
    ];


    public function products(){
        return $this->hasMany(Product::class,'category_id','id');
    }

    public function parent(){
        return $this->belongsTo(Category::class,'parent_id','id')
        ->withDefault([
            'name'=>'-'
        ]);
    }

    public function children(){
        return $this->hasMany(Category::class,'parent_id','id');
    }

    public function scopeFilter(Builder $builder,$filters) {
        if($name = $filters['name']??false){
            $builder->where('name','LIKE',"%{$name}%");
        }
        if($status = $filters['status']??false){
            $builder->where('status','=',$status);
        }
    }

    public static function rules($id = 0) {
        return [
            "name"=>[
                "required", 'string', 'min:3', 'max:255',
                Rule::unique('categories','name')->ignore($id),
            ],
            "parent_id"=>[
                'nullable','int','exists:categories,id'
            ],
            "image"=>[
                'image', 'max:1048576' , 'dimensions:maxheight=70'
            ],
            "status"=>'in:active,archived|required',
        ];
    }
}
