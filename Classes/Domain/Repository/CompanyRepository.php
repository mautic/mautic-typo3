<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Companies;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;

class CompanyRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Companies
     */
    protected $companiesApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->companiesApi = $api->newApi('companies', $this->authorization, $this->authorization->getBaseUrl());
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
