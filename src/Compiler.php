<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler;

use Velkuns\Codingame\Core\Compiler\Cleaner\CleanerInterface;

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
        return "<?php

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
