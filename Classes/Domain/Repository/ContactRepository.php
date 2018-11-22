<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

use Bitmotion\Mautic\Mautic\AuthorizationFactory;
use Mautic\Api\Contacts;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\SingletonInterface;

class ContactRepository implements SingletonInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Contacts
     */
    protected $contactsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->contactsApi = $api->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function findContactSegments(int $id): array
    {
        $segments = $this->contactsApi->getContactSegments($id);

        return $segments['lists'] ?? [];
    }

    public function createContact(array $parameters): array
    {
        return $this->contactsApi->create($parameters) ?: [];
    }

    public function editContact(int $id, array $data): array
    {
        return $this->contactsApi->edit($id, $data, false);
    }

    public function getContact(int $id): array
    {
        $contact = $this->contactsApi->get($id);

        return $contact['contact'] ?? [];
    }

    public function modifyContactPoints(int $id, int $modifier, array $data = [])
    {
        if ($modifier > 0) {
            $this->contactsApi->addPoints($id, $modifier, $data);
        } else {
            $this->contactsApi->subtractPoints($id, abs($modifier), $data);
        }
    }
}
