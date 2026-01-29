<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdoptionRequest extends Model
{
    protected $fillable = [
        'adoption_post_id',
        'applicant_id',
        'message',
        'status',
    ];

    public function post()
    {
        return $this->belongsTo(AdoptionPost::class, 'adoption_post_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
