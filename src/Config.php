<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler;

use Velkuns\Codingame\Core\Compiler\Exception\CompilerConfigException;

/**
 * Class Config
 *
 * @author Romain Cottard
 */
class Config
{
    /** @var string $copyright */
    private $copyright = '';

    /** @var bool $hasGameLoop */
    private $hasGameLoop = false;

    /** @var string[] $sources */
    private $sources = ['src', 'vendor/velkuns/codingame-core-game/src'];

    /** @var string[] $excludes */
    private $excludes = [];

    /** @var string $distFile */
    private $distFile = 'dist/codingame.php';


    public function load(string $file): self
    {
        if (!is_readable($file)) {
            throw new CompilerConfigException("Cannot read $file (not exists or not readable)!", 1001); // @codeCoverageIgnore
        }

        try {
            /** @var array<string,bool|string|array<string>> $config */
            $config = \json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $exception) { // @codeCoverageIgnore
            throw new CompilerConfigException('Cannot load file config & json decode it!', 1002, $exception); // @codeCoverageIgnore
        }

        if (!is_array($config['sources'])) {
            throw new CompilerConfigException('Invalid "sources" property!', 1003); // @codeCoverageIgnore
        }

        if (!is_array($config['excludes'])) {
            throw new CompilerConfigException('Invalid "excludes" property!', 1004); // @codeCoverageIgnore
        }

        if (!is_string($config['dist'])) {
            throw new CompilerConfigException('Invalid "dist" property!', 1005); // @codeCoverageIgnore
        }

        if (!is_string($config['copyright'])) {
            throw new CompilerConfigException('Invalid "copyright" property!', 1006); // @codeCoverageIgnore
        }

        if (!is_bool($config['hasGameLoop'])) {
            throw new CompilerConfigException('Invalid "hasGameLoop" property!', 1006); // @codeCoverageIgnore
        }

        $this->sources     = $config['sources'];
        $this->excludes    = $config['excludes'];
        $this->distFile    = $config['dist'];
        $this->copyright   = $config['copyright'];
        $this->hasGameLoop = $config['hasGameLoop'];

        return $this;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function hasGameLoop(): bool
    {
        return $this->hasGameLoop;
    }

    /**
     * @return string[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    public function getDistFile(): string
    {
        return $this->distFile;
    }
}
