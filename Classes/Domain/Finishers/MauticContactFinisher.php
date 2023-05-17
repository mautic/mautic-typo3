<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Domain\Finishers;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class MauticContactFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $contactRepository;

    public function __construct(string $finisherIdentifier = '')
    {
        parent::__construct($finisherIdentifier);

        $this->contactRepository = GeneralUtility::makeInstance(ContactRepository::class);
    }

    /**
     * Creates a contact in Mautic if enough data is present from the collected form results
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

        $contact = [];
        $mauticFields['ipAddress'] = $_SERVER['REMOTE_ADDR'];

        if (isset($_COOKIE['mtc_id'])) {
            $contact = $this->contactRepository->getContact((int)$_COOKIE['mtc_id']);
        }

        if (!empty($contact) && isset($contact['id'])) {
            $response = $this->contactRepository->editContact($contact['id'], $mauticFields);
        } else {
            $response = $this->contactRepository->createContact($mauticFields);
        }

        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->logger->critical(sprintf('%s: %s', (string)$error['code'], $error['message']));
            }
        }
    }
}
