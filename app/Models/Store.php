<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory, Notifiable;

    public const STATUSES = ['active', 'inactive', 'archived'];

    /** Fixed palette: each store keeps the same colour everywhere in the dashboard. */
    public const COLORS = [
        '#0B7A5F', '#C2412D', '#3B5BDB', '#D98A1E', '#8E44AD',
        '#0E8AA8', '#B5497E', '#5C7A1F', '#7A5C3E', '#2F4858',
    ];

    protected $fillable = [
        'name', 'slug', 'description', 'logo_image', 'cover_image', 'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', 'active');
    }

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['name'] ?? null, fn ($q, $v) => $q->where('name', 'LIKE', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }

    public function getColorAttribute(): string
    {
        return self::COLORS[($this->id ?? 0) % count(self::COLORS)];
    }

    public function getInitialsAttribute(): string
    {
        return Str::upper(Str::substr(preg_replace('/[^\pL\pN ]/u', '', $this->name), 0, 2));
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_image) {
            return null;
        }
        if (Str::startsWith($this->logo_image, ['http://', 'https://'])) {
            return $this->logo_image;
        }

        return asset('storage/' . $this->logo_image);
    }

    public static function rules(int $id = 0): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('stores', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', \Illuminate\Validation\Rule::in(self::STATUSES)],
            'logo_image' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
