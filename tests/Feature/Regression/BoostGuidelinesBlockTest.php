<?php

namespace Tests\Feature\Regression;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * `php artisan boost:install` / `boost:update` (Laravel Boost, a dev tool) rewrite the guideline block at the end of AGENTS.md
 * and CLAUDE.md. Boost finds that block with `/<laravel-boost-guidelines>.*?<\/laravel-boost-guidelines>/s`
 * (vendor/laravel/boost/src/Install/GuidelineWriter.php), so the FIRST opening tag in the file starts the block. On
 * 2026-11-12 a Boost run started at a mention of the tag inside AGENTS.md section 5 and deleted the rest of section 5 and the
 * whole of sections 6 (data and secrets) and 7 (domain rules). The project's own text must therefore never contain the tag:
 * a Boost run may only ever replace its own block.
 */
class BoostGuidelinesBlockTest extends TestCase
{
    private const OPEN = '<laravel-boost-guidelines>';

    private const CLOSE = '</laravel-boost-guidelines>';

    /** @return array<string, array{string}> */
    public static function guidelineFiles(): array
    {
        return ['AGENTS.md' => ['AGENTS.md'], 'CLAUDE.md' => ['CLAUDE.md']];
    }

    #[DataProvider('guidelineFiles')]
    public function test_the_file_holds_exactly_one_boost_block_after_all_of_the_project_text(string $file): void
    {
        $content = (string) file_get_contents(base_path($file));

        $this->assertSame(1, substr_count($content, self::OPEN), "{$file}: the opening tag may only start Boost's own block");
        $this->assertSame(1, substr_count($content, self::CLOSE), "{$file}: one closing tag");
        $this->assertStringEndsWith(self::CLOSE, rtrim($content), "{$file}: Boost's block is the last thing in the file");
    }

    #[DataProvider('guidelineFiles')]
    public function test_a_boost_rewrite_keeps_every_line_of_the_project_text(string $file): void
    {
        $content = (string) file_get_contents(base_path($file));
        $projectText = strstr($content, self::OPEN, true);
        $this->assertNotFalse($projectText, "{$file} has no Boost block");

        // Exactly what GuidelineWriter does, with new guideline text.
        $rewritten = preg_replace_callback('/<laravel-boost-guidelines>.*?<\/laravel-boost-guidelines>/s', fn () => self::OPEN . "\nnew guidelines\n\n" . self::CLOSE, $content, 1);

        $this->assertStringStartsWith($projectText, $rewritten, "{$file}: a Boost run would change the project's own text");
        if ($file === 'AGENTS.md') {
            foreach (['## 5. How to work', '## 6. Data and secrets', '## 7. Domain rules that cause bugs when forgotten'] as $heading) {
                $this->assertStringContainsString($heading, $rewritten, "AGENTS.md: a Boost run would delete \"{$heading}\"");
            }
        }
    }
}
