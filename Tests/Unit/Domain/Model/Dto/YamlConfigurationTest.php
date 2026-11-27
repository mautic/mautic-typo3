<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\Tests\Unit\Domain\Model\Dto;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Service\TokenStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Registry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class YamlConfigurationTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] = [];
    }

    #[Test]
    public function timeoutDefaultsToFiveSeconds(): void
    {
        self::assertSame(5, $this->createConfiguration([])->getTimeout());
    }

    #[Test]
    public function timeoutIsReadFromYaml(): void
    {
        self::assertSame(12, $this->createConfiguration(['timeout' => 12])->getTimeout());
    }

    #[Test]
    public function timeoutZeroMeansNoTimeout(): void
    {
        self::assertSame(0, $this->createConfiguration(['timeout' => 0])->getTimeout());
    }

    #[Test]
    public function negativeTimeoutIsTreatedAsZero(): void
    {
        self::assertSame(0, $this->createConfiguration(['timeout' => -3])->getTimeout());
    }

    #[Test]
    public function timeoutStringFromFormIsCastToInt(): void
    {
        self::assertSame(7, $this->createConfiguration(['timeout' => '7'])->getTimeout());
    }

    #[Test]
    public function emptyTimeoutFromFormFallsBackToDefault(): void
    {
        self::assertSame(5, $this->createConfiguration(['timeout' => ''])->getTimeout());
    }

    #[Test]
    public function normalizeTimeoutTurnsEmptyFormValueIntoDefault(): void
    {
        self::assertSame(5, YamlConfiguration::normalizeTimeout(''));
    }

    #[Test]
    public function normalizeTimeoutKeepsZeroFromForm(): void
    {
        self::assertSame(0, YamlConfiguration::normalizeTimeout('0'));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function nonNumericTimeoutDataProvider(): array
    {
        return [
            'text' => ['abc'],
            'number with unit' => ['7s'],
            'decimal' => ['2.5'],
        ];
    }

    #[Test]
    #[DataProvider('nonNumericTimeoutDataProvider')]
    public function nonNumericTimeoutFallsBackToDefault(mixed $timeout): void
    {
        self::assertSame(5, YamlConfiguration::normalizeTimeout($timeout));
    }

    /**
     * @param array<string, mixed> $yaml
     */
    private function createConfiguration(array $yaml): YamlConfiguration
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('get')->willReturnArgument(2);
        $tokenStorage = new TokenStorage($registry);

        return new class ($yaml, $tokenStorage) extends YamlConfiguration {
            /**
             * @param array<string, mixed> $yaml
             */
            public function __construct(private readonly array $yaml, TokenStorage $tokenStorage)
            {
                parent::__construct($tokenStorage);
            }

            protected function getYamlConfiguration(): array
            {
                return $this->yaml;
            }
        };
    }
}
