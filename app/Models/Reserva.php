<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_inici',
        'hora_inici',
        'data_final',
        'hora_final',
        'mail'
    ];

    protected $dates = [
        'data_inici',
        'data_final'
    ];
}
