<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    protected $fillable = [
        'created_by',
        'department',
        'priority',
        'statement',
        'image',
        'status',
        'assigned_to',
        'solved_by',
        'verified_by',
        'comment',
        'solution',
        'assigned_at',
        'solved_at',
        'verified_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'solved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Creator of the problem
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Assigned user
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Solver
    public function solver()
    {
        return $this->belongsTo(User::class, 'solved_by');
    }

    // Verifier
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}