<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'priority',
        'statement',
        'image',
        'status',
        'assigned_to',
        'comment',
    ];
}
