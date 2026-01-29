<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
        'name',
        'species',
        'breed',
        'sex',
        'birth_date',
        'color',
        'is_sterilized',
        'is_vaccinated',
        'description',
        'photo_path'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function adoptionPost()
    {
    return $this->hasOne(\App\Models\AdoptionPost::class);
    }

}
