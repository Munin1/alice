<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\FileLocator;

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\FileLocator\DefaultFileLocator
 */
class DefaultFileLocatorTest extends TestCase
{
    /**
     * @var DefaultFileLocator
     */
    private $locator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->locator = new DefaultFileLocator();
    }

    public function testIsAFileLocator()
    {
        $this->assertTrue(is_a(DefaultFileLocator::class, FileLocatorInterface::class, true));
    }

    /**
     * @dataProvider provideAbsolutePaths
     */
    public function testCanDetectAbsolutePaths($path)
    {
        $reflectionObject = new \ReflectionObject($this->locator);
        $methodReflection = $reflectionObject->getMethod('isAbsolutePath');
        $methodReflection->setAccessible(true);

        $this->assertTrue(
            $methodReflection->invoke($this->locator, $path),
            '->isAbsolutePath() returns true for an absolute path'
        );
    }

    public function testCanLocateFiles()
    {
        $this->assertEquals(
            __FILE__,
            $this->locator->locate('DefaultFileLocatorTest.php', __DIR__)
        );

        $this->assertEquals(
            __FILE__,
            $this->locator->locate(__DIR__.DIRECTORY_SEPARATOR.'DefaultFileLocatorTest.php')
        );
    }

    public function testThrowsExceptionIfEmptyFileNamePassed()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('An empty file name is not valid to be located.');

        $this->locator->locate('');
    }

    public function testThrowsExceptionIfTheFileDoesNotExists()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageMatches('/^The file "(.+?)foobar.xml" does not exist\.$/');

        $this->locator->locate('foobar.xml', __DIR__);
    }

    public function testLocatingFileThrowsExceptionIfTheFileDoesNotExistsInAbsolutePath()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageMatches('/^The file "(.+?)foobar.xml" does not exist\.$/');

        $this->locator->locate(__DIR__.'/Fixtures/foobar.xml');
    }

    public function provideAbsolutePaths()
    {
        return [
            ['/foo.xml'],
            ['\\server\\foo.xml'],
            ['c:\\\\foo.xml'],
            ['c:/foo.xml'],
            ['https://server/foo.xml'],
            ['phar://server/foo.xml'],
        ];
    }
}
