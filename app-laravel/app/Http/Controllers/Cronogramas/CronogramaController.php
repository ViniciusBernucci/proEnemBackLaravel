<?php

namespace App\Http\Controllers\Cronogramas;

use App\DTO\CronogramaInputDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cronogramas\StoreCronogramaRequest;
use App\Models\Cronograma;
use App\Models\CronogramaTarefa;
use App\Models\Disciplina;
use App\Services\CronogramaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CronogramaController extends Controller
{
    public function __construct(
        private readonly CronogramaService $cronogramaService
    ) {}

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
     * Retorna um cronograma específico do usuário autenticado, incluindo o cronograma_json gerado.
     */
    public function show(Cronograma $cronograma): JsonResponse
    {
        if ($cronograma->user_id !== auth()->id()) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        return response()->json(['data' => $cronograma], 200);
    }

    /**
     * Cria um novo cronograma de estudos para o usuário autenticado
     * e aciona imediatamente o algoritmo de geração detalhada.
     */
    public function store(StoreCronogramaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // ------------------------------------------------------------------
            // 1. Salvar os dados brutos do wizard no banco
            // ------------------------------------------------------------------
            $cronograma = new Cronograma($validated);
            $cronograma->user_id = auth()->id();
            $cronograma->save();

            Log::info('[CRONOGRAMAS] Novo cronograma criado', [
                'cronograma_id' => $cronograma->id,
                'user_id'       => $cronograma->user_id,
            ]);

            // ------------------------------------------------------------------
            // 2. Construir o DTO para o CronogramaService
            //
            // O StoreCronogramaRequest e o CronogramaInputDTO têm formatos
            // ligeiramente diferentes que precisam ser mapeados:
            //   - dias_semana: strings ("seg","ter"...) → ints Carbon (1,2...)
            //   - minutos_estudo_por_dia: int (120) → horasPorDia: int (2)
            //   - disciplinas_selecionadas: IDs de disciplinas → slugs
            //   - tirar_ferias: bool (negado) → estudarFerias: bool (positivo)
            // ------------------------------------------------------------------
            $diasSemanaInt          = $this->mapearDiasSemana($validated['dias_semana']);
            $horasPorDia            = (int) round($validated['minutos_estudo_por_dia'] / 60);
            $slugsDisciplinas       = $this->resolverSlugsDisciplinas($validated['disciplinas_selecionadas']);
            $estudarFerias          = ! $validated['tirar_ferias'];

            $dto = new CronogramaInputDTO(
                dataInicio              : Carbon::parse($validated['data_inicio']),
                dataFim                 : Carbon::parse($validated['data_fim']),
                diasSemana              : $diasSemanaInt,
                estudarFeriados         : (bool) $validated['estudar_feriados'],
                estudarFerias           : $estudarFerias,
                disciplinasSelecionadas : $slugsDisciplinas,
                horasPorDia             : max(1, $horasPorDia),
            );

            // ------------------------------------------------------------------
            // 3. Executar o algoritmo e persistir o resultado
            // ------------------------------------------------------------------
            $resultado = $this->cronogramaService->gerarCronograma($dto);

            if ($resultado['sucesso']) {
                $cronograma->cronograma_json = $resultado;
                $cronograma->save();

                // Fatiar JSON em tarefas e fazer batch insert
                $this->inserirTarefasNoBanco($cronograma, $resultado['cronograma'] ?? []);

                Log::info('[CRONOGRAMAS] cronograma_json gerado e salvo com sucesso', [
                    'cronograma_id'         => $cronograma->id,
                    'total_dias_estudo'     => $resultado['resumo']['total_dias_estudo'] ?? null,
                    'total_slots'           => $resultado['resumo']['total_slots'] ?? null,
                ]);
            } else {
                // Algoritmo retornou erro (período muito curto, sem tópicos, etc.)
                // O registro já foi salvo — logamos o aviso mas não falhamos a request.
                Log::warning('[CRONOGRAMAS] Algoritmo não gerou cronograma_json', [
                    'cronograma_id' => $cronograma->id,
                    'erro'          => $resultado['erro'] ?? 'desconhecido',
                ]);
            }

            // ------------------------------------------------------------------
            // 4. Montar resposta
            // ------------------------------------------------------------------
            return response()->json([
                'data' => [
                    'id'                       => $cronograma->id,
                    'user_id'                  => $cronograma->user_id,
                    'nome'                     => $cronograma->nome,
                    'data_inicio'              => $cronograma->data_inicio->format('Y-m-d'),
                    'data_fim'                 => $cronograma->data_fim->format('Y-m-d'),
                    'dias_semana'              => $cronograma->dias_semana,
                    'estudar_feriados'         => $cronograma->estudar_feriados,
                    'tirar_ferias'             => $cronograma->tirar_ferias,
                    'disciplinas_selecionadas' => $cronograma->disciplinas_selecionadas,
                    'minutos_estudo_por_dia'   => $cronograma->minutos_estudo_por_dia,
                    'status'                   => $cronograma->status,
                    'cronograma_json'          => $cronograma->cronograma_json,
                    'created_at'               => $cronograma->created_at,
                    'updated_at'               => $cronograma->updated_at,
                ],
                'message'       => 'Cronograma criado com sucesso',
                'algoritmo_ok'  => $resultado['sucesso'],
                'algoritmo_erro'=> $resultado['sucesso'] ? null : ($resultado['erro'] ?? null),
            ], 201);

        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao criar cronograma', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
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
                'user_id'       => auth()->id(),
            ]);

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao excluir cronograma', [
                'error'         => $e->getMessage(),
                'cronograma_id' => $cronograma->id,
                'user_id'       => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Erro ao excluir cronograma.',
            ], 500);
        }
    }

    /**
     * Ativa um cronograma e desativa os demais do mesmo usuário.
     */
    public function ativar(Cronograma $cronograma): JsonResponse
    {
        if ($cronograma->user_id !== auth()->id()) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        try {
            // Desativa todos do usuário
            Cronograma::where('user_id', auth()->id())->update(['ativo' => false]);

            // Ativa o selecionado
            $cronograma->ativo = true;
            $cronograma->save();

            Log::info('[CRONOGRAMAS] Cronograma ativado', [
                'cronograma_id' => $cronograma->id,
                'user_id'       => auth()->id(),
            ]);

            return response()->json(['message' => 'Cronograma ativado com sucesso.'], 200);
        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao ativar cronograma', [
                'error'         => $e->getMessage(),
                'cronograma_id' => $cronograma->id,
                'user_id'       => auth()->id(),
            ]);

            return response()->json(['message' => 'Erro ao ativar cronograma.'], 500);
        }
    }

    /**
     * Retorna o cronograma ativo do usuário atual, se existir.
     */
    public function ativo(): JsonResponse
    {
        try {
            $cronograma = Cronograma::where('user_id', auth()->id())
                ->where('ativo', true)
                ->first();

            if (!$cronograma) {
                return response()->json(['message' => 'Nenhum cronograma ativo encontrado.'], 404);
            }

            return response()->json(['data' => $cronograma], 200);
        } catch (\Exception $e) {
            Log::error('[CRONOGRAMAS] Erro ao buscar cronograma ativo', [
                'error'   => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'Erro ao buscar cronograma ativo.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES DE MAPEAMENTO
    |--------------------------------------------------------------------------
    */

    /**
     * Converte dias da semana do formato do wizard (strings abreviadas)
     * para o formato Carbon utilizado pelo CronogramaService (ints 0-6).
     *
     * O wizard envia: ["seg", "ter", "qua", "qui", "sex", "sab", "dom"]
     * O DTO espera:   [1, 2, 3, 4, 5, 6, 0]  (Carbon: 0=dom, 1=seg ... 6=sáb)
     */
    private function mapearDiasSemana(array $dias): array
    {
        $mapa = [
            'dom' => 0,
            'seg' => 1,
            'ter' => 2,
            'qua' => 3,
            'qui' => 4,
            'sex' => 5,
            'sab' => 6,
        ];

        return array_values(array_filter(
            array_map(fn(string $d) => $mapa[$d] ?? null, $dias),
            fn($v) => $v !== null
        ));
    }

    /**
     * Converte IDs de disciplinas (vindos do wizard) para slugs de texto
     * usados pelo CronogramaService ao consultar a VIEW 'topicos'.
     *
     * Mapeamento: nome da disciplina → slug do algoritmo
     * Ex: "Matemática" → "matematica", "Língua Portuguesa" → "portugues"
     */
    private function resolverSlugsDisciplinas(array $ids): array
    {
        $nomesParaSlugs = [
            'Matemática'        => 'matematica',
            'Língua Portuguesa' => 'portugues',
            'Literatura'        => 'literatura',
            'Inglês'            => 'ingles',
            'Espanhol'          => 'espanhol',
            'Redação'           => 'redacao',
            'Física'            => 'fisica',
            'Química'           => 'quimica',
            'Biologia'          => 'biologia',
            'História'          => 'historia',
            'Geografia'         => 'geografia',
            'Filosofia'         => 'filosofia',
            'Sociologia'        => 'sociologia',
        ];

        $disciplinas = Disciplina::whereIn('id', $ids)->pluck('nome', 'id');

        return array_values(array_filter(
            array_map(fn(string $nome) => $nomesParaSlugs[$nome] ?? null, $disciplinas->toArray()),
            fn($v) => $v !== null
        ));
    }

    /**
     * Pega o array do cronograma gerado e salva cada slot como uma CronogramaTarefa
     */
    private function inserirTarefasNoBanco(Cronograma $cronograma, array $dias): void
    {
        $tarefasInsert = [];
        $agora = now();

        foreach ($dias as $dia) {
            if (empty($dia['aulas'])) {
                continue;
            }

            // Duração do dia dividida pela quantidade de slots do dia
            $qtdSlots = count($dia['aulas']);
            $duracaoSlot = (int) round($cronograma->minutos_estudo_por_dia / $qtdSlots);

            foreach ($dia['aulas'] as $aula) {
                $tarefasInsert[] = [
                    'cronograma_id'   => $cronograma->id,
                    'data'            => $dia['data'],
                    'dia_semana'      => $dia['dia_semana'],
                    'slot'            => $aula['slot'],
                    'disciplina'      => $aula['disciplina'],
                    'topico'          => $aula['topico'],
                    'tipo'            => $aula['tipo'],
                    'duracao_minutos' => $duracaoSlot,
                    'completada'      => false,
                    'created_at'      => $agora,
                    'updated_at'      => $agora,
                ];
            }
        }

        if (!empty($tarefasInsert)) {
            // Bulk insert para máxima performance (chunk de 1000 para não estourar max input variables caso enorme)
            foreach (array_chunk($tarefasInsert, 1000) as $chunk) {
                CronogramaTarefa::insert($chunk);
            }
        }
    }

    /**
     * Gera e baixa um arquivo PDF detalhado do cronograma com estética customizada e textos de teoria
     */
    public function exportPdf(Cronograma $cronograma)
    {
        if ($cronograma->user_id !== auth()->id()) {
            return response()->json(['message' => 'Você não tem permissão para imprimir este cronograma.'], 403);
        }

        $tarefas = CronogramaTarefa::where('cronograma_id', $cronograma->id)
            ->orderBy('data')
            ->orderBy('slot')
            ->get();

        if ($tarefas->isEmpty()) {
            return response()->json(['message' => 'Este cronograma não possui roteiro processado e não pode ser PDF.'], 422);
        }

        // Agrupar tarefas pela data para montar no HTML facilmente
        $dias = $tarefas->groupBy(function($item) {
            return $item->data->format('Y-m-d');
        });

        $pdf = Pdf::loadView('pdf.cronograma', [
            'cronograma' => $cronograma,
            'dias'       => $dias
        ]);

        // Retorna via force download stream
        return $pdf->download('meu_cronograma_' . $cronograma->id . '.pdf');
    }
}
