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
        'paid_amount',
        'activity'
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
