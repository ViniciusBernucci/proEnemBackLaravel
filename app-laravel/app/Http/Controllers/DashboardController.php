<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Cronograma;
use App\Models\CronogramaTarefa;

class DashboardController extends Controller
{
    /**
     * Retorna os resumos estatisticos para popular a tela principal do Dashboard
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Calculo da Data do ENEM
            $hojeCarbon = Carbon::now();
            $enemData = Carbon::create($hojeCarbon->year, 11, 1);
            if ($hojeCarbon->gt($enemData)) {
                $enemData->addYear();
            }
            $diasParaEnem = (int) round($hojeCarbon->startOfDay()->diffInDays($enemData->startOfDay()));

            // Busca o cronograma ativo
            $cronogramaAtivo = Cronograma::select('id')
                ->where('user_id', $user->id)
                ->where('ativo', true)
                ->first();

            $metaHoje = 0;
            $metaSemana = 0;
            $estudadosSemana = 0;
            
            // Arrays para Gráficos
            $weeklyChartData = [0, 0, 0, 0, 0, 0, 0]; // Seg-Dom
            $monthlyChartData = array_fill(0, 12, 0); // Jan-Dez
            $subjectChartData = ['series' => [], 'labels' => []];

            if ($cronogramaAtivo) {
                // Datas Úteis
                $startOfWeek = (clone $hojeCarbon)->startOfWeek(); // Segunda
                $endOfWeek = (clone $hojeCarbon)->endOfWeek();     // Domingo
                $startOfYear = (clone $hojeCarbon)->startOfYear();
                $endOfYear = (clone $hojeCarbon)->endOfYear();

                // 1. Métricas de Cards (Hoje e Semana)
                $metaHoje = CronogramaTarefa::where('cronograma_id', $cronogramaAtivo->id)
                    ->whereDate('data', $hojeCarbon->toDateString())
                    ->count();

                $semanaTarefas = CronogramaTarefa::where('cronograma_id', $cronogramaAtivo->id)
                    ->whereBetween('data', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                    ->get();
                
                $metaSemana = $semanaTarefas->count();
                $estudadosSemana = $semanaTarefas->filter(fn($t) => (bool)$t->completada)->count();

                // 2. Gráfico Semanal (Atividade por dia da semana atual)
                $semanaAgrupada = $semanaTarefas->where('completada', true)->groupBy(function($t) {
                    // Carbon dayOfWeek: 0 (dom) a 6 (sab). 
                    // Queremos Seg(0) a Dom(6)
                    $day = Carbon::parse($t->data)->dayOfWeek;
                    return $day == 0 ? 6 : $day - 1; // Ajusta dom(0) para 6 e seg(1) para 0
                });

                foreach ($semanaAgrupada as $dayIndex => $tasks) {
                    if (isset($weeklyChartData[$dayIndex])) {
                        $weeklyChartData[$dayIndex] = $tasks->count();
                    }
                }

                // 3. Gráfico Mensal (Produtividade no ano)
                $anoTarefas = CronogramaTarefa::where('cronograma_id', $cronogramaAtivo->id)
                    ->where('completada', true)
                    ->whereBetween('data', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                    ->selectRaw('EXTRACT(MONTH FROM data) as mes, count(*) as total')
                    ->groupBy('mes')
                    ->pluck('total', 'mes');

                foreach ($anoTarefas as $mes => $total) {
                    $monthlyChartData[(int)$mes - 1] = $total;
                }

                // 4. Distribuição por Disciplina (Top 5)
                $disciplinas = CronogramaTarefa::where('cronograma_id', $cronogramaAtivo->id)
                    ->selectRaw('disciplina, count(*) as total')
                    ->groupBy('disciplina')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();

                foreach ($disciplinas as $d) {
                    $subjectChartData['series'][] = $d->total;
                    $subjectChartData['labels'][] = ucfirst($d->disciplina);
                }
            }

            return response()->json([
                'data' => [
                    'nome_usuario'     => explode(' ', trim($user->name))[0] ?? 'Aluno',
                    'dias_para_enem'   => $diasParaEnem,
                    'meta_hoje'        => $metaHoje,
                    'meta_semana'      => $metaSemana,
                    'estudados_semana' => $estudadosSemana,
                    'ultimo_simulado'  => '-',
                    'weekly_chart'     => $weeklyChartData,
                    'monthly_chart'    => $monthlyChartData,
                    'subject_chart'    => $subjectChartData,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('[DASHBOARD] Erro ao processar as stats', [
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'Erro interno ao processar as informações do dashboard.'], 500);
        }
    }
}
