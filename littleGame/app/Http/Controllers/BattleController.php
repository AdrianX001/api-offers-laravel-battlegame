<?php

namespace App\Http\Controllers;

use App\Services\BattleService;
use Illuminate\Http\JsonResponse;

class BattleController extends Controller
{
    public function __construct(
        private BattleService $battleService
    ) {}

    public function start(): JsonResponse
    {
        $result = $this->battleService->simulate();

        return response()->json($result);
    }
}
