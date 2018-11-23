<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Bitmotion\Mautic\Service\MauticSendFormService;
use Mautic\Api\Forms;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class FormRepository implements SingletonInterface
{
    use LoggerAwareTrait;

    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Forms
     */
    protected $formsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formsApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
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
        return $this->formsApi->edit($id, $parameters, $createIfNotExists) ?: [];
    }

    public function deleteForm(int $id): array
    {
        return $this->formsApi->delete($id) ?: [];
    }

    public function submitForm(int $id, array $data)
    {
        $data['formId'] = $id;
        $url = rtrim(trim($this->authorization->getBaseUrl()), '/') . '/form/submit?formId=' . $id;

        $mauticSendFormService = GeneralUtility::makeInstance(ObjectManager::class)->get(MauticSendFormService::class);
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
}
