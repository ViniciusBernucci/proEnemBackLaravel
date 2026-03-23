<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatematicaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('matematica')->truncate();

        $area = 'Matemática e suas Tecnologias';

        $modulos = [
            '1' => 'Aritmética e Matemática Básica',
            '2' => 'Grandezas e Proporcionalidade',
            '3' => 'Estatística e Probabilidade',
            '4' => 'Geometria',
            '5' => 'Funções e Álgebra',
            '6' => 'Matemática Financeira',
            '7' => 'Álgebra Linear e Tópicos Complementares',
        ];

        // recorrencia = frequencia do JSON (proporção de questões no total de 45 da prova, escala 1-5)
        // relevancia  = relevancia do JSON (impacto estratégico para a nota, escala 1-5)
        // tempo       = 0 (duração não disponível nesta disciplina)
        $assuntos = [
            // Aritmética e Matemática Básica
            ['modulo_cod' => '1', 'topico' => 'Operações fundamentais e números naturais',     'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '1', 'topico' => 'Frações, decimais e números racionais',          'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '1', 'topico' => 'Potenciação e radiciação',                       'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '1', 'topico' => 'Notação científica',                             'relevancia' => 1, 'recorrencia' => 1],

            // Grandezas e Proporcionalidade
            ['modulo_cod' => '2', 'topico' => 'Razão e proporção',                              'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '2', 'topico' => 'Porcentagem',                                    'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '2', 'topico' => 'Unidades de medida e conversões',                'relevancia' => 4, 'recorrencia' => 3],
            ['modulo_cod' => '2', 'topico' => 'Regra de três simples e composta',               'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '2', 'topico' => 'Escalas',                                        'relevancia' => 3, 'recorrencia' => 2],

            // Estatística e Probabilidade
            ['modulo_cod' => '3', 'topico' => 'Leitura e interpretação de gráficos e tabelas', 'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '3', 'topico' => 'Medidas de tendência central (média, mediana, moda)', 'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '3', 'topico' => 'Medidas de dispersão (variância, desvio padrão)', 'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '3', 'topico' => 'Probabilidade',                                  'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '3', 'topico' => 'Análise combinatória',                           'relevancia' => 3, 'recorrencia' => 3],

            // Geometria
            ['modulo_cod' => '4', 'topico' => 'Geometria plana (áreas, perímetros, ângulos)',  'relevancia' => 5, 'recorrencia' => 4],
            ['modulo_cod' => '4', 'topico' => 'Geometria espacial (volumes, áreas de superfície)', 'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '4', 'topico' => 'Trigonometria',                                  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '4', 'topico' => 'Geometria analítica',                            'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '4', 'topico' => 'Planificação de sólidos e projeções',            'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '4', 'topico' => 'Semelhança e congruência de triângulos (Teorema de Tales)', 'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '4', 'topico' => 'Simetrias e transformações geométricas',         'relevancia' => 1, 'recorrencia' => 1],

            // Funções e Álgebra
            ['modulo_cod' => '5', 'topico' => 'Funções do 1º grau (função afim)',               'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '5', 'topico' => 'Funções do 2º grau (função quadrática)',         'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '5', 'topico' => 'Função exponencial',                             'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '5', 'topico' => 'Logaritmos',                                     'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '5', 'topico' => 'Equações do 1º e 2º grau',                      'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '5', 'topico' => 'Progressão aritmética (PA)',                     'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '5', 'topico' => 'Progressão geométrica (PG)',                     'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '5', 'topico' => 'Expressões algébricas e polinômios',             'relevancia' => 1, 'recorrencia' => 1],

            // Matemática Financeira
            ['modulo_cod' => '6', 'topico' => 'Juros simples',                                  'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '6', 'topico' => 'Juros compostos',                                'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '6', 'topico' => 'Acréscimos e descontos sucessivos',              'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '6', 'topico' => 'Financiamentos e amortização',                   'relevancia' => 1, 'recorrencia' => 1],

            // Álgebra Linear e Tópicos Complementares
            ['modulo_cod' => '7', 'topico' => 'Matrizes e determinantes',                       'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7', 'topico' => 'Sistemas lineares',                              'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7', 'topico' => 'Números complexos',                              'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7', 'topico' => 'Conjuntos e operações com conjuntos',            'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7', 'topico' => 'Lógica e raciocínio lógico',                    'relevancia' => 2, 'recorrencia' => 2],
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

        DB::table('matematica')->insert($records);
    }
}
