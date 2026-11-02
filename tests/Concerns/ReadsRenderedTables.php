<?php

namespace Tests\Concerns;

use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Reads what a person sees of a list screen out of its rendered HTML: the headers, every row's cell text, the links and
 * actions of the row, the page numbers, the "Showing x to y of z" line, the per-page choices and the toolbar.
 *
 * It looks at the structure a list screen must keep (a table, thead / tbody, a .pagination list, #perPage, .search-box),
 * not at the table library's own markup, so the same snapshot can be taken before and after the library is replaced.
 * Numbers inside links and wire: calls are replaced by N: row ids differ from run to run.
 */
trait ReadsRenderedTables
{
    /** @return array<string, mixed> */
    protected function snapshotOf(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $html . '</body>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($dom);

        $headers = [];
        foreach ($xpath->query('//table//thead//th[not(@x-show)]') as $th) {
            $headers[] = $this->tidy($th->textContent);
        }

        $rows = [];
        $empty = null;
        foreach ($xpath->query('//table//tbody/tr') as $tr) {
            $cells = $xpath->query('./td[not(@x-show)]', $tr);
            if ($cells->length === 1 && $cells->item(0)->attributes->getNamedItem('colspan') !== null) {
                $empty = $this->tidy($cells->item(0)->textContent);

                continue;
            }

            $texts = [];
            foreach ($cells as $cell) {
                $texts[] = $this->tidy($cell->textContent);
            }

            $targets = [];
            foreach ($xpath->query('.//a[@href] | .//*[@*[name()="wire:click"]] | .//form[@action]', $tr) as $element) {
                foreach (['href', 'action', 'wire:click'] as $attribute) {
                    $value = $element->attributes->getNamedItem($attribute)?->nodeValue;
                    if ($value !== null && $value !== 'javascript:void(0)' && $value !== '#') {
                        $targets[] = $attribute . '=' . $this->withoutNumbers($value);
                    }
                }
            }

            $rows[] = ['cells' => $texts, 'targets' => $targets];
        }

        $perPage = [];
        foreach ($xpath->query('//select[@id="perPage"]/option') as $option) {
            $perPage[] = $this->tidy($option->textContent);
        }
        $selected = $xpath->query('//select[@id="perPage"]/option[@selected]');

        $pages = [];
        foreach ($xpath->query('//ul[contains(@class, "pagination")]/li') as $li) {
            $active = str_contains((string) $li->attributes->getNamedItem('class')?->nodeValue, 'active') ? '*' : '';
            $disabled = str_contains((string) $li->attributes->getNamedItem('class')?->nodeValue, 'disabled') ? '!' : '';
            $pages[] = $active . $disabled . $this->tidy($li->textContent);
        }

        $summary = null;
        foreach ($xpath->query('//span[normalize-space()="Showing"]/parent::*') as $line) {
            $summary = $this->tidy($line->textContent);
        }

        $toolbar = null;
        $search = $xpath->query('//input[contains(@class, "search-box")]')->item(0);
        for ($node = $search; $node instanceof DOMNode; $node = $node->parentNode) {
            if (str_contains((string) $node->attributes?->getNamedItem('class')?->nodeValue, 'justify-content-between')) {
                $toolbar = $this->tidy($node->textContent);
                break;
            }
        }

        // Everything the bar under the table says: the "Show 10 25 50" choice, the page links and the count line, which is
        // "Showing 1 to 10 of 35 results" on a list of several pages and "Showing 4 results" on a list of one.
        $footer = null;
        for ($node = $xpath->query('//select[@id="perPage"]')->item(0); $node instanceof DOMNode; $node = $node->parentNode) {
            if (str_contains((string) $node->attributes?->getNamedItem('class')?->nodeValue, 'flex-xxl-row')) {
                $footer = $this->tidy($node->textContent);
                break;
            }
        }

        $sortable = [];
        foreach ($xpath->query('//table//thead//*[@*[name()="wire:click"]]') as $element) {
            if (preg_match("/^sortBy\('([^']+)'\)$/", (string) $element->attributes->getNamedItem('wire:click')->nodeValue, $found)) {
                $sortable[] = $found[1];
            }
        }

        return [
            'headers' => $headers,
            'sortable' => $sortable,
            'search' => $search?->attributes->getNamedItem('placeholder')?->nodeValue,
            'toolbar' => $toolbar,
            'rows' => $rows,
            'empty' => $empty,
            'perPage' => $perPage,
            'perPageSelected' => $selected->length ? $this->tidy($selected->item(0)->textContent) : null,
            'pages' => $pages,
            'summary' => $summary,
            'footer' => $footer,
        ];
    }

    private function tidy(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $text)));
    }

    private function withoutNumbers(string $value): string
    {
        return preg_replace('/\d+/', 'N', $value);
    }
}
