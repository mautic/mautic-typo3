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

namespace Leuchtfeuer\Mautic\Tests\Unit\Domain\Model\FormElement;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Model\FormElement\CountryListFormElement;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CountryListFormElementTest extends UnitTestCase
{
    private const COUNTRY_URL = 'https://m.test/app/bundles/CoreBundle/Assets/json/countries.json';

    protected bool $resetSingletonInstances = true;

    private RequestFactory&MockObject $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        unset($GLOBALS['TYPO3_REQUEST']);
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getBaseUrl')->willReturn('https://m.test/');
        GeneralUtility::setSingletonInstance(YamlConfiguration::class, $configuration);
        $this->requestFactory = $this->createMock(RequestFactory::class);
        $client = new MauticHttpClient($this->requestFactory, $configuration);
        $client->setLogger(new NullLogger());
        GeneralUtility::setSingletonInstance(MauticHttpClient::class, $client);
    }

    #[Test]
    public function countriesAreLoadedFromMauticViaClient(): void
    {
        $this->requestFactory->method('request')->with(self::COUNTRY_URL)
            ->willReturn(new Response(200, [], '["Germany","France"]'));

        self::assertSame(['Germany' => 'Germany', 'France' => 'France'], $this->getCountryOptions());
    }

    #[Test]
    public function unreachableMauticYieldsEmptyCountryList(): void
    {
        $this->requestFactory->method('request')
            ->willThrowException(new ConnectException('Connection timed out', new Request('GET', self::COUNTRY_URL)));

        self::assertSame([], $this->getCountryOptions());
    }

    #[Test]
    public function invalidJsonYieldsEmptyCountryList(): void
    {
        $this->requestFactory->method('request')->willReturn(new Response(200, [], '<html>'));

        self::assertSame([], $this->getCountryOptions());
    }

    /**
     * @return array<string, string>
     */
    private function getCountryOptions(): array
    {
        $subject = new CountryListFormElement('country', 'CountryList');
        $subject->setLogger(new NullLogger());
        $subject->setOptions([]);

        return $subject->getProperties()['options'];
    }
}
