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
class FileDistWriter
{
    /** @var string file */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function write(string $content): self
    {
        file_put_contents($this->file, $content);

        return $this;
    }

    public function check(): self
    {
        echo 'Checking syntax: ... ';
        $result = exec('php -l ' . $this->file, $content);

        if (substr($result, 0, 16) === 'No syntax errors') {
            echo 'OK' . PHP_EOL;
        } else {
            echo 'FAILED' . PHP_EOL . $result . PHP_EOL; // @codeCoverageIgnore
        }

        return $this;
    }
}
