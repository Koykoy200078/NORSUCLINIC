<?php

namespace Tests\Feature\Regression;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The owner's rule (AGENTS.md section 5): every artisan command of this application is written in docs/commands.md, with what it
 * does, whether it changes data and where / when to run it, in the same commit that adds it.
 */
class CommandsLogTest extends TestCase
{
    public function test_every_command_of_the_application_is_listed_in_the_commands_log(): void
    {
        $log = file_get_contents(base_path('docs/commands.md'));
        $missing = [];
        $found = 0;

        foreach (File::allFiles(app_path('Console/Commands')) as $file) {
            if (! preg_match('/\$signature\s*=\s*[\'"]([a-z0-9:_-]+)/i', $file->getContents(), $match)) {
                continue;
            }
            $found++;
            if (! str_contains($log, 'artisan '.$match[1]) && ! str_contains($log, '`'.$match[1].'`')) {
                $missing[] = $match[1];
            }
        }

        $this->assertGreaterThan(10, $found, 'the scan found too few commands: the signature pattern no longer matches');
        $this->assertSame([], $missing, 'Commands missing from docs/commands.md: '.implode(', ', $missing));
    }
}
