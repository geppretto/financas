<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = ['descricao', 'valor', 'data', 'pago', 'user_id', 'category_id'];

    protected $casts = [
        'data' => 'date',
        'pago' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
