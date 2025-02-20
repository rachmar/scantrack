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
        'directory_id',
        'name',
        'phone',
        'purpose',
    ];
    
    public function directory()
    {
        return $this->belongsTo(Directory::class);
    }
}
