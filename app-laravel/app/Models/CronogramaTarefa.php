<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronogramaTarefa extends Model
{
    use HasFactory;

    protected $fillable = [
        'cronograma_id',
        'data',
        'dia_semana',
        'slot',
        'disciplina',
        'topico',
        'tipo',
        'duracao_minutos',
        'completada',
    ];

    protected $casts = [
        'data' => 'date',
        'slot' => 'integer',
        'duracao_minutos' => 'integer',
        'completada' => 'boolean',
    ];

    public function cronograma()
    {
        return $this->belongsTo(Cronograma::class);
    }
}
