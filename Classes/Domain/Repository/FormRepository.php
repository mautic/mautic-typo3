<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Bitmotion\Mautic\Service\MauticSendFormService;
use Mautic\Api\Forms;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class FormRepository implements SingletonInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Forms
     */
    protected $formsApi;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(AuthInterface $authorization = null, LoggerInterface $logger = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formsApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
        $this->logger = $logger ?: GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function getForm(int $identifier): array
    {
        return $this->formsApi->get($identifier);
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
