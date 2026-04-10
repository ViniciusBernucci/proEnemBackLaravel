<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DisciplinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('disciplinas')->count() > 0) {
            return;
        }

        $disciplinasImagem = [
            'Matemática'        => 'Matemática e suas Tecnologias',
            'Língua Portuguesa' => 'Linguagens, Códigos e suas Tecnologias',
            'Literatura'        => 'Linguagens, Códigos e suas Tecnologias',
            'Inglês'            => 'Linguagens, Códigos e suas Tecnologias',
            'Espanhol'          => 'Linguagens, Códigos e suas Tecnologias',
            'Redação'           => 'Linguagens, Códigos e suas Tecnologias',
            'Física'            => 'Ciências da Natureza e suas Tecnologias',
            'Química'           => 'Ciências da Natureza e suas Tecnologias',
            'Biologia'          => 'Ciências da Natureza e suas Tecnologias',
            'História'          => 'Ciências Humanas e suas Tecnologias',
            'Geografia'         => 'Ciências Humanas e suas Tecnologias',
            'Filosofia'         => 'Ciências Humanas e suas Tecnologias',
            'Sociologia'        => 'Ciências Humanas e suas Tecnologias',
        ];

        // Mapeamento dos módulos antigos para as novas disciplinas corretas
        $mapeamentoModulos = [
            // Humanas
            'História Geral' => 'História',
            'História da África e da América' => 'História',
            'História do Brasil' => 'História',
            'Sociologia' => 'Sociologia',
            'Filosofia' => 'Filosofia',
            'Geografia Física' => 'Geografia',
            'Geografia Humana e Econômica' => 'Geografia',
            'Atualidades e Geopolítica' => 'Geografia',

            // Natureza
            'Biologia - Ecologia e Meio Ambiente' => 'Biologia',
            'Biologia - Citologia e Biologia Molecular' => 'Biologia',
            'Biologia - Fisiologia e Saúde Humana' => 'Biologia',
            'Biologia - Genética e Evolução' => 'Biologia',
            'Biologia - Diversidade dos Seres Vivos' => 'Biologia',
            'Física - Mecânica' => 'Física',
            'Física - Eletricidade e Energia' => 'Física',
            'Física - Termologia' => 'Física',
            'Física - Ondulatória e Óptica' => 'Física',
            'Física - Tópicos Complementares' => 'Física',
            'Química - Química Geral e Inorgânica' => 'Química',
            'Química - Físico-Química' => 'Química',
            'Química - Química Orgânica' => 'Química',
            'Química - Química Ambiental e Aplicada' => 'Química',

            // Matemática
            'Aritmética e Matemática Básica' => 'Matemática',
            'Grandezas e Proporcionalidade' => 'Matemática',
            'Estatística e Probabilidade' => 'Matemática',
            'Geometria' => 'Matemática',
            'Funções e Álgebra' => 'Matemática',
            'Matemática Financeira' => 'Matemática',
            'Álgebra Linear e Tópicos Complementares' => 'Matemática',

            // Linguagens
            'LÍNGUA PORTUGUESA - INTERPRETAÇÃO E COMPREENSÃO TEXTUAL' => 'Língua Portuguesa',
            'LÍNGUA PORTUGUESA - GRAMÁTICA E ESTRUTURA DA LÍNGUA' => 'Língua Portuguesa',
            'LÍNGUA PORTUGUESA - GÊNEROS TEXTUAIS E TIPOLOGIA' => 'Língua Portuguesa',
            'LITERATURA BRASILEIRA' => 'Literatura',
            'LÍNGUA ESTRANGEIRA - INGLÊS' => 'Inglês',
            'LÍNGUA ESTRANGEIRA - ESPANHOL' => 'Espanhol',
            'ARTES' => 'Língua Portuguesa', // Mapeado para L. Portuguesa já que não há Artes
            'EDUCAÇÃO FÍSICA E TECNOLOGIAS DA INFORMAÇÃO' => 'Língua Portuguesa', // Mapeado para L. Portuguesa
        ];

        DB::beginTransaction();

        try {
            // 2. Inserir as 13 disciplinas base
            $disciplinasIds = [];
            foreach ($disciplinasImagem as $nome => $area) {
                $id = DB::table('disciplinas')->insertGetId([
                    'nome' => $nome,
                    'area' => $area,
                    'topicos' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $disciplinasIds[$nome] = $id;
            }

            // 3. Atualizar os topicos e disciplina_id com base no mapeamento
            $tabelas = [
                'ciencias_humanas',
                'ciencias_natureza',
                'matematica',
                'linguagens'
            ];

            foreach ($tabelas as $table) {
                $registros = DB::table($table)->get();

                foreach ($registros as $registro) {
                    $nomeModulo = $registro->disciplina;
                    if (isset($mapeamentoModulos[$nomeModulo])) {
                        $disciplinaCorreta = $mapeamentoModulos[$nomeModulo];
                        $idCorreto = $disciplinasIds[$disciplinaCorreta];

                        DB::table($table)
                            ->where('id', $registro->id)
                            ->update(['disciplina_id' => $idCorreto]);
                    }
                }
            }

            // 4. Calcular e atualizar o número de tópicos por disciplina
            foreach ($disciplinasIds as $nomeDisciplina => $id) {
                $totalTopicos = 0;
                foreach ($tabelas as $table) {
                    $totalTopicos += DB::table($table)->where('disciplina_id', $id)->count();
                }

                DB::table('disciplinas')
                    ->where('id', $id)
                    ->update(['topicos' => $totalTopicos]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
