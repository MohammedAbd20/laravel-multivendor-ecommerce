<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'slug','name'
    ];
    public function products(){
        return $this->belongsToMany(Product::class);
    }
}
