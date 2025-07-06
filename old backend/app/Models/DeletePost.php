<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletePost extends Model
{
    use HasFactory;

    protected $table = 'delete_post';

    protected $fillable  = ['user_id', 'post_id'];
}
