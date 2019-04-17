<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Mautic\Api\Companies;
use Mautic\Exception\ContextNotFoundException;

class CompanyRepository extends AbstractRepository
{
    /**
     * @var Companies
     */
    protected $companiesApi;

    /**
     * @throws ContextNotFoundException
     */
    protected function injectApis(): void
    {
        $this->companiesApi = $this->getApi('companies');
    }

    public function createCompany(array $parameters)
    {
        return $this->companiesApi->create($parameters);
    }

    public function editCompany(int $id, array $parameters)
    {
        return $this->companiesApi->edit($id, $parameters, false);
    }
}
