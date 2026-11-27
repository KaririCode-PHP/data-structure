<?php

declare(strict_types=1);

/**
 * KaririCode Devkit — Project Overrides
 *
 * Written by `kcode migrate`. Run `kcode init --config` for a copy of
 * every supported key, or see https://github.com/KaririCode-PHP/devkit.
 */

return [
    // ── No src/Contract here: nothing to exclude from static analysis ──
    'exclude_dirs' => [],

    // ── Toolchain extras (installed into .kcode/vendor/) ───────
    'tools' => [
        'mockery/mockery' => '^1.6',
    ],
];
