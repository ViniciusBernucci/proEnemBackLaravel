<?php

namespace App\DTO;

use Carbon\Carbon;

/**
 * ============================================================================
 * DTO DE ENTRADA DO CRONOGRAMA
 * ============================================================================
 *
 * Encapsula todos os dados do formulário do aluno.
 * Usar um DTO em vez de array garante tipagem forte e validação
 * antes de chegar ao Service, evitando erros silenciosos.
 */
class CronogramaInputDTO
{
    public function __construct(
        /** Data de início do cronograma */
        public readonly Carbon $dataInicio,

        /** Data de fim do cronograma */
        public readonly Carbon $dataFim,

        /**
         * Dias da semana permitidos para estudo.
         * Formato: array de inteiros onde 0=domingo, 1=segunda ... 6=sábado
         * Segue a convenção do Carbon: dayOfWeek
         */
        public readonly array $diasSemana,

        /** Se o aluno pretende estudar nos feriados nacionais */
        public readonly bool $estudarFeriados,

        /**
         * Se o aluno pretende estudar durante as férias escolares
         * (período fixo: 21/jul a 04/ago do ano corrente)
         */
        public readonly bool $estudarFerias,

        /**
         * Lista de IDs ou slugs das disciplinas selecionadas.
         * Ex: ['matematica', 'portugues', 'fisica', ...]
         */
        public readonly array $disciplinasSelecionadas,

        /**
         * Quantidade de horas de estudo disponíveis por dia.
         * Cada hora = 1 slot de estudo.
         */
        public readonly int $horasPorDia,
    ) {}
}