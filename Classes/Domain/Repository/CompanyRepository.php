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
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Companies $companiesApi */
        $companiesApi = $this->getApi('companies');
        $this->companiesApi = $companiesApi;
    }

    public function createCompany(array $parameters): mixed
    {
        return $this->companiesApi->create($parameters);
    }

    public function editCompany(int $id, array $parameters): mixed
    {
        return $this->companiesApi->edit($id, $parameters, false);
    }
}
