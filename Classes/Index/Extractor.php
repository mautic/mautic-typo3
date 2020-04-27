<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Index;

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

use Bitmotion\Mautic\Domain\Repository\AssetRepository;
use Bitmotion\Mautic\Driver\AssetDriver;
use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Type\File\ImageInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Extractor implements ExtractorInterface
{
    public function extractMetaData(Resource\File $file, array $previousExtractedData = []): array
    {
        $asset = $this->getAsset($file);
        $data = [];

        if (!empty($asset)) {
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

    public function getPriority(): int
    {
        return 5;
    }

    public function getExecutionPriority(): int
    {
        return 5;
    }

    public function getFileTypeRestrictions(): array
    {
        return [];
    }

    public function canProcess(Resource\File $file): bool
    {
        return $file->getStorage()->getDriverType() === AssetDriver::DRIVER_TYPE;
    }

    public function getDriverRestrictions(): array
    {
        return [AssetDriver::DRIVER_TYPE];
    }

    protected function getAsset(Resource\File $file): array
    {
        $mauticAlias = ltrim($file->getIdentifier(), '/asset/');
        $assetApi = GeneralUtility::makeInstance(AssetRepository::class);
        $assets = $assetApi->list($mauticAlias);

        return !empty($assets) ? array_shift($assets) : [];
    }
}
