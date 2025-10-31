<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_approved',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is team leader
    public function isTeamLeader()
    {
        return $this->role === 'team_leader';
    }

    // Check if user is team member
    public function isTeamMember()
    {
        return $this->role === 'team_member';
    }

    // Problems created by this user
    public function createdProblems()
    {
        return $this->hasMany(Problem::class, 'created_by');
    }

    // Problems assigned to this user
    public function assignedProblems()
    {
        return $this->hasMany(Problem::class, 'assigned_to');
    }

    // Problems solved by this user
    public function solvedProblems()
    {
        return $this->hasMany(Problem::class, 'solved_by');
    }
}