<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cronograma extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'nome',
        'data_inicio',
        'data_fim',
        'dias_semana',
        'estudar_feriados',
        'tirar_ferias',
        'disciplinas_selecionadas',
        'minutos_estudo_por_dia',
        'status',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'dias_semana' => 'array',
        'disciplinas_selecionadas' => 'array',
        'estudar_feriados' => 'boolean',
        'tirar_ferias' => 'boolean',
        'minutos_estudo_por_dia' => 'integer',
    ];

    /**
     * Relação com o usuário proprietário do cronograma.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
