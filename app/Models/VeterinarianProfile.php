<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeterinarianProfile extends Model
{
    protected $fillable = ['user_id','professional_license','phone','bio'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
