<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Estudos - {{ $cronograma->nome }}</title>
    <style>
        /* CSS Otimizado para DomPDF */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #1f2937;
            background-color: #fff;
            line-height: 1.5;
            padding: 1cm;
        }
        h1, h2, h3 {
            color: #4f46e5; /* Indigo-600 */
        }
        /* Pagina de Capa / Introducao */
        .cover {
            text-align: center;
            margin-top: 5cm;
        }
        .cover h1 {
            font-size: 32pt;
            margin-bottom: 20px;
        }
        .cover p.subtitle {
            font-size: 16pt;
            color: #6b7280;
            margin-bottom: 3cm;
        }
        .intro-box {
            background-color: #eef2ff; /* Indigo-50 */
            border-left: 4px solid #4f46e5;
            padding: 20px;
            text-align: left;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .intro-box h2 {
            margin-bottom: 10px;
            font-size: 18pt;
        }
        .intro-box p {
            margin-bottom: 10px;
            text-align: justify;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Layout do Cronograma */
        .day-header {
            background-color: #4f46e5;
            color: white;
            padding: 8px 12px;
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 4px 4px 0 0;
            display: block;
        }
        .task-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        .task-table th, .task-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .task-table th {
            background-color: #f3f4f6;
            color: #374151;
        }
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #9ca3af;
            display: inline-block;
            margin-right: 5px;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .bg-video { background-color: #dbeafe; color: #1e40af; }
        .bg-exercise { background-color: #fce7f3; color: #9d174d; }
        .bg-reading { background-color: #fef3c7; color: #92400e; }
        .bg-gray { background-color: #f3f4f6; color: #374151; }

    </style>
</head>
<body>

    <!-- CAPA -->
    <div class="cover">
        <h1>Meu Cronograma de Estudos</h1>
        <p class="subtitle">Personalizado Específico: <strong>{{ $cronograma->nome }}</strong></p>
        
        <div class="intro-box">
            <h2>Por que este cronograma é diferente?</h2>
            <p>Seu cronograma foi desenhado algoritmicamente para maximizar a sua retenção no ENEM utilizando dois pilares de neurociência e dados estatísticos:</p>
            <p><strong>1. Prática Intercalada:</strong> Em vez de criar blocos maciços e exaustivos de uma única disciplina por dia, nosso algoritmo implementa um formato "Round-Robin" ponderado, permitindo que seu cérebro recupere ativamente as informações ao cruzar temas diferentes, aumentando o aprendizado a longo prazo.</p>
            <p><strong>2. Score de Pareto:</strong> Cruzamos milhares de questões históricas do ENEM. Você não está gastando horas em tópicos mortos; a matemática distribuiu este tempo privilegiando e repetindo matérias baseadas estritamente em sua <strong>Relevância Oculta</strong> e <strong>Recorrência Estatística Real</strong> em provas passadas.</p>
            <br>
            <p style="text-align: center;"><strong>Bons estudos. Este documento é o seu passaporte para a Aprovação.</strong></p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- CALENDÁRIO DIÁRIO -->
    <h2>Plano de Ação</h2>
    <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #e5e7eb;">

    @foreach($dias as $data => $tarefas)
        @php 
            $dataParseada = \Carbon\Carbon::parse($data);
        @endphp
        
        <div class="day-header">
            {{ ucfirst($tarefas->first()->dia_semana) }}, {{ $dataParseada->format('d/m/Y') }}
        </div>
        
        <table class="task-table">
            <thead>
                <tr>
                    <th style="width: 5%">Check</th>
                    <th style="width: 10%">Slot</th>
                    <th style="width: 25%">Disciplina</th>
                    <th style="width: 40%">Tópico</th>
                    <th style="width: 20%">Ação Estratégica</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tarefas as $tarefa)
                    <tr>
                        <td style="text-align: center;"><div class="checkbox"></div></td>
                        <td style="text-align: center;">{{ $tarefa->slot }} ⏱️ {{ $tarefa->duracao_minutos }}m</td>
                        <td><strong>{{ ucfirst($tarefa->disciplina) }}</strong></td>
                        <td>{{ $tarefa->topico }}</td>
                        <td>
                            @if(in_array($tarefa->tipo, ['simulado', 'redacao']))
                                <span class="badge bg-exercise">Prática / Teste</span>
                            @elseif($tarefa->tipo === 'conteudo_novo')
                                <span class="badge bg-video">Aula Nova</span>
                            @else
                                <span class="badge bg-reading">Revisão Ativa</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

</body>
</html>
