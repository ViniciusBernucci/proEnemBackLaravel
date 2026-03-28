<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinguagensSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('linguagens')->truncate();

        $area = 'Linguagens, Códigos e suas Tecnologias';

        $modulos = [
            '1' => 'LÍNGUA PORTUGUESA - INTERPRETAÇÃO E COMPREENSÃO TEXTUAL',
            '2' => 'LÍNGUA PORTUGUESA - GRAMÁTICA E ESTRUTURA DA LÍNGUA',
            '3' => 'LÍNGUA PORTUGUESA - GÊNEROS TEXTUAIS E TIPOLOGIA',
            '4' => 'LITERATURA BRASILEIRA',
            '5' => 'LÍNGUA ESTRANGEIRA - INGLÊS',
            '6' => 'LÍNGUA ESTRANGEIRA - ESPANHOL',
            '7' => 'ARTES',
            '8' => 'EDUCAÇÃO FÍSICA E TECNOLOGIAS DA INFORMAÇÃO',
        ];

        // recorrencia = frequencia do JSON (proporção de questões no total da prova, escala 1-5)
        // relevancia  = relevancia do JSON (impacto estratégico para a nota, escala 1-5)
        // tempo       = 0 (duração não disponível nesta disciplina)
        $assuntos = [
            // Língua Portuguesa - Interpretação e Compreensão Textual
            ['modulo_cod' => '1', 'topico' => 'Interpretação e compreensão de textos',                'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '1', 'topico' => 'Variação linguística (regional, social, histórica, situacional)', 'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '1', 'topico' => 'Funções da linguagem e intencionalidade discursiva',   'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '1', 'topico' => 'Intertextualidade e relações entre textos',            'relevancia' => 3, 'recorrencia' => 3],

            // Língua Portuguesa - Gramática e Estrutura da Língua
            ['modulo_cod' => '2', 'topico' => 'Semântica (significado, denotação, conotação, ambiguidade, polissemia)', 'relevancia' => 4, 'recorrencia' => 3],
            ['modulo_cod' => '2', 'topico' => 'Figuras de linguagem',                                 'relevancia' => 4, 'recorrencia' => 3],
            ['modulo_cod' => '2', 'topico' => 'Coesão e coerência textual (conectivos, referenciação)', 'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '2', 'topico' => 'Gramática aplicada ao texto (concordância, regência, crase, pontuação)', 'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '2', 'topico' => 'Morfologia (classes de palavras, formação de palavras)', 'relevancia' => 2, 'recorrencia' => 1],

            // Língua Portuguesa - Gêneros Textuais e Tipologia
            ['modulo_cod' => '3', 'topico' => 'Gêneros textuais (crônica, artigo, editorial, carta, etc.)', 'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '3', 'topico' => 'Gêneros digitais e tecnologias da comunicação',        'relevancia' => 2, 'recorrencia' => 2],

            // Literatura Brasileira
            ['modulo_cod' => '4', 'topico' => 'Modernismo brasileiro (1ª, 2ª e 3ª gerações)',         'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '4', 'topico' => 'Realismo, Naturalismo e Pós-Modernismo',               'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '4', 'topico' => 'Romantismo brasileiro',                                'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '4', 'topico' => 'Gêneros literários e estética literária',              'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '4', 'topico' => 'Pré-Modernismo, Parnasianismo, Simbolismo',            'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '4', 'topico' => 'Barroco, Arcadismo, Classicismo',                      'relevancia' => 1, 'recorrencia' => 1],

            // Língua Estrangeira - Inglês
            ['modulo_cod' => '5', 'topico' => 'Inglês - Interpretação de textos',                    'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '5', 'topico' => 'Inglês - Vocabulário e tradução',                      'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '5', 'topico' => 'Inglês - Gramática em contexto',                       'relevancia' => 1, 'recorrencia' => 1],

            // Língua Estrangeira - Espanhol
            ['modulo_cod' => '6', 'topico' => 'Espanhol - Interpretação de textos',                  'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '6', 'topico' => 'Espanhol - Vocabulário, tradução e falsos cognatos',  'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '6', 'topico' => 'Espanhol - Gramática em contexto',                    'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '6', 'topico' => 'Espanhol - Diversidade cultural hispano-americana',   'relevancia' => 2, 'recorrencia' => 2],

            // Artes
            ['modulo_cod' => '7', 'topico' => 'Artes - Movimentos artísticos e história da arte',    'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7', 'topico' => 'Artes - Arte brasileira e manifestações culturais',    'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7', 'topico' => 'Artes - Música, dança, teatro e cinema',              'relevancia' => 2, 'recorrencia' => 2],

            // Educação Física e Tecnologias da Informação
            ['modulo_cod' => '8', 'topico' => 'Educação Física (corpo, esporte, práticas corporais)', 'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '8', 'topico' => 'Tecnologias da informação e comunicação',              'relevancia' => 2, 'recorrencia' => 2],
        ];

        $records = array_map(function ($assunto) use ($area, $modulos) {
            return [
                'area'        => $area,
                'disciplina'  => $modulos[$assunto['modulo_cod']],
                'topico'      => $assunto['topico'],
                'recorrencia' => $assunto['recorrencia'],
                'relevancia'  => $assunto['relevancia'],
                'tempo'       => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }, $assuntos);

        DB::table('linguagens')->insert($records);
    }
}
