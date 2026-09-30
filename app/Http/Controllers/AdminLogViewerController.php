<?php

namespace App\Http\Controllers;

use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/**
 * The bundled log viewer deletes log files on a plain GET (?del=… / ?delall=…), so any page an
 * administrator is tricked into opening (an <img src>, a link) could wipe the server logs. Reading is
 * unchanged; deleting must be done from the Log Viewer at /log-viewer, which uses POST/DELETE with CSRF
 * protection. L-14.
 */
class AdminLogViewerController extends LogViewerController
{
    public function index()
    {
        abort_if(
            request()->hasAny(['del', 'delall', 'clean']),
            405,
            'Deleting logs through a link is disabled. Use the Log Viewer (/log-viewer) instead.'
        );

        return parent::index();
    }
}
