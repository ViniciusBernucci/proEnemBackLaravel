<?php

namespace App\Services;

use App\DTO\CronogramaInputDTO;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * SERVIÇO GERADOR DE CRONOGRAMA DE ESTUDOS PARA O ENEM
 * ============================================================================
 *
 * Este serviço implementa o algoritmo completo de geração de cronograma
 * dividido em 6 fases:
 *
 *   FASE 1 — Cálculo do Tempo Disponível (dias válidos e total de slots)
 *   FASE 2 — Distribuição de Slots entre Disciplinas (peso proporcional)
 *   FASE 3 — Priorização e Alocação dos Tópicos (score ponderado)
 *   FASE 4 — Montagem do Calendário (round-robin + mapeamento nos dias)
 *   FASE 5 — Simulados Mensais e Prática de Redação
 *   FASE 6 — Montagem da Saída Final (JSON estruturado)
 *
 * Princípios de design:
 * - Cada fase é um método isolado para facilitar testes unitários.
 * - O estado intermediário é passado via arrays/collections, sem side-effects.
 * - Todas as decisões de arredondamento usam o Método dos Maiores Restos
 *   para garantir que nenhum slot se perca.
 */
class CronogramaService
{
    /*
    |--------------------------------------------------------------------------
    | CONSTANTES DE CONFIGURAÇÃO
    |--------------------------------------------------------------------------
    |
    | Centralizadas aqui para facilitar ajuste futuro sem alterar a lógica.
    | Em um cenário de produção, estas poderiam vir de config/cronograma.php.
    |
    */

    /**
     * Peso da relevância no cálculo do score de cada tópico.
     *
     * JUSTIFICATIVA: A relevância indica importância conceitual do tópico
     * na disciplina, mas nem sempre se traduz em questões diretas no ENEM.
     * Por isso recebe peso menor (40%).
     */
    private const PESO_RELEVANCIA = 0.4;

    /**
     * Peso da recorrência no cálculo do score de cada tópico.
     *
     * JUSTIFICATIVA: A recorrência mede quantas vezes o tópico já caiu no
     * ENEM nos últimos 20 anos. Como o ENEM é um exame com forte padrão de
     * repetição, temas recorrentes têm altíssima probabilidade de cair
     * novamente. Por isso recebe peso maior (60%).
     */
    private const PESO_RECORRENCIA = 0.6;

    /**
     * Percentual de slots reservados para simulados mensais.
     *
     * JUSTIFICATIVA: Os simulados são essenciais para treinar gestão de tempo,
     * resistência mental e identificar lacunas. 5% é suficiente para 1 dia
     * completo de simulado por mês sem comprometer o tempo de conteúdo novo.
     * Ex: em 300 slots, 15 slots vão para simulados (~3 simulados de 5h).
     */
    private const PERCENTUAL_SIMULADOS = 0.05;

    /**
     * Máximo de slots da mesma disciplina por dia.
     *
     * JUSTIFICATIVA: Estudar mais de 2 horas seguidas da mesma matéria causa
     * fadiga cognitiva e reduz a retenção. A prática intercalada (interleaving)
     * é comprovadamente superior para aprendizado de longo prazo.
     */
    private const MAX_DISCIPLINA_POR_DIA = 2;

    /**
     * Frequência da prática de redação: 1 vez a cada N dias de estudo.
     *
     * JUSTIFICATIVA: A redação do ENEM vale 1000 pontos (20% da nota total)
     * e é uma competência que melhora com prática regular. Uma redação por
     * semana (a cada 7 dias corridos) é o ritmo recomendado por professores
     * especializados. Usamos 7 como referência mas o cálculo real é baseado
     * em semanas de estudo efetivas.
     */
    private const FREQUENCIA_REDACAO_DIAS = 7;


    /*
    |--------------------------------------------------------------------------
    | LISTA DE FERIADOS NACIONAIS
    |--------------------------------------------------------------------------
    |
    | Retorna os feriados nacionais fixos + móveis para um dado ano.
    | Em produção, isso poderia vir de um banco de dados ou API externa.
    |
    | NOTA: Os feriados móveis (Carnaval, Sexta-feira Santa, Corpus Christi)
    | são calculados a partir da data da Páscoa usando o algoritmo de
    | Computus (Meeus/Jones/Butcher). O Carbon já possui easter_date().
    |
    */

    /**
     * Retorna Collection de datas (formato Y-m-d) dos feriados nacionais
     * que caem dentro do período informado.
     */
    private function getFeriadosNacionais(Carbon $inicio, Carbon $fim): Collection
    {
        Log::info('[CronogramaService] getFeriadosNacionais :: entrada', [
            'inicio' => $inicio->format('Y-m-d'),
            'fim'    => $fim->format('Y-m-d'),
        ]);

        $feriados = collect();

        // Itera por cada ano que o período abrange
        $anoInicio = $inicio->year;
        $anoFim    = $fim->year;

        for ($ano = $anoInicio; $ano <= $anoFim; $ano++) {

            // --- Feriados fixos ---
            $fixos = [
                "$ano-01-01", // Confraternização Universal
                "$ano-04-21", // Tiradentes
                "$ano-05-01", // Dia do Trabalho
                "$ano-09-07", // Independência do Brasil
                "$ano-10-12", // Nossa Senhora Aparecida
                "$ano-11-02", // Finados
                "$ano-11-15", // Proclamação da República
                "$ano-12-25", // Natal
            ];

            // --- Feriados móveis (baseados na Páscoa) ---
            // easter_date() retorna timestamp Unix da Páscoa para o ano dado
            $pascoa = Carbon::createFromTimestamp(easter_date($ano));

            $moveis = [
                $pascoa->copy()->subDays(47)->format('Y-m-d'), // Carnaval (segunda)
                $pascoa->copy()->subDays(46)->format('Y-m-d'), // Carnaval (terça)
                $pascoa->copy()->subDays(2)->format('Y-m-d'),  // Sexta-feira Santa
                $pascoa->format('Y-m-d'),                       // Páscoa (domingo)
                $pascoa->copy()->addDays(60)->format('Y-m-d'), // Corpus Christi
            ];

            $feriados = $feriados->merge($fixos)->merge($moveis);
        }

        // Filtra apenas os que caem dentro do período do cronograma
        $resultado = $feriados
            ->map(fn(string $data) => Carbon::parse($data))
            ->filter(fn(Carbon $data) => $data->between($inicio, $fim))
            ->map(fn(Carbon $data) => $data->format('Y-m-d'))
            ->values();

        Log::info('[CronogramaService] getFeriadosNacionais :: saída', [
            'total_feriados' => $resultado->count(),
            'feriados'       => $resultado->toArray(),
        ]);

        return $resultado;
    }


    /*
    |==========================================================================
    | MÉTODO PRINCIPAL — ORQUESTRA TODAS AS FASES
    |==========================================================================
    |
    | Este é o ponto de entrada do algoritmo. Recebe o DTO do formulário
    | e retorna o cronograma completo como array estruturado.
    |
    | Fluxo:
    |   1. Calcula dias disponíveis
    |   2. Distribui slots entre disciplinas
    |   3. Prioriza e aloca tópicos dentro de cada disciplina
    |   4. Monta a sequência intercalada e mapeia nos dias
    |   5. Insere simulados mensais e práticas de redação
    |   6. Monta o JSON de saída
    |
    */

