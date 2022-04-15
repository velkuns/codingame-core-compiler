<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler\Cleaner;

abstract class AbstractCleaner implements CleanerInterface
{
    /**
     * @return string[]
     */
    abstract protected function getPatterns(): array;

    public function clean(string $string): string
    {
        foreach ($this->getPatterns() as $pattern) {
            $string = (string) preg_replace($pattern, '', $string);
        }

        return $string;
    }
}
