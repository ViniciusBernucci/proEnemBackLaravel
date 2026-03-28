<?php

namespace App\Http\Controllers\Cronogramas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cronogramas\StoreCronogramaRequest;
use App\Models\Cronograma;
use App\Models\Disciplina;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CronogramaController extends Controller
{
    /**
     * Lista os cronogramas do usuário autenticado.
     */
    public function index(): JsonResponse
    {
        try {
            $cronogramas = auth()->user()->cronogramas()->orderBy('created_at', 'desc')->get();

            // Carrega mapa de id => nome para resolver disciplinas sem consulta extra por cronograma
            $disciplinasMap = Disciplina::pluck('nome', 'id');

            $data = $cronogramas->map(function ($cronograma) use ($disciplinasMap) {
                $ids = $cronograma->disciplinas_selecionadas ?? [];
                $nomes = array_values(array_filter(
                    array_map(fn($id) => $disciplinasMap[$id] ?? null, $ids)
                ));

                return [...$cronograma->toArray(), 'disciplinas_selecionadas' => $nomes];
            });

            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao listar cronogramas', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Erro ao listar cronogramas',
            ], 500);
        }
    }

    /**
     * Cria um novo cronograma de estudos para o usuário autenticado.
     */
    public function store(StoreCronogramaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Instancia o cronograma com os dados validados
            $cronograma = new Cronograma($validated);

            // Associa o cronograma ao usuário autenticado e salva no banco
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
                    'nome' => $cronograma->nome,
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

    /**
     * Remove um cronograma do usuário autenticado.
     */
    public function destroy(Cronograma $cronograma): JsonResponse
    {
        if ($cronograma->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Ação não autorizada.',
            ], 403);
        }

        try {
            $cronograma->delete();

            Log::info('[CRONOGRAMAS] Cronograma excluído', [
                'cronograma_id' => $cronograma->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao excluir cronograma', [
                'error' => $e->getMessage(),
                'cronograma_id' => $cronograma->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Erro ao excluir cronograma.',
            ], 500);
        }
    }
}
