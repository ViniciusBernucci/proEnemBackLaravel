<?php

namespace App\Http\Controllers\Disciplinas;

use App\Http\Controllers\Controller;
use App\Models\Disciplina;
use Illuminate\Http\JsonResponse;

class DisciplinaController extends Controller
{
    /**
     * Lista todas as disciplinas disponíveis.
     */
    public function index(): JsonResponse
    {
        $disciplinas = Disciplina::orderBy('area')->orderBy('nome')->get(['id', 'nome', 'area']);

        return response()->json([
            'data' => $disciplinas,
        ], 200);
    }
}
