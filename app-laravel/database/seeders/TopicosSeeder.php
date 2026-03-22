<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicosSeeder extends Seeder
{
    public function run(): void
    {
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

        $assuntos = [
            ['modulo_cod' => '1',  'topico' => 'Introdução',                              'tempo' => 315],
            ['modulo_cod' => '1',  'topico' => 'Pré-História',                            'tempo' => 172],
            ['modulo_cod' => '1',  'topico' => 'Antiguidade Oriental',                    'tempo' => 362],
            ['modulo_cod' => '1',  'topico' => 'Antiguidade Clássica',                    'tempo' => 388],
            ['modulo_cod' => '1',  'topico' => 'História Medieval',                       'tempo' => 223],
            ['modulo_cod' => '1',  'topico' => 'Mundo Moderno',                           'tempo' => 653],
            ['modulo_cod' => '1',  'topico' => 'Transição para a Contemporaneidade',      'tempo' => 245],
            ['modulo_cod' => '1',  'topico' => 'História Contemporânea',                  'tempo' => 1071],
            ['modulo_cod' => '1',  'topico' => 'Guerra Fria',                             'tempo' => 203],

            ['modulo_cod' => '2',  'topico' => 'História da África',                      'tempo' => 300],
            ['modulo_cod' => '2',  'topico' => 'História da América',                     'tempo' => 445],
            ['modulo_cod' => '2',  'topico' => 'Independências das 13 colônias',          'tempo' => 51],
            ['modulo_cod' => '2',  'topico' => 'EUA no século XIX',                       'tempo' => 174],
            ['modulo_cod' => '2',  'topico' => 'América Latina no século XX',             'tempo' => 116],

            ['modulo_cod' => '3',  'topico' => 'Introdução',                              'tempo' => 124],
            ['modulo_cod' => '3',  'topico' => 'Brasil Colônia',                          'tempo' => 658],
            ['modulo_cod' => '3',  'topico' => 'Independência do Brasil',                 'tempo' => 74],
            ['modulo_cod' => '3',  'topico' => 'Brasil Império',                          'tempo' => 602],
            ['modulo_cod' => '3',  'topico' => 'Brasil República',                        'tempo' => 1178],

            ['modulo_cod' => '4',  'topico' => 'Sociologia Clássica',                     'tempo' => 424],
            ['modulo_cod' => '4',  'topico' => 'Transformações Sociais',                  'tempo' => 516],
            ['modulo_cod' => '4',  'topico' => 'Cultura e Sociedade',                     'tempo' => 113],
            ['modulo_cod' => '4',  'topico' => 'Sociologia Brasileira',                   'tempo' => 206],

            ['modulo_cod' => '5',  'topico' => 'Filosofia Antiga',                        'tempo' => 438],
            ['modulo_cod' => '5',  'topico' => 'Filosofia Medieval',                      'tempo' => 540],
            ['modulo_cod' => '5',  'topico' => 'Filosofia Contemporânea',                 'tempo' => 582],

            ['modulo_cod' => '6',  'topico' => 'Cartografia',                             'tempo' => 390],
            ['modulo_cod' => '6',  'topico' => 'Geologia',                                'tempo' => 362],
            ['modulo_cod' => '6',  'topico' => 'Geomorfologia',                           'tempo' => 64],
            ['modulo_cod' => '6',  'topico' => 'Pedologia',                               'tempo' => 122],
            ['modulo_cod' => '6',  'topico' => 'Recursos Hídricos',                       'tempo' => 255],
            ['modulo_cod' => '6',  'topico' => 'Climatologia',                            'tempo' => 343],
            ['modulo_cod' => '6',  'topico' => 'Mudanças Climáticas',                     'tempo' => 143],
            ['modulo_cod' => '6',  'topico' => 'Biomas',                                  'tempo' => 347],

            ['modulo_cod' => '7',  'topico' => 'Demografia',                              'tempo' => 519],
            ['modulo_cod' => '7',  'topico' => 'Geografia Agrária',                       'tempo' => 118],
            ['modulo_cod' => '7',  'topico' => 'Recursos naturais',                       'tempo' => 300],
            ['modulo_cod' => '7',  'topico' => 'Industrialização',                        'tempo' => 101],
            ['modulo_cod' => '7',  'topico' => 'Transportes',                             'tempo' => 53],
            ['modulo_cod' => '7',  'topico' => 'Industrialização do Brasil',              'tempo' => 110],
            ['modulo_cod' => '7',  'topico' => 'Urbanização Brasileira',                  'tempo' => 132],
            ['modulo_cod' => '7',  'topico' => 'Indicadores Sociais',                     'tempo' => 68],
            ['modulo_cod' => '7',  'topico' => 'Setores de Atividades Econômicas',        'tempo' => 54],
            ['modulo_cod' => '7',  'topico' => 'Globalização',                            'tempo' => 217],
            ['modulo_cod' => '7',  'topico' => 'Categorias Geográficas',                  'tempo' => 62],
            ['modulo_cod' => '7',  'topico' => 'Regionalizações do Brasil',               'tempo' => 27],
            ['modulo_cod' => '7',  'topico' => 'Regiões do Brasil',                       'tempo' => 249],

            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 01',   'tempo' => 148],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 02',   'tempo' => 239],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 03',   'tempo' => 203],
            ['modulo_cod' => '11', 'topico' => 'Atualidades e geopolítica - Parte 04',   'tempo' => 166],
        ];

        $records = array_map(function ($assunto) use ($area, $modulos) {
            return [
                'area'        => $area,
                'disciplina'  => $modulos[$assunto['modulo_cod']],
                'topico'      => $assunto['topico'],
                'recorrencia' => 0,
                'relevancia'  => 0,
                'tempo'       => $assunto['tempo'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }, $assuntos);

        DB::table('topicos')->insert($records);
    }
}
