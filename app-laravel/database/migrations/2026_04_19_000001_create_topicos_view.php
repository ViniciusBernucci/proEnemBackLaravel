<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria uma VIEW unificada chamada 'topicos' que o CronogramaService espera.
 *
 * O algoritmo em CronogramaService consulta:
 *   - tabela: topicos
 *   - colunas: id, nome, disciplina_slug, relevancia, recorrencia
 *
 * As tabelas reais são: ciencias_humanas, ciencias_natureza, matematica, linguagens.
 * Cada uma tem: id, disciplina (nome do módulo), topico (nome do tópico),
 * relevancia, recorrencia, disciplina_id (FK → disciplinas.id).
 *
 * Esta VIEW une todas e cria um slug a partir do nome da disciplina.
 * O slug é derivado do nome da disciplina via mapeamento fixo (disciplina_id → slug).
 *
 * NOTA: Esta é uma VIEW (não uma tabela). Leitura apenas — não aceita INSERT/UPDATE.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW topicos AS
            SELECT
                id,
                topico   AS nome,
                CASE disciplina_id
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Matemática'        LIMIT 1) THEN 'matematica'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Língua Portuguesa' LIMIT 1) THEN 'portugues'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Literatura'        LIMIT 1) THEN 'literatura'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Inglês'            LIMIT 1) THEN 'ingles'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Espanhol'          LIMIT 1) THEN 'espanhol'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Redação'           LIMIT 1) THEN 'redacao'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Física'            LIMIT 1) THEN 'fisica'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Química'           LIMIT 1) THEN 'quimica'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Biologia'          LIMIT 1) THEN 'biologia'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'História'          LIMIT 1) THEN 'historia'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Geografia'         LIMIT 1) THEN 'geografia'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Filosofia'         LIMIT 1) THEN 'filosofia'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Sociologia'        LIMIT 1) THEN 'sociologia'
                    ELSE 'outro'
                END AS disciplina_slug,
                relevancia,
                recorrencia
            FROM ciencias_humanas WHERE disciplina_id IS NOT NULL

            UNION ALL

            SELECT
                id,
                topico   AS nome,
                CASE disciplina_id
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Física'    LIMIT 1) THEN 'fisica'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Química'   LIMIT 1) THEN 'quimica'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Biologia'  LIMIT 1) THEN 'biologia'
                    ELSE 'outro'
                END AS disciplina_slug,
                relevancia,
                recorrencia
            FROM ciencias_natureza WHERE disciplina_id IS NOT NULL

            UNION ALL

            SELECT
                id,
                topico   AS nome,
                'matematica' AS disciplina_slug,
                relevancia,
                recorrencia
            FROM matematica WHERE disciplina_id IS NOT NULL

            UNION ALL

            SELECT
                id,
                topico   AS nome,
                CASE disciplina_id
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Língua Portuguesa' LIMIT 1) THEN 'portugues'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Literatura'        LIMIT 1) THEN 'literatura'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Inglês'            LIMIT 1) THEN 'ingles'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Espanhol'          LIMIT 1) THEN 'espanhol'
                    WHEN (SELECT id FROM disciplinas WHERE nome = 'Redação'           LIMIT 1) THEN 'redacao'
                    ELSE 'outro'
                END AS disciplina_slug,
                relevancia,
                recorrencia
            FROM linguagens WHERE disciplina_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS topicos');
    }
};