    public function gerarCronograma(CronogramaInputDTO $input): array
    {
        Log::info('[CronogramaService] gerarCronograma :: entrada', [
            'data_inicio'             => $input->dataInicio->format('Y-m-d'),
            'data_fim'                => $input->dataFim->format('Y-m-d'),
            'horas_por_dia'           => $input->horasPorDia,
            'dias_semana'             => $input->diasSemana,
            'disciplinas_selecionadas'=> $input->disciplinasSelecionadas,
            'estudar_ferias'          => $input->estudarFerias,
            'estudar_feriados'        => $input->estudarFeriados,
        ]);

        // =====================================================================
        // FASE 1 — Cálculo do Tempo Disponível
        // =====================================================================
        $diasDisponiveis = $this->calcularDiasDisponiveis($input);
        $totalSlots      = $diasDisponiveis->count() * $input->horasPorDia;

        Log::info('[CronogramaService] gerarCronograma :: FASE 1 concluída', [
            'total_dias_disponiveis' => $diasDisponiveis->count(),
            'total_slots'            => $totalSlots,
        ]);

        // Validação de segurança: se não há dias disponíveis, não há cronograma
        if ($totalSlots === 0) {
            Log::warning('[CronogramaService] gerarCronograma :: abortado — sem dias disponíveis');
            return [
                'sucesso' => false,
                'erro'    => 'Nenhum dia de estudo disponível no período selecionado. Verifique os filtros de dias da semana, feriados e férias.',
            ];
        }

        // =====================================================================
        // FASE 5a — Reservar slots para simulados mensais
        // =====================================================================
        //
        // JUSTIFICATIVA de fazer isso ANTES da Fase 2:
        // Os simulados consomem slots do total. Se distribuíssemos primeiro e
        // depois tirássemos slots, teríamos que recalcular toda a proporção.
        // É mais limpo separar o "bolo" antes de fatiar.
        //
        $mesesEstudo        = $this->calcularMesesDeEstudo($diasDisponiveis);
        $totalSimulados     = max(1, $mesesEstudo); // mínimo 1 simulado
        $slotsPorSimulado   = $input->horasPorDia;  // 1 dia inteiro de simulado
        $slotsSimulados     = $totalSimulados * $slotsPorSimulado;

        // =====================================================================
        // FASE 5b — Reservar slots para prática semanal de redação
        // =====================================================================
        //
        // Calcula quantas semanas de estudo existem no período para definir
        // quantas práticas de redação serão agendadas (1 por semana).
        // Cada prática de redação ocupa 1 slot (1 hora).
        //
        $semanasEstudo      = $this->calcularSemanasDeEstudo($diasDisponiveis);
        $slotsRedacao       = $semanasEstudo; // 1 slot por semana

        // Slots efetivos para conteúdo das disciplinas
        $slotsConteudo = $totalSlots - $slotsSimulados - $slotsRedacao;

        Log::info('[CronogramaService] gerarCronograma :: FASE 5a/5b concluída (reservas)', [
            'meses_estudo'      => $mesesEstudo,
            'total_simulados'   => $totalSimulados,
            'slots_simulados'   => $slotsSimulados,
            'semanas_estudo'    => $semanasEstudo,
            'slots_redacao'     => $slotsRedacao,
            'slots_conteudo'    => $slotsConteudo,
        ]);

        if ($slotsConteudo <= 0) {
            Log::warning('[CronogramaService] gerarCronograma :: abortado — slotsConteudo <= 0');
            return [
                'sucesso' => false,
                'erro'    => 'Tempo insuficiente para montar o cronograma. Os simulados e práticas de redação consomem todo o tempo disponível. Aumente o período ou as horas por dia.',
            ];
        }

        // =====================================================================
        // FASE 2 — Distribuição de Slots entre Disciplinas
        // =====================================================================
        //
        // Remove 'redacao' das disciplinas regulares (terá tratamento especial).
        // As demais disciplinas competem pelos slotsConteudo proporcionalmente.
        //
        $disciplinasRegulares = collect($input->disciplinasSelecionadas)
            ->reject(fn(string $d) => $d === 'redacao')
            ->values()
            ->toArray();

        $distribuicaoDisciplinas = $this->distribuirSlotsDisciplinas(
            $disciplinasRegulares,
            $slotsConteudo
        );

        Log::info('[CronogramaService] gerarCronograma :: FASE 2 concluída', [
            'disciplinas_regulares'   => $disciplinasRegulares,
            'distribuicao_disciplinas'=> $distribuicaoDisciplinas,
        ]);

        // =====================================================================
        // FASE 3 — Priorização e Alocação dos Tópicos
        // =====================================================================
        $alocacaoTopicos = $this->priorizarEAlocarTopicos($distribuicaoDisciplinas);

        Log::info('[CronogramaService] gerarCronograma :: FASE 3 concluída', [
            'disciplinas_com_topicos' => array_map(fn($v) => [
                'total_incluidos' => $v['total_topicos_incluidos'],
                'total_excluidos' => $v['total_topicos_excluidos'],
                'slots_alocados'  => $v['slots_alocados'],
            ], $alocacaoTopicos),
        ]);

        // =====================================================================
        // FASE 4 — Montagem do Calendário
        // =====================================================================
        $sequencia  = $this->montarSequenciaIntercalada($alocacaoTopicos);

        Log::info('[CronogramaService] gerarCronograma :: FASE 4a concluída (sequência intercalada)', [
            'total_slots_na_sequencia' => $sequencia->count(),
        ]);

        $cronograma = $this->mapearSequenciaNoDias(
            $sequencia,
            $diasDisponiveis,
            $input->horasPorDia
        );

        Log::info('[CronogramaService] gerarCronograma :: FASE 4b concluída (mapeamento nos dias)', [
            'total_dias_no_cronograma' => $cronograma->count(),
        ]);

        // =====================================================================
        // FASE 5c — Inserir simulados e redações no calendário
        // =====================================================================
        $cronograma = $this->inserirSimulados($cronograma, $diasDisponiveis, $totalSimulados, $input->horasPorDia);

        Log::info('[CronogramaService] gerarCronograma :: FASE 5c simulados inseridos', [
            'total_simulados' => $totalSimulados,
        ]);

        $cronograma = $this->inserirRedacoes($cronograma, $diasDisponiveis, $semanasEstudo);

        Log::info('[CronogramaService] gerarCronograma :: FASE 5c redações inseridas', [
            'total_redacoes' => $semanasEstudo,
        ]);

        // =====================================================================
        // FASE 6 — Montagem da Saída Final
        // =====================================================================
        Log::info('[CronogramaService] gerarCronograma :: iniciando FASE 6 (saída final)');

        $saida = $this->montarSaidaFinal(
            $cronograma,
            $diasDisponiveis,
            $totalSlots,
            $slotsConteudo,
            $slotsSimulados,
            $slotsRedacao,
            $distribuicaoDisciplinas,
            $alocacaoTopicos,
            $totalSimulados,
            $semanasEstudo,
            $input
        );

        Log::info('[CronogramaService] gerarCronograma :: saída final', [
            'sucesso'                 => $saida['sucesso'] ?? false,
            'total_dias_estudo'       => $saida['resumo']['total_dias_estudo'] ?? null,
            'total_slots'             => $saida['resumo']['total_slots'] ?? null,
            'slots_conteudo'          => $saida['resumo']['slots_conteudo'] ?? null,
            'slots_simulados'         => $saida['resumo']['slots_simulados'] ?? null,
            'slots_redacao'           => $saida['resumo']['slots_redacao'] ?? null,
            'total_topicos_incluidos' => $saida['resumo']['total_topicos_incluidos'] ?? null,
            'total_alertas'           => \count($saida['alertas'] ?? []),
        ]);

        return $saida;
    }


    /*
    |==========================================================================
    | FASE 1 — CÁLCULO DO TEMPO DISPONÍVEL
    |==========================================================================
    |
    | Objetivo: Determinar todos os dias válidos para estudo no período.
    |
    | Estratégia: Gerar todos os dias entre data_inicio e data_fim usando
    | CarbonPeriod (iterador eficiente do Carbon), e aplicar 3 filtros
    | eliminatórios em cascata. A ordem dos filtros não importa logicamente,
    | mas colocar o filtro mais barato primeiro (dia da semana = O(1))
    | otimiza a performance para períodos longos.
    |
    | Retorna: Collection de strings no formato 'Y-m-d'
    |
    */

