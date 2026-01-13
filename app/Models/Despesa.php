<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = ['descricao', 'valor', 'data', 'user_id', 'category_id'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
