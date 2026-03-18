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

namespace Leuchtfeuer\Mautic\Index;

use Leuchtfeuer\Mautic\Domain\Repository\AssetRepository;
use Leuchtfeuer\Mautic\Driver\AssetDriver;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Type\File\ImageInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Extractor implements ExtractorInterface
{
    #[\Override]
    public function extractMetaData(File $file, array $previousExtractedData = []): array
    {
        $asset = $this->getAsset($file);
        $data = [];

        if ($asset !== []) {
            $data['description'] = $asset['description'];
            $data['alternative'] = $asset['description'];
            $data['title'] = $asset['title'];

            if ($file->isDeleted() === false) {
                $fileName = $file->getForLocalProcessing(false);
                $imageInfo = GeneralUtility::makeInstance(ImageInfo::class, $fileName);

                if ($imageInfo !== null) {
                    $data['width'] = $imageInfo->getWidth();
                    $data['height'] = $imageInfo->getHeight();
                }
            }
        }

        return $data;
    }

    #[\Override]
    public function getPriority(): int
    {
        return 5;
    }

    #[\Override]
    public function getExecutionPriority(): int
    {
        return 5;
    }

    #[\Override]
    public function getFileTypeRestrictions(): array
    {
        return [];
    }

    #[\Override]
    public function canProcess(File $file): bool
    {
        return $file->getStorage()->getDriverType() === AssetDriver::DRIVER_TYPE;
    }

    #[\Override]
    public function getDriverRestrictions(): array
    {
        return [AssetDriver::DRIVER_TYPE];
    }

    protected function getAsset(File $file): array
    {
        $mauticAlias = ltrim($file->getIdentifier(), '/asset/');
        $assetApi = GeneralUtility::makeInstance(AssetRepository::class);
        $assets = $assetApi->list($mauticAlias);

        return empty($assets) ? [] : array_shift($assets);
    }
}
