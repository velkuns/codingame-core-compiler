<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler\Cleaner;

class CodeCoverageIgnoreCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '` ?// ?@codeCoverageIgnore`',
        ];
    }
}
