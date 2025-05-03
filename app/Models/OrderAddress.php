<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\Intl\Countries;

class OrderAddress extends Model
{
    use HasFactory, Notifiable;
    public $timestamps = false;

    protected $fillable = [
        'order_id','type','first_name','last_name','email','phone_number',
        'street_address','city','postal_code','country','state',
    ];

    public function getNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getCountryNameAttribute() {
        if($this->country){
            return Countries::getName($this->country) ;
        }
    }
}
