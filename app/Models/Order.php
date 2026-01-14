<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'order_code',
        'user_id',
        'sub_total',
        'tax',
        'grand_total',
        'status',
        'table_number',
        'payment_method',
        'notes',
    ];

    protected $dates = ['deleted_at'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
