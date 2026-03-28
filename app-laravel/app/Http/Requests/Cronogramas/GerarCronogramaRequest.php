<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================================
 * VALIDAÇÃO DO FORMULÁRIO DE CRONOGRAMA
 * ============================================================================
 *
 * Toda a validação dos dados do aluno acontece aqui, ANTES de chegar ao
 * Controller ou ao Service. Isso segue o princípio de "fail fast":
 * se os dados são inválidos, a resposta de erro é imediata e padronizada.
 *
 * O Laravel automaticamente retorna erro 422 (Unprocessable Entity) com
 * as mensagens traduzidas se qualquer regra falhar.
 */
class GerarCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar conforme sua policy de autenticação
    }

    public function rules(): array
    {
        return [
            // -----------------------------------------------------------------
            // Datas do período
            // -----------------------------------------------------------------
            'data_inicio' => [
                'required',
                'date',
                'after_or_equal:today', // Não permite cronograma no passado
            ],
            'data_fim' => [
                'required',
                'date',
                'after:data_inicio', // Fim deve ser posterior ao início
            ],

            // -----------------------------------------------------------------
            // Dias da semana
            // -----------------------------------------------------------------
            // Array de inteiros: 0=domingo, 1=segunda ... 6=sábado
            // Pelo menos 1 dia deve ser selecionado.
            // -----------------------------------------------------------------
            'dias_semana' => [
                'required',
                'array',
                'min:1',
            ],
            'dias_semana.*' => [
                'integer',
                'between:0,6',
            ],

            // -----------------------------------------------------------------
            // Flags de feriados e férias
            // -----------------------------------------------------------------
            'estudar_feriados' => ['required', 'boolean'],
            'estudar_ferias'   => ['required', 'boolean'],

            // -----------------------------------------------------------------
            // Disciplinas selecionadas
            // -----------------------------------------------------------------
            // Pelo menos 1 disciplina deve ser selecionada.
            // Os valores devem estar na lista permitida (slugs do banco).
            // -----------------------------------------------------------------
            'disciplinas' => [
                'required',
                'array',
                'min:1',
            ],
            'disciplinas.*' => [
                'string',
                'in:matematica,portugues,literatura,ingles,espanhol,redacao,fisica,quimica,biologia,historia,geografia,filosofia,sociologia',
            ],

            // -----------------------------------------------------------------
            // Horas por dia
            // -----------------------------------------------------------------
            // Mínimo 1h, máximo 12h (mais que isso é irrealista e prejudicial).
            // -----------------------------------------------------------------
            'horas_por_dia' => [
                'required',
                'integer',
                'between:1,12',
            ],
        ];
    }

    /**
     * Mensagens de erro customizadas em português.
     */
    public function messages(): array
    {
        return [
            'data_inicio.required'       => 'A data de início é obrigatória.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
            'data_fim.required'          => 'A data de fim é obrigatória.',
            'data_fim.after'             => 'A data de fim deve ser posterior à data de início.',
            'dias_semana.required'       => 'Selecione pelo menos 1 dia da semana.',
            'dias_semana.min'            => 'Selecione pelo menos 1 dia da semana.',
            'dias_semana.*.between'      => 'Dia da semana inválido.',
            'disciplinas.required'       => 'Selecione pelo menos 1 disciplina.',
            'disciplinas.min'            => 'Selecione pelo menos 1 disciplina.',
            'disciplinas.*.in'           => 'Disciplina ":input" não é válida.',
            'horas_por_dia.required'     => 'Informe quantas horas por dia pretende estudar.',
            'horas_por_dia.between'      => 'As horas por dia devem estar entre 1 e 12.',
        ];
    }
}