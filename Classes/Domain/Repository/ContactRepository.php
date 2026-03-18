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

use Mautic\Api\Contacts;
use Mautic\Exception\ContextNotFoundException;

class ContactRepository extends AbstractRepository
{
    /**
     * @var Contacts
     */
    protected Contacts $contactsApi;

    /**
     * @throws ContextNotFoundException
     */
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Contacts $contactsApi */
        $contactsApi = $this->getApi('contacts');
        $this->contactsApi = $contactsApi;
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

    public function modifyContactPoints(int $id, int $modifier, array $data = []): void
    {
        if ($modifier > 0) {
            $this->contactsApi->addPoints($id, $modifier, $data);
        } else {
            $this->contactsApi->subtractPoints($id, abs($modifier), $data);
        }
    }

    public function addDnc(int $id, string $channel = 'email', int $reason = Contacts::MANUAL, int $channelId = null, string $comments = 'via API'): void
    {
        $this->contactsApi->addDnc($id, $channel, $reason, $channelId, $comments);
    }

    public function removeDnc(int $id, string $channel = 'email'): void
    {
        $this->contactsApi->removeDnc($id, $channel);
    }
}
