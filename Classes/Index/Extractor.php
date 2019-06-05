<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Index;

use Bitmotion\Mautic\Domain\Repository\AssetRepository;
use Bitmotion\Mautic\Driver\AssetDriver;
use TYPO3\CMS\Core\Resource;
use TYPO3\CMS\Core\Resource\Index\ExtractorInterface;
use TYPO3\CMS\Core\Type\File\ImageInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Extractor implements ExtractorInterface
{
    public function extractMetaData(Resource\File $file, array $previousExtractedData = []): array
    {
        $asset = $this->getAsset($file);

        $data = [
            'description' => $asset['dscription'],
            'alternative' => $asset['description'],
            'title' => $asset['title'],
        ];

        $fileName = $file->getForLocalProcessing(false);
        $imageInfo = GeneralUtility::makeInstance(ImageInfo::class, $fileName);

        if ($imageInfo !== null) {
            $data['width'] = $imageInfo->getWidth();
            $data['height'] = $imageInfo->getHeight();
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
        $assetApi = GeneralUtility::makeInstance(ObjectManager::class)->get(AssetRepository::class);
        $assets = $assetApi->list($mauticAlias);

        return !empty($assets) ? array_shift($assets) : [];
    }
}
