<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Regression guard for toastr call signatures.
 *
 * `Yoeunes\Toastr\Toastr` declares:
 *     error(string $message, string $title = '', array $options = [])
 *
 * The old codebase called `toastr()->error($msg, [], 'error')`, which passes a
 * string into the `array $options` slot. On PHP 8 that is a TypeError: the
 * request dies before the redirect, so the user sees an error page instead of
 * a toast. A previous fix missed 41 of these, so this test scans the source.
 */
class ToastrCallSignatureTest extends TestCase
{
    public function test_every_toastr_call_matches_the_real_signature(): void
    {
        $offenders = [];

        foreach ($this->phpFiles(app_path()) as $path) {
            $source = file_get_contents($path);

            if (! preg_match_all('/toastr\(\)->(\w+)\((.*?)\);/s', $source, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $call) {
                $args = $this->splitTopLevelArgs($call[2]);

                if (count($args) >= 2 && preg_match('/^\[\s*\]$/', $args[1])) {
                    $offenders[] = $path . ' :: ' . $call[1] . '() passes an array as $title';
                }

                if (count($args) >= 3 && ! str_starts_with($args[2], '[')) {
                    $offenders[] = $path . ' :: ' . $call[1] . '() passes a non-array as $options';
                }

                if (count($args) >= 2 && ! preg_match('/^[\'"]/', $args[1])) {
                    $offenders[] = $path . ' :: ' . $call[1] . '() passes a non-string as $title';
                }
            }
        }

        $this->assertSame([], $offenders, "Malformed toastr() calls:\n" . implode("\n", $offenders));
    }

    /** @return iterable<string> */
    private function phpFiles(string $root): iterable
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }

    /**
     * Split a call's argument list on commas that are not nested inside
     * parentheses, brackets, braces or string literals.
     *
     * @return array<int, string>
     */
    private function splitTopLevelArgs(string $args): array
    {
        if (trim($args) === '') {
            return [];
        }

        $parts = [];
        $depth = 0;
        $quote = null;
        $current = '';
        $length = strlen($args);

        for ($i = 0; $i < $length; $i++) {
            $char = $args[$i];

            if ($quote !== null) {
                $current .= $char;
                if ($char === $quote && $args[$i - 1] !== '\\') {
                    $quote = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if (in_array($char, ['(', '[', '{'], true)) {
                $depth++;
            } elseif (in_array($char, [')', ']', '}'], true)) {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }
}
