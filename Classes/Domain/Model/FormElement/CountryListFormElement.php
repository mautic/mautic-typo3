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

namespace Leuchtfeuer\Mautic\Domain\Model\FormElement;

use Doctrine\DBAL\Connection;
use Leuchtfeuer\Mautic\Mautic\AuthorizationFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class CountryListFormElement extends GenericFormElement implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $baseUrl;

    /**
     * @var string
     */
    protected string $countryFile = '/app/bundles/CoreBundle/Assets/json/countries.json';

    /**
     * @var string
     */
    protected string $locale;

    public function __construct(
        string $identifier,
        string $type,
        string $locale = ''
    ) {
        parent::__construct($identifier, $type);

        $authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        // @extensionScannerIgnoreLine
        $this->baseUrl = $authorization->getBaseUrl();

        if ($locale === '') {
            // Try to get locale from the request (TYPO3 v12 way)
            $locale = 'en'; // Default fallback

            if (isset($GLOBALS['TYPO3_REQUEST'])) {
                $siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
                if ($siteLanguage) {
                    // Get locale string (e.g., 'en_US.UTF-8') and extract language code
                    $localeString = (string)$siteLanguage->getLocale();
                    $locale = explode('_', explode('.', $localeString)[0])[0];
                }
            }
        }
        $this->locale = $locale;
    }

    #[\Override]
    public function setOptions(array $options, bool $reset = false): void
    {
        parent::setOptions($options);

        // This sucks. This really, really sucks
        $countries = $this->getCountries();

        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->localizeCountries($countries);
            $this->sortCountries($countries);
        }

        $this->setProperty('options', $countries);
    }

    protected function getCountries(): array
    {
        $report = [];
        // @extensionScannerIgnoreLine
        $countryJson = @file_get_contents($this->baseUrl . $this->countryFile);

        // cURL errors return errorCode 0, so we can not check for "if ($report['error'] !== 0) { ... }"
        // TODO: Datei lokal laden
        //Currently disabled until the $report variable is used
        /**
        if ($report['message'] !== '') {
            $this->logger->critical($report['message']);
            return [];
        }
         */
        $countries = json_decode($countryJson, true);

        return array_combine($countries, $countries);
    }

    /**
     * Mautic does not return localized country names, so we use EXT:static_info_tables for this.
     */
    protected function localizeCountries(array &$countries): void
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables_' . $this->locale)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_countries');
            $countryNames = $queryBuilder
                ->select('*')
                ->from('static_countries')->where($queryBuilder->expr()->in(
                    'cn_short_en',
                    $queryBuilder->createNamedParameter(array_keys($countries), Connection::PARAM_STR_ARRAY)
                ))->executeQuery()->fetchAllAssociative();

            foreach ($countryNames as $countryName) {
                if (!empty($countryName['cn_short_' . $this->locale])) {
                    $countries[$countryName['cn_short_en']] = $countryName['cn_short_' . $this->locale];
                }
            }
        }
    }

    protected function sortCountries(array &$countries): void
    {
        asort($countries);
        if (class_exists(\Collator::class)) {
            $collator = new \Collator(setlocale(LC_COLLATE, '0') ?: null);
            $collator->asort($countries);
        }
    }
}
