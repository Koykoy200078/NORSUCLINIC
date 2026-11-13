<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message): JsonResponse
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 422): JsonResponse
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    public function sendSuccess($message): JsonResponse
    {
        return Response::json([
            'success' => true,
            'message' => $message,
        ], 200);
    }

    /**
     * Resets an account's password to the default, for the screen that manages that kind of account (patients, doctors
     * or staff; re-audit 2026-11-13, one copy instead of three). All account types share the `users` table, so the type
     * check keeps a screen from resetting someone else's account (the clinic admin's above all). A failed save is left to
     * the exception handler, which logs it and answers with a generic error: no database text reaches the browser.
     */
    protected function resetToDefaultPassword(User $user, int $accountType): JsonResponse
    {
        abort_unless((int) $user->type === $accountType, 403);

        $user->update(['password' => Hash::make(User::DEFAULT_PASSWORD)]);

        return $this->sendSuccess('Password has been reset to default ('.User::DEFAULT_PASSWORD.') successfully.');
    }
}
