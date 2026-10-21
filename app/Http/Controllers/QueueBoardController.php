<?php

namespace App\Http\Controllers;

use App\Services\QueueBoardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class QueueBoardController extends Controller
{
    private const HEADERS = ['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow'];

    public function settings(QueueBoardService $board): Response
    {
        return response()->view('patient_queue.board_settings', ['token' => $board->token()], 200, self::HEADERS);
    }

    public function rotate(QueueBoardService $board): RedirectResponse
    {
        $board->rotate();

        return redirect()->route('queue-board.settings')->with('success', 'Board link generated. Previous links no longer work.');
    }

    public function show(string $token, QueueBoardService $board): Response
    {
        $this->validateToken($token, $board);

        return response()->view('patient_queue.board', ['state' => $board->state(), 'channel' => $board->channel(), 'token' => $token], 200, self::HEADERS);
    }

    public function state(string $token, QueueBoardService $board): JsonResponse
    {
        $this->validateToken($token, $board);

        return response()->json($board->state(), 200, self::HEADERS);
    }

    private function validateToken(string $token, QueueBoardService $board): void
    {
        $current = $board->token();
        abort_unless($current !== null && hash_equals($current, $token), 404);
    }
}
