<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

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

use Bitmotion\Mautic\Exception\SubmitFormException;
use Bitmotion\Mautic\Service\MauticSendFormService;
use Mautic\Api\Forms;
use Mautic\Exception\ContextNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormRepository extends AbstractRepository
{
    /**
     * @var Forms
     */
    protected $formsApi;

    /**
     * @throws ContextNotFoundException
     */
    protected function injectApis(): void
    {
        $this->formsApi = $this->getApi('forms');
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
        return $this->formsApi->getList('', 0, 999)['forms'] ?: [];
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
        $url = rtrim(trim($this->authorization->getBaseUrl()), '/') . '/form/submit?formId=' . $id;

        /** @var MauticSendFormService $mauticSendFormService */
        $mauticSendFormService = GeneralUtility::makeInstance(MauticSendFormService::class);
        try {
            $response = $mauticSendFormService->submitForm($url, $data);
            $code = $response->getStatusCode();

            if ($code < 200 || $code >= 400) {
                $errorMsg = \sprintf(
                    'An error occured submitting the form with the Mautic id %d to Mautic. Status code %d returned by Mautic.',
                    $id,
                    $code
                );
                throw new SubmitFormException($errorMsg, 1630398752303);
            }

            if (302 === $code) {
                $content = $response->getBody()->getContents();
                if (\preg_match('/mauticError=.*/', $content)) {
                    $errorMsg = \sprintf(
                        'An error during form submission with the Mautic id %d to Mautic. Status code %d with response: %s.',
                        $id,
                        $code,
                        $content
                    );

                    throw new SubmitFormException($content, 1630395758101);
                }
            }
        } catch (\Throwable $throwable) {
            $errorMsg = \sprintf(
                'An error occured submitting the form with the Mautic id %d to Mautic. Error: %s.',
                $id,
                $throwable->getMessage()
            );
            throw new SubmitFormException($errorMsg, 1630399106988);
        }
    }

    public function formExists(int $id): bool
    {
        return !empty($this->getForm($id));
    }
}
