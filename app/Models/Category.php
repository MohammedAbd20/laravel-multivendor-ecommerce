<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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

    public function scopeFilter(Builder $builder, $filters) {
        $f = is_array($filters) ? $filters : $filters->all();

        return $builder
            ->when($f['q'] ?? $f['name'] ?? null, fn ($q, $v) => $q->where('categories.name', 'LIKE', "%{$v}%"))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('categories.status', $v));
    }

    public const STATUSES = ['active', 'archived'];

    public static function rules($id = 0) {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$id])],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ];
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;
        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
