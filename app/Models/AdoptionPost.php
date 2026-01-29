<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdoptionPost extends Model
{
    protected $fillable = [
        'pet_id',
        'created_by',
        'title',
        'description',
        'requirements',
        'is_active',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requests()
    {
        return $this->hasMany(AdoptionRequest::class);
    }
}
