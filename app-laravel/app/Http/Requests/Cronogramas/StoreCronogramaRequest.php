<?php

namespace App\Http\Requests\Cronogramas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCronogramaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Qualquer usuário autenticado pode criar cronogramas
        // O middleware 'auth:sanctum' já garante que o usuário está autenticado
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'data_inicio' => ['required', 'date', 'after_or_equal:today'],
            'data_fim' => ['required', 'date', 'after:data_inicio'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['in:seg,ter,qua,qui,sex,sab,dom'],
            'estudar_feriados' => ['required', 'boolean'],
            'tirar_ferias' => ['required', 'boolean'],
            'disciplinas_selecionadas' => ['required', 'array', 'min:1'],
            'disciplinas_selecionadas.*' => [
                'string',
                'max:100',
                Rule::in([
                    'Matemática',
                    'Língua Portuguesa',
                    'Literatura',
                    'Inglês',
                    'Espanhol',
                    'Redação',
                    'Física',
                    'Química',
                    'Biologia',
                    'História',
                    'Geografia',
                    'Filosofia',
                    'Sociologia',
                ])
            ],
            'minutos_estudo_por_dia' => ['required', 'integer', 'min:50', 'max:720'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do cronograma é obrigatório.',
            'nome.string' => 'O nome do cronograma deve ser um texto.',
            'nome.max' => 'O nome do cronograma deve ter no máximo 100 caracteres.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
            'data_fim.required' => 'A data de fim é obrigatória.',
            'data_fim.date' => 'A data de fim deve ser uma data válida.',
            'data_fim.after' => 'A data de fim deve ser posterior à data de início.',
            'dias_semana.required' => 'Selecione ao menos um dia da semana.',
            'dias_semana.array' => 'Os dias da semana devem ser um array.',
            'dias_semana.min' => 'Selecione ao menos um dia da semana.',
            'dias_semana.*.in' => 'Dia da semana inválido.',
            'estudar_feriados.required' => 'Informe se deseja estudar em feriados.',
            'estudar_feriados.boolean' => 'O campo estudar em feriados deve ser verdadeiro ou falso.',
            'tirar_ferias.required' => 'Informe se deseja tirar férias.',
            'tirar_ferias.boolean' => 'O campo tirar férias deve ser verdadeiro ou falso.',
            'disciplinas_selecionadas.required' => 'Selecione ao menos uma disciplina.',
            'disciplinas_selecionadas.array' => 'As disciplinas selecionadas devem ser um array.',
            'disciplinas_selecionadas.min' => 'Selecione ao menos uma disciplina.',
            'disciplinas_selecionadas.*.string' => 'O nome da disciplina deve ser uma string.',
            'disciplinas_selecionadas.*.max' => 'O nome da disciplina deve ter no máximo 100 caracteres.',
            'disciplinas_selecionadas.*.in' => 'A disciplina selecionada não é válida.',
            'minutos_estudo_por_dia.required' => 'Informe os minutos de estudo por dia.',
            'minutos_estudo_por_dia.integer' => 'Os minutos de estudo devem ser um número inteiro.',
            'minutos_estudo_por_dia.min' => 'Os minutos de estudo devem ser no mínimo 50.',
            'minutos_estudo_por_dia.max' => 'Os minutos de estudo devem ser no máximo 720.',
        ];
    }
}
