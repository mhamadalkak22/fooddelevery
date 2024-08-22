<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'payment_method',
        'delivery_address',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function foodItems()
    {
        return $this->belongsToMany(FoodItem::class);
    }
    
}
