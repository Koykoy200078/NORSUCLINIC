<?php

namespace Tests\Concerns;

use DOMDocument;
use DOMXPath;

/**
 * Reads every form control of a page in document order: form, label, input, select, option, optgroup, textarea, button, each
 * with its attributes sorted and written the same way whatever builder drew it, and the text it shows.
 *
 * Used by the link crawl to record the forms of every page before the form builder was replaced (FORMS_BASELINE=<file>) and to
 * compare them afterwards (FORMS_COMPARE=<file>): a control that appears, disappears or changes its name, id, value, class,
 * options or text shows up as a difference. Not part of the page that a person sees: Livewire's own state attributes, the
 * CSRF token, random ids and the order of attributes are left out.
 */
trait ReadsFormControls
{
    /** attributes that mean "on" by being there, whether the builder writes `required` or `required="required"` */
    private const BOOLEAN_ATTRIBUTES = ['required', 'multiple', 'selected', 'checked', 'disabled', 'readonly', 'autofocus', 'novalidate', 'hidden', 'formnovalidate', 'open'];

    private const SKIPPED_ATTRIBUTES = ['wire:id', 'wire:snapshot', 'wire:effects', 'wire:initial-data', 'x-intersect', 'x-data'];

    /** @return array<int, string> */
    protected function formControls(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $html . '</body>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $controls = [];
        foreach ((new DOMXPath($dom))->query('//form | //label | //input | //select | //option | //optgroup | //textarea | //button') as $element) {
            $attributes = [];
            foreach ($element->attributes as $attribute) {
                $name = $attribute->nodeName;
                if (in_array($name, self::SKIPPED_ATTRIBUTES, true)) {
                    continue;
                }

                $value = $attribute->nodeValue;
                // A token's value changes with every request, and a builder that has none yet leaves the attribute out. An empty
                // value on an <input> is the same as no value. spatie gives the hidden "_method" field an id that Collective did
                // not: nothing selects it, so it is left out of the comparison (U0.3 records it as an accepted difference).
                if ($element->nodeName === 'input' && $name === 'value' && (str_ends_with($element->getAttribute('name'), '_token') || $value === '')) {
                    continue;
                }
                if ($element->nodeName === 'input' && $name === 'id' && $element->getAttribute('name') === '_method') {
                    continue;
                }
                if (in_array($name, self::BOOLEAN_ATTRIBUTES, true)) {
                    $value = '';
                } else {
                    $value = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', 'UUID', trim(preg_replace('/\s+/', ' ', $value)));
                }

                $attributes[$name] = $value === '' ? $name : $name . '=' . $value;
            }
            ksort($attributes);

            $text = in_array($element->nodeName, ['label', 'button', 'option', 'textarea'], true)
                ? mb_substr(trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $element->textContent))), 0, 100)
                : '';

            $controls[] = $element->nodeName . '[' . implode(' ', $attributes) . ']' . ($text !== '' ? ' ' . $text : '');
        }

        return $controls;
    }
}
