<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'gross_amount',
        'processing_fee',
        'platform_fee',
        'net_amount',
        'currency',
        'processor_payload',
        'activity',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processor_payload' => 'array',
        'activity' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Each donation belongs to one post
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    // public function users(){
    //     return $this->hasMany(User::class, 'user_id');
    // }

    // public function posts(){
    //     return $this->hasMany(Post::class, 'post_id');
    // }

    // public function user(){
    //     return $this->belongsTo(User::class);
    // }

    // public function post(){
    //     return $this->belongsTo(Post::class);
    // }
}
