<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Velkuns\Codingame\Core\Compiler\Tests;

use PHPUnit\Framework\TestCase;
use Velkuns\Codingame\Core\Compiler\Compiler;
use Velkuns\Codingame\Core\Compiler\Config;

/**
 * Class CompilerTest
 *
 * @author Romain Cottard
 */
class CompilerTest extends TestCase
{
    public function testICanCompileTheCompiler(): void
    {
        $rootDir  = realpath(__DIR__ . '/..') . '/';
        $compiler = new Compiler($rootDir, (new Config())->load(__DIR__ . '/config/compiler.json'));
        $compiler->run();

        $this->assertTrue(file_exists($rootDir . '/dist/codingame.php'));

        $expectedFile = file_get_contents($rootDir . '/dist/expected_codingame.php');
        $compiledFile = file_get_contents($rootDir . '/dist/codingame.php');

        //$this->assertSame($expectedFile, $compiledFile); - not reliable if tested on another php version
    }
}
