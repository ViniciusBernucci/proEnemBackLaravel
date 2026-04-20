<?php

namespace App\Http\Controllers;

use App\Models\Cronograma;
use App\Models\CronogramaTarefa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackerController extends Controller
{
    /**
     * Busca as tarefas do cronograma ativo do usuário dentro do intervalo de datas limitando à exibição do painel.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        try {
            // Busca o cronograma ativo do usuário logado (Apenas ID é necessário)
            $cronogramaAtivo = Cronograma::select('id')
                ->where('user_id', auth()->id())
                ->where('ativo', true)
                ->first();

            if (!$cronogramaAtivo) {
                return response()->json(['message' => 'Nenhum cronograma ativo encontrado.'], 404);
            }

            // Busca apenas as tarefas entre as datas solicitadas
            $tarefas = CronogramaTarefa::where('cronograma_id', $cronogramaAtivo->id)
                ->whereBetween('data', [$request->start_date, $request->end_date])
                ->orderBy('data')
                ->orderBy('slot')
                ->get();

            // Mapeia os tipos de aula gerados pelo algoritmo para os tipos conhecidos pelo Frontend Tracker
            $mappedTarefas = $tarefas->map(function ($tarefa) {
                
                $typeVal = 'reading';
                if (in_array($tarefa->tipo, ['simulado', 'redacao'])) {
                    $typeVal = 'exercise';
                } elseif ($tarefa->tipo === 'conteudo_novo') {
                    $typeVal = 'video';
                }
                
                return [
                    'id'         => (string) $tarefa->id,
                    'subject'    => ucfirst($tarefa->disciplina),
                    'topic'      => $tarefa->topico,
                    'duration'   => $tarefa->duracao_minutos,
                    'completed'  => (bool) $tarefa->completada,
                    'date'       => $tarefa->data->format('Y-m-d'),
                    'type'       => $typeVal,
                ];
            });

            return response()->json([
                'data' => $mappedTarefas
            ], 200);

        } catch (\Exception $e) {
            Log::error('[TRACKER] Erro ao buscar tarefas do tracker', [
                'error'   => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'Erro ao listar tarefas do tracker'], 500);
        }
    }

    /**
     * Alterna o status de completada de uma tarefa do tracker.
     */
    public function toggle($id): JsonResponse
    {
        try {
            $tarefa = CronogramaTarefa::findOrFail($id);

            // Otimização de Performance: Valida a permissão sem puxar $tarefa->cronograma (evitando 
            // carregar uma tabela que possui um "json blob" mega pesado do gerador na memória)
            $pertence = Cronograma::where('id', $tarefa->cronograma_id)->where('user_id', auth()->id())->exists();

            if (!$pertence) {
                return response()->json(['message' => 'Ação não autorizada.'], 403);
            }

            $tarefa->completada = !$tarefa->completada;
            $tarefa->save();

            return response()->json([
                'data' => [
                    'id'        => (string) $tarefa->id,
                    'completed' => $tarefa->completada,
                ],
                'message' => 'Status da tarefa atualizado com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('[TRACKER] Erro ao alterar status da tarefa', [
                'error'     => $e->getMessage(),
                'user_id'   => auth()->id(),
                'tarefa_id' => $id,
            ]);

            return response()->json(['message' => 'Erro ao atualizar tarefa'], 500);
        }
    }
}
