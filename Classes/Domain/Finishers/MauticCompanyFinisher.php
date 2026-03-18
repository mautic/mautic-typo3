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

namespace Leuchtfeuer\Mautic\Domain\Finishers;

use Leuchtfeuer\Mautic\Domain\Repository\CompanyRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

/**
 * TODO: Support companies
 */
class MauticCompanyFinisher extends AbstractFinisher
{
    protected object $companyRepository;

    public function __construct()
    {
        $this->companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
    }

    /**
     * Creates a company in Mautic if enough data is present from the collected form results
     */
    #[\Override]
    protected function executeInternal(): ?string
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

        if ($mauticFields === []) {
            return null;
        }

        $this->companyRepository->createCompany($mauticFields);

        return null;
    }
}
