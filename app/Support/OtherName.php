<?php

namespace App\Support;

/**
 * The free text typed on an "Others" line of the consultation form (illness or service).
 *
 * clean(): what is saved: leading / trailing spaces gone and every run of spaces, tabs and line breaks one space.
 * key():   what the ACCOMPLISHMENT REPORT compares: the same text without upper / lower case and without punctuation at
 *          its ends, so "dengue", "Dengue ", "DENGUE." and "dengue  " are one line, and so are "aa" and "aA".
 */
final class OtherName
{
    public static function clean(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        // \s alone misses the no-break space of a pasted text; invalid UTF-8 makes preg_replace return null: keep the text then.
        $clean = preg_replace('/[\s\x{00A0}\x{2000}-\x{200B}\x{202F}\x{3000}]+/u', ' ', $text) ?? $text;
        $clean = trim($clean);

        return $clean === '' ? null : $clean;
    }

    public static function key(string $text): string
    {
        $clean = self::clean($text) ?? '';
        $lower = mb_strtolower($clean);

        // A name that is only punctuation ("...") stays what it is instead of becoming an empty key.
        $trimmed = preg_replace('/^[\s\p{P}\p{S}]+|[\s\p{P}\p{S}]+$/u', '', $lower);

        return ($trimmed === null || $trimmed === '') ? $lower : $trimmed;
    }
}
