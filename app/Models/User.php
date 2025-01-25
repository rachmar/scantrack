<?php

namespace App\Models; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'name', 'email', 'password'
    ];
  
    protected $hidden = [
        'password', 'remember_token',
    ];

  
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
    
    public function hasAnyRole($roles)
    {
        $roles = (array) $roles;
    
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
    
        return false;
    }
    
    public function hasRole($role)
    {
        return $this->roles()->where('slug', $role)->exists();
    }
}
