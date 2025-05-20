<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    protected $fillable = ['descricao', 'valor', 'data', 'pago', 'user_id'];
}
