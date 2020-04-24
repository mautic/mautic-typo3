<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Finishers;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Bitmotion\Mautic\Domain\Repository\CompanyRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

/**
 * TODO: Support companies
 */
class MauticCompanyFinisher extends AbstractFinisher
{
    protected $companyRepository;

    public function __construct(string $finisherIdentifier = '')
    {
        parent::__construct($finisherIdentifier);

        $this->companyRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(CompanyRepository::class);
    }

    /**
     * Creates a company in Mautic if enough data is present from the collected form results
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();

        $mauticFields = [];

        foreach ($this->finisherContext->getFormValues() as $key => $value) {
            $formElement = $formDefinition->getElementByIdentifier($key);

            if ($formElement instanceof GenericFormElement) {
                $properties = $formElement->getProperties();
                if (!empty($properties['mauticTable'])) {
                    $mauticFields[$properties['mauticTable']] = $value;
                }
            }
        }

        if (\count($mauticFields) === 0) {
            return;
        }

        $this->companyRepository->createCompany($mauticFields);
    }
}
