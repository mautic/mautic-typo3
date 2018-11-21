<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\CompanyFields;
use Mautic\Api\ContactFields;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\SingletonInterface;

class FieldRepository implements SingletonInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var ContactFields
     */
    protected $contactFieldsApi;

    /**
     * @var CompanyFields
     */
    protected $companyFieldsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->contactFieldsApi = $api->newApi('contactFields', $this->authorization, $this->authorization->getBaseUrl());
        $this->companyFieldsApi = $api->newApi('companyFields', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function editContactField(int $id, array $params): array
    {
        return $this->contactFieldsApi->edit($id, $params);
    }

    public function editCompanyField(int $id, array $params): array
    {
        return $this->companyFieldsApi->edit($id, $params);
    }

    public function getContactFields(string $query = '', bool $onlyActive = true): array
    {
        $response = $this->contactFieldsApi->getList($query);
        $fields = $response['fields'] ?? [];
        $activeFields = [];

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($field['isPublished']) {
                    $activeFields[] = $field;
                }
            }
        }

        return $activeFields;
    }

    public function getCompanyFields(string $query = ''): array
    {
        $response = $this->companyFieldsApi->getList($query);

        return $response['fields'] ?? [];
    }

    public function getContactFieldByAlias(string $alias): array
    {
        $fields = $this->getContactFields($alias);

        foreach ($fields as $field) {
            if ($field['alias'] === $alias) {
                return $field;
            }
        }

        return [];
    }
}
