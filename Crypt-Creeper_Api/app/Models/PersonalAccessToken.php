<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'token', 'abilities', 'last_used_at', 'expires_at'
    ];

    public function tokenable()
    {
        return $this->morphTo();
        // morphTo es un método de Eloquent en Laravel que permite una relación polimórfica
    }
}







