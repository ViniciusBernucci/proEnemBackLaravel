<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CienciasNaturezaSeeder extends Seeder
{
    public function run(): void
    {
        $area = 'Ciências da Natureza e suas Tecnologias';

        $modulos = [
            '1'  => 'Biologia - Ecologia e Meio Ambiente',
            '2'  => 'Biologia - Citologia e Biologia Molecular',
            '3'  => 'Biologia - Fisiologia e Saúde Humana',
            '4'  => 'Biologia - Genética e Evolução',
            '5'  => 'Biologia - Diversidade dos Seres Vivos',
            '6'  => 'Física - Mecânica',
            '7'  => 'Física - Eletricidade e Energia',
            '8'  => 'Física - Termologia',
            '9'  => 'Física - Ondulatória e Óptica',
            '10' => 'Física - Tópicos Complementares',
            '11' => 'Química - Química Geral e Inorgânica',
            '12' => 'Química - Físico-Química',
            '13' => 'Química - Química Orgânica',
            '14' => 'Química - Química Ambiental e Aplicada',
        ];

        // recorrencia = frequencia do JSON (proporção de questões no total de 45 da prova, escala 1-5)
        // relevancia  = relevancia do JSON (impacto estratégico para a nota, escala 1-5)
        // tempo       = 0 (duração não disponível nesta disciplina)
        $assuntos = [
            // Biologia - Ecologia e Meio Ambiente
            ['modulo_cod' => '1',  'topico' => 'Ecologia e ecossistemas',                                               'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '1',  'topico' => 'Biomas e biodiversidade',                                               'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '1',  'topico' => 'Impactos ambientais e sustentabilidade',                                'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '1',  'topico' => 'Cadeias e teias alimentares / Fluxo de energia',                        'relevancia' => 3, 'recorrencia' => 3],

            // Biologia - Citologia e Biologia Molecular
            ['modulo_cod' => '2',  'topico' => 'Citologia (estrutura celular, organelas, divisão celular)',             'relevancia' => 4, 'recorrencia' => 3],
            ['modulo_cod' => '2',  'topico' => 'Biologia molecular (DNA, RNA, síntese proteica)',                       'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '2',  'topico' => 'Biotecnologia (engenharia genética, transgênicos, células-tronco)',     'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '2',  'topico' => 'Bioquímica (carboidratos, lipídios, proteínas, enzimas)',               'relevancia' => 2, 'recorrencia' => 2],

            // Biologia - Fisiologia e Saúde Humana
            ['modulo_cod' => '3',  'topico' => 'Fisiologia humana (sistemas do corpo)',                                 'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '3',  'topico' => 'Microbiologia e doenças (vírus, bactérias, parasitas)',                 'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '3',  'topico' => 'Embriologia e reprodução',                                             'relevancia' => 1, 'recorrencia' => 1],

            // Biologia - Genética e Evolução
            ['modulo_cod' => '4',  'topico' => 'Genética mendeliana (1ª e 2ª leis de Mendel)',                         'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '4',  'topico' => 'Evolução (seleção natural, especiação, teorias evolutivas)',            'relevancia' => 3, 'recorrencia' => 2],

            // Biologia - Diversidade dos Seres Vivos
            ['modulo_cod' => '5',  'topico' => 'Botânica',                                                             'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '5',  'topico' => 'Zoologia e parasitologia',                                             'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '5',  'topico' => 'Taxonomia e origem da vida',                                           'relevancia' => 1, 'recorrencia' => 1],

            // Física - Mecânica
            ['modulo_cod' => '6',  'topico' => 'Dinâmica (leis de Newton, forças, trabalho, energia, potência)',       'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '6',  'topico' => 'Cinemática (MRU, MRUV, movimentos)',                                   'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '6',  'topico' => 'Hidrostática (pressão, empuxo, Arquimedes)',                           'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '6',  'topico' => 'Estática e equilíbrio',                                                'relevancia' => 1, 'recorrencia' => 1],

            // Física - Eletricidade e Energia
            ['modulo_cod' => '7',  'topico' => 'Eletrodinâmica (circuitos, resistência, potência elétrica)',           'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '7',  'topico' => 'Eletrostática (cargas, campo elétrico, potencial)',                    'relevancia' => 2, 'recorrencia' => 1],
            ['modulo_cod' => '7',  'topico' => 'Eletromagnetismo (indução, geradores, transformadores)',               'relevancia' => 2, 'recorrencia' => 2],

            // Física - Termologia
            ['modulo_cod' => '8',  'topico' => 'Termologia (calorimetria, dilatação, termodinâmica)',                  'relevancia' => 4, 'recorrencia' => 4],

            // Física - Ondulatória e Óptica
            ['modulo_cod' => '9',  'topico' => 'Ondulatória (ondas, som, fenômenos ondulatórios)',                     'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '9',  'topico' => 'Óptica (reflexão, refração, lentes, espelhos)',                        'relevancia' => 3, 'recorrencia' => 2],

            // Física - Tópicos Complementares
            ['modulo_cod' => '10', 'topico' => 'Física moderna (radioatividade, relatividade, quântica básica)',       'relevancia' => 2, 'recorrencia' => 1],

            // Química - Química Geral e Inorgânica
            ['modulo_cod' => '11', 'topico' => 'Química geral (tabela periódica, ligações, propriedades)',             'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '11', 'topico' => 'Atomística (modelos atômicos, distribuição eletrônica)',               'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '11', 'topico' => 'Estequiometria e cálculos químicos',                                   'relevancia' => 3, 'recorrencia' => 3],

            // Química - Físico-Química
            ['modulo_cod' => '12', 'topico' => 'Físico-química (soluções, termoquímica, cinética, equilíbrio, eletroquímica)', 'relevancia' => 5, 'recorrencia' => 5],
            ['modulo_cod' => '12', 'topico' => 'Soluções e concentrações',                                             'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '12', 'topico' => 'Termoquímica (entalpia, reações exo/endotérmicas)',                    'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '12', 'topico' => 'Cinética química',                                                     'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '12', 'topico' => 'Equilíbrio químico (Kc, Kp, pH, hidrólise)',                          'relevancia' => 2, 'recorrencia' => 2],
            ['modulo_cod' => '12', 'topico' => 'Eletroquímica (pilhas, eletrólise, oxidação-redução)',                 'relevancia' => 2, 'recorrencia' => 2],

            // Química - Química Orgânica
            ['modulo_cod' => '13', 'topico' => 'Química orgânica (funções orgânicas, hidrocarbonetos, isomeria)',      'relevancia' => 4, 'recorrencia' => 4],
            ['modulo_cod' => '13', 'topico' => 'Reações orgânicas e polímeros',                                        'relevancia' => 2, 'recorrencia' => 2],

            // Química - Química Ambiental e Aplicada
            ['modulo_cod' => '14', 'topico' => 'Química ambiental (poluição, tratamento de água, chuva ácida)',        'relevancia' => 3, 'recorrencia' => 3],
            ['modulo_cod' => '14', 'topico' => 'Energia (combustíveis, fontes energéticas, biocombustíveis)',          'relevancia' => 3, 'recorrencia' => 2],
            ['modulo_cod' => '14', 'topico' => 'Separação de misturas e propriedades da matéria',                      'relevancia' => 2, 'recorrencia' => 2],
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

        $existing = DB::table('ciencias_natureza')->pluck('topico')->toArray();
        $newRecords = array_values(array_filter($records, fn($r) => !in_array($r['topico'], $existing)));
        if (!empty($newRecords)) {
            DB::table('ciencias_natureza')->insert($newRecords);
        }
    }
}
