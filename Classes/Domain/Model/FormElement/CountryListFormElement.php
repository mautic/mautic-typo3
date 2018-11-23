<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Model\FormElement;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class CountryListFormElement extends GenericFormElement
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $countryFile = '/app/bundles/CoreBundle/Assets/json/countries.json';

    /**
     * @var string
     */
    protected $locale;

    public function __construct(
        string $identifier,
        string $type,
        string $locale = '',
        Logger $logger = null
    ) {
        parent::__construct($identifier, $type);

        $authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->baseUrl = $authorization->getBaseUrl();
        $this->locale = $locale ?: $GLOBALS['TSFE']->lang;
    }

    public function setOptions(array $options, bool $reset = false)
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
        $countryJson = GeneralUtility::getUrl($this->baseUrl . $this->countryFile, 0, null, $report);

        // cURL errors return errorCode 0, so we can not check for "if ($report['error'] !== 0) { ... }"
        // TODO: Datei lokal laden
        // TODO: Core "Bug?" - Nein
        if ($report['message'] !== '') {
            $this->logger->critical($report['message']);

            return [];
        }

        $countries = json_decode($countryJson, true);

        return array_combine($countries, $countries);
    }

    /**
     * Mautic does not return localized country names, so we use EXT:static_info_tables for this.
     */
    protected function localizeCountries(array &$countries)
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables_' . $this->locale)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_countries');
            $countryNames = $queryBuilder
                ->select('*')
                ->from('static_countries')
                ->where(
                    $queryBuilder->expr()->in(
                        'cn_short_en',
                        $queryBuilder->createNamedParameter(array_keys($countries), Connection::PARAM_STR_ARRAY)
                    )
                )
                ->execute()
                ->fetchAll();

            foreach ($countryNames as $countryName) {
                if (!empty($countryName['cn_short_' . $this->locale])) {
                    $countries[$countryName['cn_short_en']] = $countryName['cn_short_' . $this->locale];
                }
            }
        }
    }

    protected function sortCountries(array &$countries)
    {
        asort($countries);
        if (class_exists(\Collator::class)) {
            $collator = new \Collator(setlocale(LC_COLLATE, 0));
            $collator->asort($countries);
        }
    }
}
