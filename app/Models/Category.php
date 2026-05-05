<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function despesas()
    {
        return $this->hasMany(Despesa::class);
    }
}
