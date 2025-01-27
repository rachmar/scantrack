<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'card_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'image',
        'purpose',
    ];

    /**
     * Get the masked email.
     *
     * @return string
     */
    public function getEmailAttribute($value)
    {
        $parts = explode('@', $value);
        $masked = substr($parts[0], 0, 2) . '****';
        return $masked . '@' . $parts[1];
    }

    /**
     * Get the masked phone number.
     *
     * @return string
     */
    public function getPhoneAttribute($value)
    {
        return substr($value, 0, 3) . '****' . substr($value, -3);
    }
}
