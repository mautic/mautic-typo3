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

use Mautic\Api\Assets;
use Mautic\Api\Files;
use Mautic\Exception\ContextNotFoundException;

class AssetRepository extends AbstractRepository
{
    /**
     * @var Assets
     */
    protected $assetsApi;

    /**
     * @var Files
     */
    protected $filesApi;

    /**
     * @throws ContextNotFoundException
     */
    #[\Override]
    protected function injectApis(): void
    {
        /** @var Assets $assetsApi */
        $assetsApi = $this->getApi('assets');
        $this->assetsApi = $assetsApi;

        /** @var Files $filesApi */
        $filesApi = $this->getApi('files');
        $this->filesApi = $filesApi;
    }

    public function list(string $search = '', int $start = 0, int $limit = 0, string $orderBy = '', string $orderByDir = 'ASC', bool $publishedOnly = false, bool $minimal = false): array
    {
        $assets = $this->assetsApi->getList($search, $start, $limit, $orderBy, $orderByDir, $publishedOnly, $minimal);

        return $assets['assets'] ?? [];
    }

    public function upload(string $file, string $title): array
    {
        $this->filesApi->setFolder('assets');
        $response = $this->filesApi->create(['file' => $file]);

        if ($file !== '' && $file !== '0') {
            $response = $this->assetsApi->create([
                'title' => $title,
                'storageLocation' => 'local',
                'file' => $response['file']['name'],
            ]);
        }

        return $response['asset'] ?? [];
    }

    public function get(int $id): array
    {
        $asset = $this->assetsApi->get($id);

        return $asset['asset'] ?? [];
    }

    public function update(int $id, array $data): array
    {
        $asset = $this->assetsApi->edit($id, $data);

        return $asset['asset'] ?? [];
    }

    public function count(): int
    {
        return count($this->list());
    }
}
