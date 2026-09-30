<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Text that is safe to show a clinic user for a caught exception.
     *
     * Only a plain \RuntimeException carries a message written for users (validation-style
     * problems raised by our own code, e.g. "Insufficient stock for ..."). Anything else (SQL
     * errors with table/column names, file-system paths, framework exceptions, ...) is logged by
     * the caller and replaced by a generic sentence instead of being echoed to the browser.
     */
    protected function userFacingErrorMessage(\Throwable $e, string $fallback = 'Something went wrong. Please try again or contact the administrator.'): string
    {
        if (get_class($e) === \RuntimeException::class && $e->getMessage() !== '') {
            return $e->getMessage();
        }

        return $fallback;
    }
}
