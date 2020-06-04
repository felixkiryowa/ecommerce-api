<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'name',
        'description',
        'price',
        'picture',
        'user_id',
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }
}
