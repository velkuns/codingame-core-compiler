<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler;

/**
 * Class FileReader
 *
 * @author Romain Cottard
 */
class FileSourceReader
{
    public function read(string $file): string
    {
        return (string) file_get_contents($file);
    }
}