    private function calcularDiasDisponiveis(CronogramaInputDTO $input): Collection
    {
        Log::info('[CronogramaService] calcularDiasDisponiveis :: entrada', [
            'data_inicio'      => $input->dataInicio->format('Y-m-d'),
            'data_fim'         => $input->dataFim->format('Y-m-d'),
            'dias_semana'      => $input->diasSemana,
            'estudar_ferias'   => $input->estudarFerias,
            'estudar_feriados' => $input->estudarFeriados,
        ]);

        // CarbonPeriod gera um iterador dia a dia — não carrega tudo em memória
        $periodo = CarbonPeriod::create($input->dataInicio, $input->dataFim);

        // Pré-calcula os feriados uma vez para evitar recálculo em cada iteração.
        // O lookup via contains() em Collection é O(n), mas como feriados são
        // poucos (~15/ano), é negligível. Para otimização extrema, use um Set.
        $feriados = $this->getFeriadosNacionais($input->dataInicio, $input->dataFim);

        // Período de férias escolares: 21/jul a 04/ago de cada ano no intervalo
        $periodosFerias = $this->getPeriodosFerias($input->dataInicio, $input->dataFim);

        $diasDisponiveis = collect();

        foreach ($periodo as $dia) {
            // -----------------------------------------------------------------
            // FILTRO A — Dia da semana permitido?
            // -----------------------------------------------------------------
            // Carbon::dayOfWeek retorna 0=dom, 1=seg ... 6=sáb.
            // Comparamos com o array de dias selecionados pelo aluno.
            //
            // JUSTIFICATIVA: É o filtro mais rápido (comparação de inteiro)
            // e elimina ~28-43% dos dias (dependendo de quantos dias o aluno
            // selecionou), reduzindo o trabalho dos filtros seguintes.
            // -----------------------------------------------------------------
            if (! in_array($dia->dayOfWeek, $input->diasSemana)) {
                continue;
            }

            // -----------------------------------------------------------------
            // FILTRO B — Período de férias escolares?
            // -----------------------------------------------------------------
            // Se o aluno NÃO quer estudar nas férias E o dia cai no período
            // de férias (21/jul a 04/ago), descartamos o dia.
            //
            // JUSTIFICATIVA: As férias escolares são um período fixo e
            // previsível. Muitos alunos viajam ou descansam nesse período.
            // Permitir a escolha evita cronogramas irrealistas.
            // -----------------------------------------------------------------
            if (! $input->estudarFerias && $this->isDiaDeFerias($dia, $periodosFerias)) {
                continue;
            }

            // -----------------------------------------------------------------
            // FILTRO C — Feriado nacional?
            // -----------------------------------------------------------------
            // Se o aluno NÃO quer estudar em feriados E o dia é feriado,
            // descartamos o dia.
            //
            // JUSTIFICATIVA: Feriados frequentemente envolvem compromissos
            // familiares. Respeitar essa escolha torna o cronograma aderente
            // à realidade do aluno.
            // -----------------------------------------------------------------
            if (! $input->estudarFeriados && $feriados->contains($dia->format('Y-m-d'))) {
                continue;
            }

            // O dia sobreviveu aos 3 filtros — é um dia válido de estudo
            $diasDisponiveis->push($dia->format('Y-m-d'));
        }

        Log::info('[CronogramaService] calcularDiasDisponiveis :: saída', [
            'total_dias' => $diasDisponiveis->count(),
            'primeiro'   => $diasDisponiveis->first(),
            'ultimo'     => $diasDisponiveis->last(),
        ]);

        return $diasDisponiveis;
    }

    /**
     * Gera os períodos de férias escolares (21/jul a 04/ago) para cada ano
     * abrangido pelo período do cronograma.
     *
     * Retorna array de arrays [inicio, fim] como strings Y-m-d.
     */
    private function getPeriodosFerias(Carbon $inicio, Carbon $fim): array
    {
        $periodos = [];

        for ($ano = $inicio->year; $ano <= $fim->year; $ano++) {
            $periodos[] = [
                'inicio' => "$ano-07-21",
                'fim'    => "$ano-08-04",
            ];
        }

        return $periodos;
    }

