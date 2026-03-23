<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ciencias_humanas')->truncate();

        $area = 'Ciências Humanas e suas Tecnologias';

        $modulos = [
            '1'  => 'História Geral',
            '2'  => 'História da África e da América',
            '3'  => 'História do Brasil',
            '4'  => 'Sociologia',
            '5'  => 'Filosofia',
            '6'  => 'Geografia Física',
            '7'  => 'Geografia Humana e Econômica',
            '11' => 'Atualidades e Geopolítica',
        ];

        // recorrencia = frequencia do JSON (proporção de questões no total de 45 da prova, escala 1-5)
        // relevancia  = relevancia do JSON (impacto estratégico para a nota, escala 1-5)
        // tempo       = assunto_duracao (segundos)
        $assuntos = [
            // História Geral
            ['modulo_cod' => '1',  'topico' => 'Introdução',                              'tempo' => 315,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '1',  'topico' => 'Pré-História',                            'tempo' => 172,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '1',  'topico' => 'Antiguidade Oriental',                    'tempo' => 362,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '1',  'topico' => 'Antiguidade Clássica',                    'tempo' => 388,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '1',  'topico' => 'História Medieval',                       'tempo' => 223,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '1',  'topico' => 'Mundo Moderno',                           'tempo' => 653,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '1',  'topico' => 'Transição para a Contemporaneidade',      'tempo' => 245,  'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '1',  'topico' => 'História Contemporânea',                  'tempo' => 1071, 'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '1',  'topico' => 'Guerra Fria',                             'tempo' => 203,  'relevancia' => 2, 'recorrencia' => 2],

            // História da África e da América
            ['modulo_cod' => '2',  'topico' => 'História da África',                      'tempo' => 300,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '2',  'topico' => 'História da América',                     'tempo' => 445,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '2',  'topico' => 'Independências das 13 colônias',          'tempo' => 51,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '2',  'topico' => 'EUA no século XIX',                       'tempo' => 174,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '2',  'topico' => 'América Latina no século XX',             'tempo' => 116,  'relevancia' => 2, 'recorrencia' => 2],

            // História do Brasil
            ['modulo_cod' => '3',  'topico' => 'Introdução',                              'tempo' => 124,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '3',  'topico' => 'Brasil Colônia',                          'tempo' => 658,  'relevancia' => 5, 'recorrencia' => 4],
            ['modulo_cod' => '3',  'topico' => 'Independência do Brasil',                 'tempo' => 74,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '3',  'topico' => 'Brasil Império',                          'tempo' => 602,  'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '3',  'topico' => 'Brasil República',                        'tempo' => 1178, 'relevancia' => 5, 'recorrencia' => 5],

            // Sociologia
            ['modulo_cod' => '4',  'topico' => 'Sociologia Clássica',                     'tempo' => 424,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '4',  'topico' => 'Transformações Sociais',                  'tempo' => 516,  'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '4',  'topico' => 'Cultura e Sociedade',                     'tempo' => 113,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '4',  'topico' => 'Sociologia Brasileira',                   'tempo' => 206,  'relevancia' => 3, 'recorrencia' => 2],

            // Filosofia
            ['modulo_cod' => '5',  'topico' => 'Filosofia Antiga',                        'tempo' => 438,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '5',  'topico' => 'Filosofia Medieval',                      'tempo' => 540,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '5',  'topico' => 'Filosofia Contemporânea',                 'tempo' => 582,  'relevancia' => 3, 'recorrencia' => 2],

            // Geografia Física
            ['modulo_cod' => '6',  'topico' => 'Cartografia',                             'tempo' => 390,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Geologia',                                'tempo' => 362,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Geomorfologia',                           'tempo' => 64,   'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Pedologia',                               'tempo' => 122,  'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '6',  'topico' => 'Recursos Hídricos',                       'tempo' => 255,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Climatologia',                            'tempo' => 343,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Mudanças Climáticas',                     'tempo' => 143,  'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Biomas',                                  'tempo' => 347,  'relevancia' => 2, 'recorrencia' => 2],

            // Geografia Humana e Econômica
            ['modulo_cod' => '7',  'topico' => 'Demografia',                              'tempo' => 519,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7',  'topico' => 'Geografia Agrária',                       'tempo' => 118,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7',  'topico' => 'Recursos naturais',                       'tempo' => 300,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '7',  'topico' => 'Industrialização',                        'tempo' => 101,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '7',  'topico' => 'Transportes',                             'tempo' => 53,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Industrialização do Brasil',              'tempo' => 110,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '7',  'topico' => 'Urbanização Brasileira',                  'tempo' => 132,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7',  'topico' => 'Indicadores Sociais',                     'tempo' => 68,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Setores de Atividades Econômicas',        'tempo' => 54,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Globalização',                            'tempo' => 217,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '7',  'topico' => 'Categorias Geográficas',                  'tempo' => 62,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Regionalizações do Brasil',               'tempo' => 27,   'relevancia' => 1, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Regiões do Brasil',                       'tempo' => 249,  'relevancia' => 2, 'recorrencia' => 2],

            // Atualidades e Geopolítica
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 01',   'tempo' => 148,  'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 02',   'tempo' => 239,  'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 03',   'tempo' => 203,  'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 04',   'tempo' => 166,  'relevancia' => 2, 'recorrencia' => 2],
        ];

        $records = array_map(function ($assunto) use ($area, $modulos) {
            return [
                'area'        => $area,
                'disciplina'  => $modulos[$assunto['modulo_cod']],
                'topico'      => $assunto['topico'],
                'recorrencia' => $assunto['recorrencia'],
                'relevancia'  => $assunto['relevancia'],
                'tempo'       => $assunto['tempo'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }, $assuntos);

        DB::table('ciencias_humanas')->insert($records);
    }
}
