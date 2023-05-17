<?php

declare(strict_types=1);
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

use Mautic\Api\CompanyFields;
use Mautic\Api\ContactFields;
use Mautic\Exception\ContextNotFoundException;

class FieldRepository extends AbstractRepository
{
    /**
     * @var ContactFields
     */
    protected $contactFieldsApi;

    /**
     * @var CompanyFields
     */
    protected $companyFieldsApi;

    /**
     * @throws ContextNotFoundException
     */
    protected function injectApis(): void
    {
        $this->contactFieldsApi = $this->getApi('contactFields');
        $this->companyFieldsApi = $this->getApi('companyFields');
    }

    public function editContactField(int $id, array $params): array
    {
        return $this->contactFieldsApi->edit($id, $params);
    }

    public function editCompanyField(int $id, array $params): array
    {
        return $this->companyFieldsApi->edit($id, $params);
    }

    public function getContactFields(string $query = ''): array
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
