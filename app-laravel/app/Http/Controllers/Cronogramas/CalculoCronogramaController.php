<?php
 
namespace App\Http\Controllers;
 
use App\DTO\CronogramaInputDTO;
use App\Http\Requests\GerarCronogramaRequest;
use App\Services\CronogramaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
 
/**
 * ============================================================================
 * CONTROLLER DO CRONOGRAMA
 * ============================================================================
 *
 * Responsabilidade única: receber a requisição HTTP validada, converter
 * os dados para o DTO, chamar o Service, e retornar a resposta JSON.
 *
 * NÃO contém lógica de negócio — toda a inteligência está no Service.
 * Isso facilita testes (o Service pode ser testado isoladamente) e
 * permite reusar o algoritmo em outros contextos (Artisan command,
 * job assíncrono, etc).
 */
class CronogramaController extends Controller
{
    public function __construct(
        private readonly CronogramaService $cronogramaService
    ) {}
 
    /**
     * POST /api/cronograma/gerar
     *
     * Recebe os dados do formulário do aluno, gera o cronograma e retorna
     * a estrutura completa em JSON.
     *
     * O GerarCronogramaRequest já validou todos os inputs antes de chegar aqui.
     * Se a validação falhou, o Laravel já retornou 422 automaticamente.
     */
    public function gerar(GerarCronogramaRequest $request): JsonResponse
    {
        // -----------------------------------------------------------------
        // Montar o DTO a partir dos dados validados
        // -----------------------------------------------------------------
        // O DTO garante tipagem forte e imutabilidade dos dados de entrada.
        // Usando readonly properties do PHP 8.1+, os valores não podem ser
        // alterados acidentalmente dentro do Service.
        // -----------------------------------------------------------------
        $input = new CronogramaInputDTO(
            dataInicio              : Carbon::parse($request->validated('data_inicio')),
            dataFim                 : Carbon::parse($request->validated('data_fim')),
            diasSemana              : $request->validated('dias_semana'),
            estudarFeriados         : $request->boolean('estudar_feriados'),
            estudarFerias           : $request->boolean('estudar_ferias'),
            disciplinasSelecionadas : $request->validated('disciplinas'),
            horasPorDia             : $request->validated('horas_por_dia'),
        );
 
        // -----------------------------------------------------------------
        // Gerar o cronograma via Service
        // -----------------------------------------------------------------
        $resultado = $this->cronogramaService->gerarCronograma($input);
 
        // -----------------------------------------------------------------
        // Retornar resposta HTTP adequada
        // -----------------------------------------------------------------
        if (! $resultado['sucesso']) {
            return response()->json([
                'sucesso'  => false,
                'mensagem' => $resultado['erro'],
            ], 422);
        }
 
        return response()->json($resultado, 200);
    }
}
 
