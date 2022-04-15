<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
            echo 'FAILED' . PHP_EOL . $result . PHP_EOL;
        }

        return $this;
    }
}

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
            throw new CompilerConfigException("Cannot read $file (not exists or not readable)!", 1001);
        }

        try {
            /** @var array<string,bool|string|array<string>> $config */
            $config = \json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $exception) {
            throw new CompilerConfigException('Cannot load file config & json decode it!', 1002, $exception);
        }

        if (!is_array($config['sources'])) {
            throw new CompilerConfigException('Invalid "sources" property!', 1003);
        }

        if (!is_array($config['excludes'])) {
            throw new CompilerConfigException('Invalid "excludes" property!', 1004);
        }

        if (!is_string($config['dist'])) {
            throw new CompilerConfigException('Invalid "dist" property!', 1005);
        }

        if (!is_string($config['copyright'])) {
            throw new CompilerConfigException('Invalid "copyright" property!', 1006);
        }

        if (!is_bool($config['hasGameLoop'])) {
            throw new CompilerConfigException('Invalid "hasGameLoop" property!', 1006);
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

/**
 * Class Compiler
 *
 * @author Romain Cottard
 */
class Compiler
{
    /** @var string rootDir */
    private $rootDir;

    /** @var Config config */
    private $config;

    /**
     * Compiler constructor.
     *
     * @param string $rootDir
     * @param Config $config
     */
    public function __construct(string $rootDir, Config $config)
    {
        $this->rootDir = realpath($rootDir) . '/';
        $this->config  = $config;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $compiledCode = str_replace('#COMPILED_CODE#', $this->compile(), $this->getTemplate());

        (new FileDistWriter($this->rootDir . ltrim($this->config->getDistFile(), '/')))
            ->write($compiledCode)
            ->check()
        ;
    }

    /**
     * @return string
     */
    private function compile(): string
    {
        echo 'Compiling: ... ';
        $compiled = '';

        $fileReader = new FileSourceReader();
        /** @var CleanerInterface[] $cleaners */
        $cleaners   = [
            new Cleaner\PhpTagCleaner(),
            new Cleaner\HeaderCleaner($this->config->getCopyright()),
            new Cleaner\DeclareCleaner(),
            new Cleaner\NamespaceCleaner(),
            new Cleaner\NamespaceUsesCleaner(),
            new Cleaner\MultiEndLineCleaner(),
            new Cleaner\CodeCoverageIgnoreCleaner(),
        ];

        foreach ($this->config->getSources() as $directory) {
            $fullPathname = $this->rootDir . '/' . $directory;

            $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($fullPathname);

            /** @var \SplFileObject $file */
            foreach (new \RecursiveIteratorIterator($recursiveDirectoryIterator) as $file) {
                $dirname = str_replace($this->rootDir, '', dirname($file->getPathname()));

                if ($file->isDir() || $file->getExtension() !== 'php' || in_array($dirname, $this->config->getExcludes())) {
                    continue;
                }

                $content = $fileReader->read($file->getPathname());
                foreach ($cleaners as $cleaner) {
                    $content = $cleaner->clean($content);
                }
                $compiled .= $content;
            }
        }

        echo 'done' . PHP_EOL;

        return $compiled;
    }

    /**
     * @return string
     */
    private function getTemplate(): string
    {
        return "

/*
 * Copyright (c) {$this->config->getCopyright()}
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#COMPILED_CODE#

(new Application(new Game(), " . var_export($this->config->hasGameLoop(), true) . "))->run();
";
    }
}

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

/**
 * Class CompilerException
 *
 * @author Romain Cottard
 */
class CompilerException extends \RuntimeException
{
}

/**
 * Class CompilerConfigException
 *
 * @author Romain Cottard
 */
class CompilerConfigException extends CompilerException
{
}

class MultiEndLineCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            "`^\n+$`m",
        ];
    }
}

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

class PhpTagCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '`<\?php`',
        ];
    }
}

class NamespaceCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '``',
        ];
    }
}

class DeclareCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '`declare\(strict_types=1\);`',
        ];
    }
}

class CodeCoverageIgnoreCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '` ?// ?@codeCoverageIgnore`',
        ];
    }
}

class NamespaceUsesCleaner extends AbstractCleaner
{
    protected function getPatterns(): array
    {
        return [
            '`^use .+;$`m',
        ];
    }
}

interface CleanerInterface
{
    public function clean(string $string): string;
}

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


(new Application(new Game(), false))->run();
