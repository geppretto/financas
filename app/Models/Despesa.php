<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = ['descricao', 'valor', 'data', 'user_id'];

}