    /**
     * Verifica se um dia cai em algum período de férias.
     */
    private function isDiaDeFerias(Carbon $dia, array $periodosFerias): bool
    {
        $diaStr = $dia->format('Y-m-d');

        foreach ($periodosFerias as $periodo) {
            if ($diaStr >= $periodo['inicio'] && $diaStr <= $periodo['fim']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula quantos meses completos de estudo existem no período.
     *
     * JUSTIFICATIVA: Usamos meses completos (não frações) porque um simulado
     * faz sentido apenas quando o aluno já acumulou ~30 dias de conteúdo.
     * Se restar uma fração >= 0.5 mês (15+ dias), incluímos mais um simulado.
     */
    private function calcularMesesDeEstudo(Collection $diasDisponiveis): int
    {
        Log::info('[CronogramaService] calcularMesesDeEstudo :: entrada', [
            'total_dias' => $diasDisponiveis->count(),
        ]);

        if ($diasDisponiveis->isEmpty()) {
            Log::info('[CronogramaService] calcularMesesDeEstudo :: saída (vazio)', ['meses' => 0]);
            return 0;
        }

        $primeiro = Carbon::parse($diasDisponiveis->first());
        $ultimo   = Carbon::parse($diasDisponiveis->last());

        $meses = $primeiro->diffInDays($ultimo) / 30;
        $resultado = (int) round($meses);

        Log::info('[CronogramaService] calcularMesesDeEstudo :: saída', [
            'primeiro'     => $primeiro->format('Y-m-d'),
            'ultimo'       => $ultimo->format('Y-m-d'),
            'diff_em_dias' => $primeiro->diffInDays($ultimo),
            'meses_float'  => $meses,
            'meses_final'  => $resultado,
        ]);

        // Se a fração restante é >= 15 dias, arredonda para cima
        return $resultado;
    }

    /**
     * Calcula quantas semanas de estudo existem no período.
     *
     * JUSTIFICATIVA: Cada semana terá 1 prática de redação.
     * Usamos semanas baseadas nos dias efetivos de estudo, não no calendário,
     * para que alunos com poucos dias/semana não acumulem redações demais.
     */
    private function calcularSemanasDeEstudo(Collection $diasDisponiveis): int
    {
        Log::info('[CronogramaService] calcularSemanasDeEstudo :: entrada', [
            'total_dias' => $diasDisponiveis->count(),
        ]);

        if ($diasDisponiveis->isEmpty()) {
            Log::info('[CronogramaService] calcularSemanasDeEstudo :: saída (vazio)', ['semanas' => 0]);
            return 0;
        }

        $primeiro = Carbon::parse($diasDisponiveis->first());
        $ultimo   = Carbon::parse($diasDisponiveis->last());

        $resultado = max(1, (int) ceil($primeiro->diffInDays($ultimo) / self::FREQUENCIA_REDACAO_DIAS));

        Log::info('[CronogramaService] calcularSemanasDeEstudo :: saída', [
            'primeiro'     => $primeiro->format('Y-m-d'),
            'ultimo'       => $ultimo->format('Y-m-d'),
            'diff_em_dias' => $primeiro->diffInDays($ultimo),
            'semanas'      => $resultado,
        ]);

        return $resultado;
    }


    /*
    |==========================================================================
    | FASE 2 — DISTRIBUIÇÃO DE SLOTS ENTRE DISCIPLINAS
    |==========================================================================
    |
    | Objetivo: Decidir quantos slots cada disciplina recebe, proporcional
    | à soma dos scores de seus tópicos.
    |
    | Estratégia:
    |   1. Para cada disciplina, somar (relevancia + recorrencia) de todos
    |      os seus tópicos. Isso dá o "peso bruto" da disciplina.
    |   2. Calcular a fração de cada disciplina sobre o peso total.
    |   3. Multiplicar a fração pelo total de slots disponíveis.
    |   4. Arredondar usando o Método dos Maiores Restos.
    |
    | JUSTIFICATIVA do peso bruto ser a SOMA (e não a média):
    | Uma disciplina com 30 tópicos relevantes precisa de mais tempo do que
    | uma com 8 tópicos igualmente relevantes. A soma captura tanto a
    | quantidade de conteúdo quanto a importância agregada. Se usássemos
    | a média, Filosofia (poucos tópicos, alta média) receberia proporção
    | similar a Matemática (muitos tópicos, alta média), o que seria injusto.
    |
    | Retorna: array associativo [slug_disciplina => ['slots' => int, 'peso' => float, ...]]
    |
    */

    private function distribuirSlotsDisciplinas(array $disciplinas, int $slotsConteudo): array
    {
        Log::info('[CronogramaService] distribuirSlotsDisciplinas :: entrada', [
            'disciplinas'    => $disciplinas,
            'slots_conteudo' => $slotsConteudo,
        ]);

        // -----------------------------------------------------------------
        // Passo 2.1 — Calcular peso bruto de cada disciplina
        // -----------------------------------------------------------------
        // Consulta o banco agrupando por disciplina e somando os scores.
        //
        // NOTA: Ajuste 'topicos' para o nome real da sua tabela e
        // 'disciplina_slug' para sua coluna de identificação.
        // -----------------------------------------------------------------
        $pesosDisciplinas = DB::table('topicos')
            ->select(
                'disciplina_slug',
                DB::raw('SUM(relevancia + recorrencia) as peso_bruto'),
                DB::raw('COUNT(*) as total_topicos')
            )
            ->whereIn('disciplina_slug', $disciplinas)
            ->groupBy('disciplina_slug')
            ->get()
            ->keyBy('disciplina_slug');

        // -----------------------------------------------------------------
        // Passo 2.2 — Calcular peso total e fração de cada disciplina
        // -----------------------------------------------------------------
        $pesoTotal = $pesosDisciplinas->sum('peso_bruto');

        Log::info('[CronogramaService] distribuirSlotsDisciplinas :: pesos consultados do banco', [
            'peso_total'         => $pesoTotal,
            'disciplinas_no_banco' => $pesosDisciplinas->keys()->toArray(),
        ]);

        if ($pesoTotal == 0) {
            // Fallback: se não há tópicos no banco, distribuir igualmente
            $slotsPorDisciplina = intdiv($slotsConteudo, \count($disciplinas));
            $resultado = [];
            foreach ($disciplinas as $d) {
                $resultado[$d] = [
                    'slots'          => $slotsPorDisciplina,
                    'peso_bruto'     => 0,
                    'total_topicos'  => 0,
                    'fracao'         => 1 / \count($disciplinas),
                ];
            }
            Log::warning('[CronogramaService] distribuirSlotsDisciplinas :: peso_total=0, distribuição igualitária aplicada', [
                'slots_por_disciplina' => $slotsPorDisciplina,
            ]);
            return $resultado;
        }

        // -----------------------------------------------------------------
        // Passo 2.3 — Converter fração em slots com Método dos Maiores Restos
        // -----------------------------------------------------------------
        //
        // O MÉTODO DOS MAIORES RESTOS funciona assim:
        //
        // 1. Calcular o valor fracionário exato para cada disciplina:
        //    slots_exato = fração × total_slots
        //
        // 2. Atribuir floor(slots_exato) para cada uma. Isso garante que
        //    ninguém recebe mais do que merece.
        //
        // 3. Calcular o "resto" de cada uma: resto = slots_exato - floor(slots_exato)
        //
        // 4. Somar todos os floor() — a soma será menor que total_slots.
        //    A diferença são os slots "sobrando".
        //
        // 5. Distribuir os slots sobrando, um a um, para as disciplinas
        //    com maior resto. Isso é justo porque o resto grande significa
        //    que a disciplina foi a mais "prejudicada" pelo arredondamento.
        //
        // JUSTIFICATIVA: Este método é usado em sistemas eleitorais
        // proporcionais (método Hamilton) e garante que a soma final
        // seja exatamente igual ao total, sem slots perdidos.
        // -----------------------------------------------------------------

        $distribuicao = [];

        foreach ($pesosDisciplinas as $slug => $info) {
            $fracao       = $info->peso_bruto / $pesoTotal;
            $slotsExato   = $fracao * $slotsConteudo;
            $slotsFloor   = (int) floor($slotsExato);
            $resto        = $slotsExato - $slotsFloor;

            $distribuicao[$slug] = [
                'slots'          => $slotsFloor,
                'slots_exato'    => $slotsExato,
                'resto'          => $resto,
                'peso_bruto'     => $info->peso_bruto,
                'total_topicos'  => $info->total_topicos,
                'fracao'         => $fracao,
            ];
        }

        // Calcular quantos slots faltam após o floor
        $slotsFaltantes = $slotsConteudo - collect($distribuicao)->sum('slots');

        // Ordenar por maior resto e distribuir os slots faltantes
        $ordenadoPorResto = collect($distribuicao)
            ->sortByDesc('resto')
            ->keys()
            ->toArray();

        for ($i = 0; $i < $slotsFaltantes; $i++) {
            $slug = $ordenadoPorResto[$i % count($ordenadoPorResto)];
            $distribuicao[$slug]['slots']++;
        }

        // Limpar campos auxiliares do cálculo
        foreach ($distribuicao as &$info) {
            unset($info['slots_exato'], $info['resto']);
        }

        Log::info('[CronogramaService] distribuirSlotsDisciplinas :: saída', [
            'distribuicao' => array_map(fn($v) => [
                'slots'         => $v['slots'],
                'peso_bruto'    => $v['peso_bruto'],
                'total_topicos' => $v['total_topicos'],
                'fracao'        => round($v['fracao'] * 100, 2) . '%',
            ], $distribuicao),
            'soma_slots' => array_sum(array_column($distribuicao, 'slots')),
        ]);

        return $distribuicao;
    }


    /*
    |==========================================================================
    | FASE 3 — PRIORIZAÇÃO E ALOCAÇÃO DOS TÓPICOS
    |==========================================================================
    |
    | Objetivo: Dentro de cada disciplina, calcular o score de prioridade
    | de cada tópico, ordenar do mais ao menos importante, e distribuir
    | os slots disponíveis entre eles.
    |
    | O score de cada tópico é calculado como:
    |   score = (relevancia × 0.4) + (recorrencia × 0.6)
    |
    | JUSTIFICATIVA dos pesos:
    | - Recorrência (0.6): O ENEM repete temas com frequência previsível.
    |   Se "Razão e Proporção" caiu 18/20 vezes, há altíssima chance de cair
    |   novamente. É o preditor mais forte de presença na prova.
    | - Relevância (0.4): Indica importância conceitual e base para outros
    |   temas. Um tópico pode ser relevante (ex: Termodinâmica) mas ter
    |   aparecido menos vezes no ENEM por ser abordado indiretamente.
    |
    | A distribuição de slots dentro da disciplina segue a mesma lógica
    | proporcional da Fase 2, garantindo que tópicos mais importantes
    | recebam mais tempo de estudo.
    |
    | Se houver mais tópicos do que slots, os tópicos de menor score são
    | CORTADOS e retornados como alerta ao aluno.
    |
    */

    private function priorizarEAlocarTopicos(array $distribuicaoDisciplinas): array
    {
        Log::info('[CronogramaService] priorizarEAlocarTopicos :: entrada', [
            'disciplinas' => array_keys($distribuicaoDisciplinas),
        ]);

        $resultado = [];

        foreach ($distribuicaoDisciplinas as $slug => $infoDisciplina) {
            $slotsDisponiveis = $infoDisciplina['slots'];

            // -----------------------------------------------------------------
            // Passo 3.1 — Buscar tópicos da disciplina e calcular score
            // -----------------------------------------------------------------
            $topicos = DB::table('topicos')
                ->where('disciplina_slug', $slug)
                ->get()
                ->map(function ($topico) {
                    // Cálculo do score ponderado
                    $topico->score = round(
                        ($topico->relevancia * self::PESO_RELEVANCIA)
                        + ($topico->recorrencia * self::PESO_RECORRENCIA),
                        2
                    );
                    return $topico;
                });

            // -----------------------------------------------------------------
            // Passo 3.2 — Ordenar tópicos por score decrescente
            // -----------------------------------------------------------------
            // Essa ordenação define a FILA DE PRIORIDADE:
            // os tópicos do topo são os primeiros a entrar no cronograma.
            // Em caso de empate no score, a ordem do banco é mantida (stable sort).
            // -----------------------------------------------------------------
            $topicos = $topicos->sortByDesc('score')->values();

            // -----------------------------------------------------------------
            // Passo 3.4 — Validação de excesso (antes da distribuição)
            // -----------------------------------------------------------------
            //
            // Se a disciplina tem mais tópicos do que slots, cortamos os de
            // menor score. Esses tópicos NÃO CABEM no cronograma.
            //
            // JUSTIFICATIVA: É melhor estudar bem os tópicos mais importantes
            // do que pincelar superficialmente todos. Os tópicos cortados são
            // retornados como feedback ao aluno com a sugestão de aumentar
            // o tempo de estudo.
            //
            // NOTA: O mínimo de 1 slot por tópico é a regra. Se um tópico
            // entrou no cronograma, ele recebe pelo menos 1 hora.
            // -----------------------------------------------------------------
            $topicosExcluidos = collect();

            if ($topicos->count() > $slotsDisponiveis) {
                $topicosExcluidos = $topicos->slice($slotsDisponiveis)->values();
                $topicos          = $topicos->take($slotsDisponiveis)->values();
            }

            // -----------------------------------------------------------------
            // Passo 3.3 — Distribuir slots entre os tópicos incluídos
            // -----------------------------------------------------------------
            // Usa a mesma lógica proporcional da Fase 2 (Maiores Restos).
            //
            // O score de cada tópico define sua fração do bolo de slots
            // da disciplina. Tópicos com score alto ganham mais horas.
            //
            // Regra de mínimo: todo tópico incluído recebe pelo menos 1 slot.
            // Isso é garantido pelo corte de excesso acima — se entrou na
            // lista, há slots suficientes para dar 1 a cada um.
            // -----------------------------------------------------------------
            $scoreTotal = $topicos->sum('score');

            if ($scoreTotal > 0 && $slotsDisponiveis > 0) {
                $topicosComSlots = $this->distribuirSlotsProporcional(
                    $topicos,
                    $slotsDisponiveis,
                    $scoreTotal
                );
            } else {
                $topicosComSlots = collect();
            }

            Log::info("[CronogramaService] priorizarEAlocarTopicos :: disciplina '{$slug}'", [
                'slots_disponiveis'      => $slotsDisponiveis,
                'total_topicos_banco'    => $topicos->count(),
                'total_topicos_incluidos'=> $topicosComSlots->count(),
                'total_topicos_excluidos'=> $topicosExcluidos->count(),
                'score_total'            => $scoreTotal,
                'topicos_incluidos'      => $topicosComSlots->map(fn($t) => [
                    'id'             => $t->id,
                    'nome'           => $t->nome,
                    'score'          => $t->score,
                    'slots_alocados' => $t->slots_alocados,
                ])->toArray(),
                'topicos_excluidos'      => $topicosExcluidos->pluck('nome')->toArray(),
            ]);

            $resultado[$slug] = [
                'topicos_incluidos'       => $topicosComSlots,
                'topicos_excluidos'       => $topicosExcluidos,
                'total_topicos_incluidos' => $topicosComSlots->count(),
                'total_topicos_excluidos' => $topicosExcluidos->count(),
                'slots_alocados'          => $infoDisciplina['slots'],
            ];
        }

        Log::info('[CronogramaService] priorizarEAlocarTopicos :: saída', [
            'disciplinas_processadas' => \count($resultado),
        ]);

        return $resultado;
    }

    /**
     * Distribui N slots entre items proporcionalmente ao score de cada um,
     * usando o Método dos Maiores Restos.
     *
     * Garante que todo item receba pelo menos 1 slot (minimo).
     *
     * @param  Collection  $items  Coleção de objetos com propriedade ->score
     * @param  int         $totalSlots  Total de slots a distribuir
     * @param  float       $scoreTotal  Soma dos scores de todos os items
     * @return Collection  Items com propriedade ->slots_alocados adicionada
     */
    private function distribuirSlotsProporcional(Collection $items, int $totalSlots, float $scoreTotal): Collection
    {
        Log::info('[CronogramaService] distribuirSlotsProporcional :: entrada', [
            'total_items'  => $items->count(),
            'total_slots'  => $totalSlots,
            'score_total'  => $scoreTotal,
        ]);

        // Passo 1: calcular floor e resto para cada item
        $items = $items->map(function ($item) use ($totalSlots, $scoreTotal) {
            $fracao          = $item->score / $scoreTotal;
            $slotsExato      = $fracao * $totalSlots;
            $slotsFloor      = max(1, (int) floor($slotsExato)); // mínimo 1

            $item->slots_alocados = $slotsFloor;
            $item->_resto         = $slotsExato - $slotsFloor;

            return $item;
        });

        // Passo 2: verificar se a soma dos floors excede o total
        // (pode acontecer quando o mínimo de 1 é aplicado a muitos itens)
        $somaAtual = $items->sum('slots_alocados');

        if ($somaAtual > $totalSlots) {
            // Se excedeu, remover o excesso dos items de menor score
            // (já estão ordenados por score desc, então remove do final)
            $excesso = $somaAtual - $totalSlots;
            $items   = $items->reverse()->values();

            for ($i = 0; $i < $items->count() && $excesso > 0; $i++) {
                if ($items[$i]->slots_alocados > 1) {
                    $reducao = min($items[$i]->slots_alocados - 1, $excesso);
                    $items[$i]->slots_alocados -= $reducao;
                    $excesso -= $reducao;
                }
            }

            $items = $items->reverse()->values();
        }

        // Passo 3: distribuir slots faltantes pelos maiores restos
        $slotsFaltantes = $totalSlots - $items->sum('slots_alocados');

        if ($slotsFaltantes > 0) {
            $ordenados = $items->sortByDesc('_resto')->values();

            for ($i = 0; $i < $slotsFaltantes; $i++) {
                $idx = $i % $ordenados->count();
                $ordenados[$idx]->slots_alocados++;
            }
        }

        // Limpeza: remover campo auxiliar
        $resultado = $items->map(function ($item) {
            unset($item->_resto);
            return $item;
        });

        Log::info('[CronogramaService] distribuirSlotsProporcional :: saída', [
            'soma_slots_alocados' => $resultado->sum('slots_alocados'),
            'items' => $resultado->map(fn($i) => [
                'id'             => $i->id ?? null,
                'nome'           => $i->nome ?? null,
                'score'          => $i->score ?? null,
                'slots_alocados' => $i->slots_alocados,
            ])->toArray(),
        ]);

        return $resultado;
    }


    /*
    |==========================================================================
    | FASE 4 — MONTAGEM DO CALENDÁRIO
    |==========================================================================
    |
    | Objetivo: Distribuir os tópicos nos dias concretos do calendário
    | usando intercalação de disciplinas (round-robin ponderado).
    |
    | PARTE 4a — Montar a sequência intercalada
    |
    | Estratégia: Criar um array linear (sequencia_disciplinas[]) onde cada
    | posição representa 1 slot de 1 hora. As disciplinas se alternam
    | proporcionalmente — quem tem mais slots aparece mais vezes, mas nunca
    | em blocos consecutivos longos.
    |
    | JUSTIFICATIVA CIENTÍFICA: A prática intercalada (interleaving) é
    | comprovadamente superior à prática em blocos (blocking) para retenção
    | de longo prazo. Estudos de Rohrer & Taylor (2007) mostram que
    | intercalar assuntos durante o estudo melhora em ~43% a performance
    | em testes posteriores comparado a estudar em blocos.
    |
    */

    /**
     * Monta a sequência linear de slots intercalando disciplinas.
     *
     * O algoritmo percorre as disciplinas em ordem decrescente de peso
     * (quem tem mais slots é "servida" primeiro em cada rodada) e vai
     * preenchendo a sequência até esgotar todos os slots.
     *
     * Cada item da sequência contém:
     * - disciplina: slug da disciplina
     * - topico: objeto do tópico a ser estudado
     * - tipo: 'conteudo_novo'
     *
     * @return Collection  Sequência linear de slots
     */
    private function montarSequenciaIntercalada(array $alocacaoTopicos): Collection
    {
        Log::info('[CronogramaService] montarSequenciaIntercalada :: entrada', [
            'disciplinas' => array_keys($alocacaoTopicos),
        ]);

        $sequencia = collect();

        // Preparar filas de tópicos por disciplina.
        // Cada disciplina tem uma fila (queue) de seus tópicos ordenados por score.
        // Cada tópico aparece na fila tantas vezes quanto seus slots_alocados.
        //
        // Exemplo: Se "Razão e Proporção" tem 3 slots_alocados, aparece 3x na fila.
        // Isso significa que o aluno estudará esse tópico em 3 sessões diferentes,
        // aprofundando a cada sessão.

        $filas     = [];
        $slotsRest = []; // slots restantes por disciplina

        // Ordenar disciplinas por total de slots (desc) para o round-robin
        $disciplinasOrdenadas = collect($alocacaoTopicos)
            ->sortByDesc('slots_alocados')
            ->keys()
            ->toArray();

        foreach ($disciplinasOrdenadas as $slug) {
            $info = $alocacaoTopicos[$slug];
            $fila = collect();

            foreach ($info['topicos_incluidos'] as $topico) {
                // Cada tópico entra na fila N vezes (N = slots_alocados)
                for ($i = 0; $i < $topico->slots_alocados; $i++) {
                    $fila->push($topico);
                }
            }

            $filas[$slug]     = $fila;
            $slotsRest[$slug] = $info['slots_alocados'];
        }

        // -----------------------------------------------------------------
        // Algoritmo Round-Robin Ponderado
        // -----------------------------------------------------------------
        //
        // Em cada "rodada", percorremos todas as disciplinas (da com mais
        // slots para a com menos) e adicionamos 1 slot de cada uma à
        // sequência, se ela ainda tiver slots restantes.
        //
        // Isso garante que:
        // 1. Disciplinas com mais slots aparecem em mais rodadas
        // 2. Nunca há mais do que 1 slot consecutivo da mesma disciplina
        //    (exceto quando restam poucas disciplinas no final)
        // 3. A alternância é natural e previsível
        //
        // É como distribuir cartas: cada disciplina recebe 1 carta por rodada,
        // até acabarem as cartas de cada uma.
        // -----------------------------------------------------------------

        Log::info('[CronogramaService] montarSequenciaIntercalada :: filas montadas', [
            'slots_por_disciplina' => $slotsRest,
        ]);

        $totalSlotsRestantes = array_sum($slotsRest);

        while ($totalSlotsRestantes > 0) {
            foreach ($disciplinasOrdenadas as $slug) {
                if ($slotsRest[$slug] > 0 && $filas[$slug]->isNotEmpty()) {
                    $topico = $filas[$slug]->shift(); // retira o primeiro da fila

                    $sequencia->push([
                        'disciplina' => $slug,
                        'topico_id'  => $topico->id,
                        'topico_nome'=> $topico->nome,
                        'score'      => $topico->score,
                        'tipo'       => 'conteudo_novo',
                    ]);

                    $slotsRest[$slug]--;
                    $totalSlotsRestantes--;
                }
            }
        }

        Log::info('[CronogramaService] montarSequenciaIntercalada :: saída', [
            'total_slots_na_sequencia' => $sequencia->count(),
            'primeiros_5_slots'        => $sequencia->take(5)->toArray(),
        ]);

        return $sequencia;
    }

    /*
    |--------------------------------------------------------------------------
    | FASE 4b — Mapear a Sequência nos Dias do Calendário
    |--------------------------------------------------------------------------
    |
    | Objetivo: Pegar a sequência linear (ex: 300 slots) e "cortar" em
    | pedaços de N slots (horas_por_dia), alocando cada pedaço a um dia.
    |
    | Também aplica a regra de limite de disciplina por dia:
    | máximo MAX_DISCIPLINA_POR_DIA slots da mesma disciplina no mesmo dia.
    |
    | Se o limite é violado (ex: aluno tem 5h e 3 disciplinas), tenta trocar
    | com o próximo slot de outra disciplina. Se não for possível (poucas
    | disciplinas), aceita a violação — é melhor estudar 3h de Matemática
    | em um dia do que deixar 1h vazia.
    |
    */

    private function mapearSequenciaNoDias(
        Collection $sequencia,
        Collection $diasDisponiveis,
        int $horasPorDia
    ): Collection {
        Log::info('[CronogramaService] mapearSequenciaNoDias :: entrada', [
            'total_slots_sequencia' => $sequencia->count(),
            'total_dias'            => $diasDisponiveis->count(),
            'horas_por_dia'         => $horasPorDia,
        ]);

        $cronograma       = collect();
        $indiceSequencia  = 0;
        $totalSlots       = $sequencia->count();

        foreach ($diasDisponiveis as $diaStr) {
            $aulas = [];
            $contadorDisciplinaDia = []; // [slug => count] para controle do limite

            for ($slot = 0; $slot < $horasPorDia; $slot++) {
                if ($indiceSequencia >= $totalSlots) {
                    break; // acabaram os slots de conteúdo
                }

                $itemAtual = $sequencia[$indiceSequencia];
                $slugAtual = $itemAtual['disciplina'];

                // Inicializa contador da disciplina neste dia
                if (! isset($contadorDisciplinaDia[$slugAtual])) {
                    $contadorDisciplinaDia[$slugAtual] = 0;
                }

                // ---------------------------------------------------------------
                // Verificação do limite de disciplina por dia
                // ---------------------------------------------------------------
                // Se esta disciplina já atingiu o máximo neste dia, tenta trocar
                // com o próximo slot de uma disciplina diferente.
                //
                // A troca acontece "olhando para frente" na sequência: buscamos
                // o próximo slot que seja de uma disciplina ainda não esgotada
                // neste dia, e fazemos swap.
                //
                // Se não encontrar nenhum candidato (ex: só restam slots de
                // Matemática), aceita a violação. É um cenário de edge case
                // que só ocorre com poucas disciplinas e muitas horas/dia.
                // ---------------------------------------------------------------
                if ($contadorDisciplinaDia[$slugAtual] >= self::MAX_DISCIPLINA_POR_DIA) {
                    $trocou = false;

                    // Buscar candidato para troca (olha até 10 posições à frente)
                    $limiteOlhar = min($indiceSequencia + 10, $totalSlots);
                    for ($j = $indiceSequencia + 1; $j < $limiteOlhar; $j++) {
                        $candidato     = $sequencia[$j];
                        $slugCandidato = $candidato['disciplina'];

                        $contCandidato = $contadorDisciplinaDia[$slugCandidato] ?? 0;

                        if ($contCandidato < self::MAX_DISCIPLINA_POR_DIA) {
                            // Swap: troca as posições na sequência
                            $sequencia[$indiceSequencia] = $candidato;
                            $sequencia[$j]               = $itemAtual;

                            $itemAtual = $candidato;
                            $slugAtual = $slugCandidato;

                            if (! isset($contadorDisciplinaDia[$slugAtual])) {
                                $contadorDisciplinaDia[$slugAtual] = 0;
                            }

                            $trocou = true;
                            break;
                        }
                    }

                    // Se não trocou, aceita a violação (edge case)
                }

                $contadorDisciplinaDia[$slugAtual]++;

                $aulas[] = [
                    'slot'       => $slot + 1,
                    'disciplina' => $slugAtual,
                    'topico_id'  => $itemAtual['topico_id'],
                    'topico'     => $itemAtual['topico_nome'],
                    'tipo'       => $itemAtual['tipo'],
                    'score'      => $itemAtual['score'],
                ];

                $indiceSequencia++;
            }

            $dia = Carbon::parse($diaStr);

            $cronograma->push([
                'data'       => $diaStr,
                'dia_semana' => $this->traduzirDiaSemana($dia->dayOfWeek),
                'aulas'      => $aulas,
            ]);
        }

        Log::info('[CronogramaService] mapearSequenciaNoDias :: saída', [
            'total_dias_no_cronograma' => $cronograma->count(),
            'slots_consumidos'         => $indiceSequencia,
            'slots_nao_alocados'       => $sequencia->count() - $indiceSequencia,
        ]);

        return $cronograma;
    }


    /*
    |==========================================================================
    | FASE 5 — SIMULADOS MENSAIS E PRÁTICA DE REDAÇÃO
    |==========================================================================
    |
    | Objetivo: Inserir no cronograma já montado:
    |   a) 1 simulado completo por mês de estudo
    |   b) 1 prática de redação por semana
    |
    | JUSTIFICATIVA DOS SIMULADOS:
    | Simulados são essenciais no preparo para o ENEM por 3 razões:
    | 1. Treino de gestão de tempo (5h30 de prova com 90 questões + redação)
    | 2. Prática de resistência mental (maratona cognitiva)
    | 3. Diagnóstico de lacunas (mostra quais disciplinas estão fracas)
    |
    | O simulado ocupa 1 dia INTEIRO de estudo (todos os slots daquele dia).
    | É agendado no último dia de estudo de cada mês, pois o aluno terá
    | acumulado ~30 dias de conteúdo e está pronto para ser avaliado.
    |
    | JUSTIFICATIVA DA REDAÇÃO SEMANAL:
    | A redação do ENEM vale 1000 pontos (20% da nota) e exige prática
    | constante para desenvolver as 5 competências avaliadas. Uma redação
    | por semana é o ritmo recomendado por especialistas porque:
    | 1. Permite receber feedback e corrigir erros antes da próxima
    | 2. Desenvolve fluência argumentativa gradualmente
    | 3. Não sobrecarrega (redação exige alto esforço cognitivo)
    |
    */

    /**
     * Insere simulados mensais no cronograma.
     *
     * Estratégia: Encontra o último dia de estudo de cada mês e substitui
     * todas as aulas daquele dia por um bloco de simulado.
     *
     * Se não houver dia de estudo no final do mês, pega o dia disponível
     * mais próximo do final do mês.
     */
    private function inserirSimulados(
        Collection $cronograma,
        Collection $diasDisponiveis,
        int $totalSimulados,
        int $horasPorDia
    ): Collection {
        Log::info('[CronogramaService] inserirSimulados :: entrada', [
            'total_simulados' => $totalSimulados,
            'horas_por_dia'   => $horasPorDia,
            'total_dias'      => $cronograma->count(),
        ]);

        if ($totalSimulados <= 0 || $cronograma->isEmpty()) {
            Log::info('[CronogramaService] inserirSimulados :: sem simulados para inserir, retornando sem alteração');
            return $cronograma;
        }

        // -----------------------------------------------------------------
        // Identificar os dias de simulado
        // -----------------------------------------------------------------
        // Agrupa os dias disponíveis por mês (YYYY-MM) e pega o último
        // dia de cada grupo. Limita ao número de simulados calculado.
        // -----------------------------------------------------------------
        $diasPorMes = $diasDisponiveis
            ->groupBy(fn(string $dia) => Carbon::parse($dia)->format('Y-m'))
            ->map(fn(Collection $dias) => $dias->last()); // último dia do mês

        // Remove o primeiro mês (o aluno precisa de conteúdo antes do 1º simulado)
        $diasSimulado = $diasPorMes->values()->skip(1)->take($totalSimulados)->values();

        // Se não conseguiu pular o primeiro mês (período muito curto),
        // usa o último dia disponível como único simulado
        if ($diasSimulado->isEmpty() && $totalSimulados > 0) {
            $diasSimulado = collect([$diasDisponiveis->last()]);
        }

        // -----------------------------------------------------------------
        // Substituir as aulas dos dias de simulado
        // -----------------------------------------------------------------
        Log::info('[CronogramaService] inserirSimulados :: dias de simulado identificados', [
            'dias_simulado' => $diasSimulado->toArray(),
        ]);

        $datasSimulado = $diasSimulado->toArray();
        $numSimulado   = 1;

        return $cronograma->map(function (array $dia) use (&$numSimulado, $datasSimulado, $horasPorDia) {
            if (in_array($dia['data'], $datasSimulado)) {
                // Substitui todas as aulas do dia por slots de simulado
                $aulasSimulado = [];

                for ($slot = 1; $slot <= $horasPorDia; $slot++) {
                    $aulasSimulado[] = [
                        'slot'       => $slot,
                        'disciplina' => 'simulado',
                        'topico_id'  => null,
                        'topico'     => "Simulado #{$numSimulado} — Todas as disciplinas",
                        'tipo'       => 'simulado',
                        'score'      => null,
                    ];
                }

                $dia['aulas'] = $aulasSimulado;
                $numSimulado++;
            }

            return $dia;
        });
    }

    /**
     * Insere práticas semanais de redação no cronograma.
     *
     * Estratégia: A cada 7 dias corridos de distância no cronograma,
     * substitui o ÚLTIMO slot do dia por uma prática de redação.
     *
     * JUSTIFICATIVA de ser o último slot do dia:
     * - A redação é uma atividade de alta demanda cognitiva que funciona
     *   melhor como "atividade de fechamento" do dia de estudos.
     * - Não interrompe a sequência de conteúdo no meio do dia.
     * - O aluno termina o dia com uma atividade produtiva diferente,
     *   evitando a monotonia.
     */
    private function inserirRedacoes(
        Collection $cronograma,
        Collection $diasDisponiveis,
        int $totalRedacoes
    ): Collection {
        Log::info('[CronogramaService] inserirRedacoes :: entrada', [
            'total_redacoes' => $totalRedacoes,
            'total_dias'     => $cronograma->count(),
        ]);

        if ($totalRedacoes <= 0 || $cronograma->isEmpty()) {
            Log::info('[CronogramaService] inserirRedacoes :: sem redações para inserir, retornando sem alteração');
            return $cronograma;
        }

        // -----------------------------------------------------------------
        // Identificar os dias de redação
        // -----------------------------------------------------------------
        // Distribui uniformemente ao longo do período, com espaçamento
        // de ~7 dias corridos entre cada prática.
        // -----------------------------------------------------------------
        $totalDias       = $diasDisponiveis->count();
        $intervalo       = max(1, (int) floor($totalDias / $totalRedacoes));
        $diasRedacao     = [];
        $numRedacao      = 1;

        for ($i = $intervalo - 1; $i < $totalDias && $numRedacao <= $totalRedacoes; $i += $intervalo) {
            $diasRedacao[] = $diasDisponiveis[$i];
            $numRedacao++;
        }

        Log::info('[CronogramaService] inserirRedacoes :: dias de redação identificados', [
            'intervalo'    => $intervalo,
            'dias_redacao' => $diasRedacao,
        ]);

        // -----------------------------------------------------------------
        // Substituir o último slot dos dias de redação
        // -----------------------------------------------------------------
        return $cronograma->map(function (array $dia) use ($diasRedacao) {
            if (in_array($dia['data'], $diasRedacao)) {
                // Não coloca redação em dia de simulado
                if (! empty($dia['aulas']) && $dia['aulas'][0]['tipo'] === 'simulado') {
                    return $dia;
                }

                // Substitui o último slot por prática de redação
                if (! empty($dia['aulas'])) {
                    $ultimoIdx = count($dia['aulas']) - 1;
                    $dia['aulas'][$ultimoIdx] = [
                        'slot'       => $dia['aulas'][$ultimoIdx]['slot'],
                        'disciplina' => 'redacao',
                        'topico_id'  => null,
                        'topico'     => 'Prática de Redação — Modelo ENEM',
                        'tipo'       => 'redacao',
                        'score'      => null,
                    ];
                }
            }

            return $dia;
        });
    }


    /*
    |==========================================================================
    | FASE 6 — MONTAGEM DA SAÍDA FINAL
    |==========================================================================
    |
    | Objetivo: Consolidar todos os dados em uma estrutura JSON padronizada
    | que pode ser consumida pelo frontend, salva no banco, ou exportada.
    |
    | A saída contém 3 blocos principais:
    |
    | 1. RESUMO — Números agregados para o dashboard do aluno
    | 2. CRONOGRAMA — Array dia a dia com todas as aulas
    | 3. ALERTAS — Mensagens importantes sobre tópicos cortados,
    |    sugestões de melhoria, etc.
    |
    */

    private function montarSaidaFinal(
        Collection $cronograma,
        Collection $diasDisponiveis,
        int $totalSlots,
        int $slotsConteudo,
        int $slotsSimulados,
        int $slotsRedacao,
        array $distribuicaoDisciplinas,
        array $alocacaoTopicos,
        int $totalSimulados,
        int $semanasEstudo,
        CronogramaInputDTO $input
    ): array {
        Log::info('[CronogramaService] montarSaidaFinal :: entrada', [
            'total_slots'      => $totalSlots,
            'slots_conteudo'   => $slotsConteudo,
            'slots_simulados'  => $slotsSimulados,
            'slots_redacao'    => $slotsRedacao,
            'total_simulados'  => $totalSimulados,
            'semanas_estudo'   => $semanasEstudo,
            'total_dias'       => $diasDisponiveis->count(),
            'disciplinas'      => array_keys($distribuicaoDisciplinas),
        ]);

        $alertas = [];

        // -----------------------------------------------------------------
        // Gerar resumo por disciplina
        // -----------------------------------------------------------------
        $resumoDisciplinas = [];

        foreach ($distribuicaoDisciplinas as $slug => $info) {
            $alocacao = $alocacaoTopicos[$slug] ?? null;

            $disciplinaResumo = [
                'slug'                    => $slug,
                'slots_alocados'          => $info['slots'],
                'peso_bruto'              => $info['peso_bruto'],
                'fracao_percentual'       => round($info['fracao'] * 100, 1) . '%',
                'topicos_incluidos'       => $alocacao ? $alocacao['total_topicos_incluidos'] : 0,
                'topicos_excluidos'       => $alocacao ? $alocacao['total_topicos_excluidos'] : 0,
                'topicos_excluidos_lista' => [],
            ];

            // Se há tópicos excluídos, gerar alerta
            if ($alocacao && $alocacao['total_topicos_excluidos'] > 0) {
                $listaExcluidos = $alocacao['topicos_excluidos']
                    ->pluck('nome')
                    ->toArray();

                $disciplinaResumo['topicos_excluidos_lista'] = $listaExcluidos;

                $alertas[] = sprintf(
                    '%d tópico(s) de %s não couberam no cronograma: %s. Considere aumentar seu tempo de estudo.',
                    $alocacao['total_topicos_excluidos'],
                    ucfirst(str_replace('_', ' ', $slug)),
                    implode(', ', array_slice($listaExcluidos, 0, 5)) // mostra até 5
                    . (count($listaExcluidos) > 5 ? ' e mais ' . (count($listaExcluidos) - 5) : '')
                );
            }

            $resumoDisciplinas[] = $disciplinaResumo;
        }

        // -----------------------------------------------------------------
        // Alertas adicionais baseados em heurísticas
        // -----------------------------------------------------------------

        // Alerta se o tempo médio por tópico é muito baixo
        $totalTopicosIncluidos = collect($resumoDisciplinas)->sum('topicos_incluidos');
        if ($totalTopicosIncluidos > 0) {
            $mediaHorasPorTopico = $slotsConteudo / $totalTopicosIncluidos;
            if ($mediaHorasPorTopico < 1.5) {
                $alertas[] = sprintf(
                    'Atenção: a média é de %.1f hora(s) por tópico, o que pode ser insuficiente para absorção profunda. Considere aumentar as horas diárias ou reduzir disciplinas.',
                    $mediaHorasPorTopico
                );
            }
        }

        // Alerta se o período é muito curto
        if ($diasDisponiveis->count() < 30) {
            $alertas[] = 'O período de estudo é inferior a 30 dias. Para um preparo mais completo para o ENEM, recomendamos iniciar com pelo menos 3 meses de antecedência.';
        }

        // Alerta informativo sobre simulados e redação
        $alertas[] = sprintf(
            '%d simulado(s) mensal(is) programado(s) e %d prática(s) de redação semanal(is) incluída(s) no cronograma.',
            $totalSimulados,
            $semanasEstudo
        );

        Log::info('[CronogramaService] montarSaidaFinal :: alertas gerados', [
            'alertas' => $alertas,
        ]);

        // -----------------------------------------------------------------
        // Montar saída final
        // -----------------------------------------------------------------
        return [
            'sucesso' => true,

            'resumo' => [
                'periodo' => [
                    'data_inicio' => $input->dataInicio->format('Y-m-d'),
                    'data_fim'    => $input->dataFim->format('Y-m-d'),
                    'horas_por_dia' => $input->horasPorDia,
                ],
                'total_dias_estudo'       => $diasDisponiveis->count(),
                'total_slots'             => $totalSlots,
                'slots_conteudo'          => $slotsConteudo,
                'slots_simulados'         => $slotsSimulados,
                'slots_redacao'           => $slotsRedacao,
                'total_simulados'         => $totalSimulados,
                'total_redacoes'          => $semanasEstudo,
                'total_topicos_incluidos' => $totalTopicosIncluidos,
                'disciplinas'             => $resumoDisciplinas,
            ],

            'cronograma' => $cronograma->toArray(),

            'alertas' => $alertas,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    /**
     * Traduz o índice do dia da semana (Carbon) para português.
     */
    private function traduzirDiaSemana(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'domingo',
            1 => 'segunda-feira',
            2 => 'terça-feira',
            3 => 'quarta-feira',
            4 => 'quinta-feira',
            5 => 'sexta-feira',
            6 => 'sábado',
        };
    }
}