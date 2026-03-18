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

namespace Leuchtfeuer\Mautic\Domain\Repository;

use Leuchtfeuer\Mautic\Service\MauticSendFormService;
use Mautic\Api\Forms;
use Mautic\Exception\ContextNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormRepository extends AbstractRepository
{
    /**
     * @var Forms
     */
    protected Forms $formsApi;

    /**
     * @throws ContextNotFoundException
     */
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Forms $formsApi */
        $formsApi = $this->getApi('forms');
        $this->formsApi = $formsApi;
    }

    public function getForm(int $identifier): array
    {
        $form = $this->formsApi->get($identifier);

        if (isset($form['errors'])) {
            foreach ($form['errors'] as $error) {
                $this->logger->critical(sprintf('%s: %s', $error['code'], $error['message']));
            }

            return [];
        }

        return $form['form'];
    }

    public function getAllForms(): array
    {
        return ($this->formsApi->getList('', 0, 999)['forms'] ?? []) ?: [];
    }

    public function createForm(array $parameters): array
    {
        return $this->formsApi->create($parameters) ?: [];
    }

    public function editForm(int $id, array $parameters, bool $createIfNotExists = false): array
    {
        // Unset cachedHtml to not exceed request header field server limit
        $parameters['cachedHtml'] = '';

        return $this->formsApi->edit($id, $parameters, $createIfNotExists) ?: [];
    }

    public function deleteForm(int $id): array
    {
        return $this->formsApi->delete($id) ?: [];
    }

    public function submitForm(int $id, array $data): void
    {
        $data['formId'] = $id;
        // @extensionScannerIgnoreLine
        $url = rtrim(trim((string)$this->authorization->getBaseUrl()), '/') . '/form/submit?formId=' . $id;

        $mauticSendFormService = GeneralUtility::makeInstance(MauticSendFormService::class);
        $code = $mauticSendFormService->submitForm($url, $data);

        if ($code < 200 || $code >= 400) {
            $this->logger->critical(
                sprintf(
                    'An error occured submitting the form with the Mautic id %d to Mautic. Status code %d returned by Mautic.',
                    $id,
                    $code
                )
            );
        }
    }

    public function formExists(int $id): bool
    {
        return $this->getForm($id) !== [];
    }
}
