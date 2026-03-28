<?php

namespace App\Http\Controllers\Cronogramas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cronogramas\StoreCronogramaRequest;
use App\Models\Cronograma;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CronogramaController extends Controller
{
    /**
     * Cria um novo cronograma de estudos para o usuário autenticado.
     */
    public function store(StoreCronogramaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Cria o cronograma sem user_id (protegido em $guarded)
            $cronograma = Cronograma::create($validated);

            // Associa o cronograma ao usuário autenticado de forma segura
            $cronograma->user_id = auth()->id();
            $cronograma->save();

            Log::info('[CRONOGRAMAS] Novo cronograma criado', [
                'cronograma_id' => $cronograma->id,
                'user_id' => $cronograma->user_id,
            ]);

            return response()->json([
                'data' => [
                    'id' => $cronograma->id,
                    'user_id' => $cronograma->user_id,
                    'data_inicio' => $cronograma->data_inicio->format('Y-m-d'),
                    'data_fim' => $cronograma->data_fim->format('Y-m-d'),
                    'dias_semana' => $cronograma->dias_semana,
                    'estudar_feriados' => $cronograma->estudar_feriados,
                    'tirar_ferias' => $cronograma->tirar_ferias,
                    'disciplinas_selecionadas' => $cronograma->disciplinas_selecionadas,
                    'minutos_estudo_por_dia' => $cronograma->minutos_estudo_por_dia,
                    'status' => $cronograma->status,
                    'created_at' => $cronograma->created_at,
                    'updated_at' => $cronograma->updated_at,
                ],
                'message' => 'Cronograma criado com sucesso',
            ], 201);

        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao criar cronograma', [
                'error' => $e->getMessage(),
                'user_id' => auth()->user()->id ?? null,
            ]);

            return response()->json([
                'message' => 'Erro ao criar cronograma',
            ], 500);
        }
    }
}
