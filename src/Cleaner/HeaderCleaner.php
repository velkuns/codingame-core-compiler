<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler\Cleaner;

class HeaderCleaner extends AbstractCleaner
{
    /** @var string copyright */
    private $copyright;

    public function __construct(string $copyright)
    {
        $this->copyright = $copyright;
    }

    protected function getPatterns(): array
    {
        return [
            "`\/\*\n \* Copyright \(c\) Romain Cottard\n \*\n \* For the full copyright and license information, please view the LICENSE\n \* file that was distributed with this source code\.\n \*\\n*/`m",
            "`\/\*\n \* Copyright \(c\) $this->copyright\n \*\n \* For the full copyright and license information, please view the LICENSE\n \* file that was distributed with this source code\.\n \*\\n*/`m",
        ];
    }
}
