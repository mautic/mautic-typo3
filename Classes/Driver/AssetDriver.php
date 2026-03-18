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

namespace Leuchtfeuer\Mautic\Driver;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\AssetRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InvalidPathException;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class AssetDriver extends AbstractHierarchicalFilesystemDriver implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    public const DRIVER_SHORT_NAME = 'mautic';
    public const DRIVER_NAME = 'Mautic';
    public const DRIVER_TYPE = 'mautic';
    public const ROOT_LEVEL_FOLDER = '/';

    protected $capabilities;

    protected string $baseUrl;

    protected array $assets = [];

    protected array $assetsToDelete = [];

    protected bool $cleanUp = true;

    protected bool $assetsLoaded = false;

    protected array $temporaryPaths = [];

    protected array $publicUrls = [];

    /**
     * @var AssetRepository
     */
    protected AssetRepository $assetRepository;

    public function __construct(array $configuration = [])
    {
        parent::__construct($configuration);

        $this->capabilities = ResourceStorage::CAPABILITY_BROWSABLE | ResourceStorage::CAPABILITY_PUBLIC | ResourceStorage::CAPABILITY_WRITABLE;
    }

    public function __destruct()
    {
        foreach ($this->temporaryPaths as $temporaryPath) {
            unlink($temporaryPath);
        }
    }

    #[\Override]
    public function mergeConfigurationCapabilities($capabilities): int
    {
        $this->capabilities &= $capabilities;

        return $this->capabilities;
    }

    #[\Override]
    public function processConfiguration(): void {}

    #[\Override]
    public function initialize(): void
    {
        // @extensionScannerIgnoreLine
        $this->baseUrl = GeneralUtility::makeInstance(YamlConfiguration::class)->getBaseUrl();
    }

    #[\Override]
    public function getPublicUrl($identifier): string
    {
        if (!isset($this->publicUrls[$identifier])) {
            $uriParts = GeneralUtility::trimExplode('/', ltrim($identifier, '/'), true);
            $uriParts = array_map('rawurlencode', $uriParts);
            // @extensionScannerIgnoreLine
            $this->publicUrls[$identifier] = $this->baseUrl . '/' . implode('/', $uriParts);
        }

        return $this->publicUrls[$identifier];
    }

    #[\Override]
    public function hash($fileIdentifier, $hashAlgorithm): string
    {
        return $this->hashIdentifier($fileIdentifier);
    }

    #[\Override]
    public function getDefaultFolder(): string
    {
        return self::ROOT_LEVEL_FOLDER;
    }

    #[\Override]
    public function getRootLevelFolder(): string
    {
        return self::ROOT_LEVEL_FOLDER;
    }

    /**
     * @throws FileDoesNotExistException
     */
    #[\Override]
    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = []): array
    {
        $fileInfo = $this->getAssetData($fileIdentifier);

        if (empty($fileInfo)) {
            throw new FileDoesNotExistException('File does not exist', 1555571365);
        }

        if ($propertiesToExtract !== []) {
            $fileInfo = array_intersect_key($fileInfo, array_flip($propertiesToExtract));
        }

        return $fileInfo;
    }

    #[\Override]
    public function fileExists($identifier): bool
    {
        if (str_ends_with($identifier, '/') || $identifier === '') {
            return false;
        }

        $this->normalizeIdentifier($identifier);

        return $this->objectExists($identifier);
    }

    #[\Override]
    public function folderExists($identifier): bool
    {
        if ($identifier === self::ROOT_LEVEL_FOLDER) {
            return true;
        }
        if (!str_ends_with($identifier, '/')) {
            $identifier .= '/';
        }

        return $this->objectExists($identifier);
    }

    #[\Override]
    public function fileExistsInFolder($fileName, $folderIdentifier): bool
    {
        return $this->objectExists($folderIdentifier . $fileName);
    }

    #[\Override]
    public function folderExistsInFolder($folderName, $folderIdentifier): bool
    {
        return $this->objectExists($folderIdentifier . $folderName . '/');
    }

    #[\Override]
    public function getFolderInFolder($folderName, $folderIdentifier): string
    {
        $identifier = $folderIdentifier . '/' . $folderName . '/';
        $this->normalizeIdentifier($identifier);

        return $identifier;
    }

    #[\Override]
    public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = true): string
    {
        $newFileName = $this->sanitizeFileName($newFileName !== '' ? $newFileName : PathUtility::basename($localFilePath));
        $targetPath = Environment::getVarPath() . '/transient/' . $newFileName;

        $result = move_uploaded_file($localFilePath, $targetPath);

        if ($result === false || !file_exists($targetPath)) {
            throw new \RuntimeException(
                'Adding file ' . $localFilePath . ' at ' . $newFileName . ' failed.',
                1476046453
            );
        }

        $targetIdentifier = '/asset/' . $newFileName;
        $asset = $this->getAssetRepository()->upload($targetPath, $newFileName);

        if ($asset !== []) {
            $assetData = $this->getAssetDataFromResponse($asset);
            $this->assets[$targetIdentifier] = $assetData;
        }

        if ($removeOriginal === true) {
            unlink($targetPath);
        }

        return $targetIdentifier;
    }

    #[\Override]
    public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName): string
    {
        // TODO: Implement later
        $this->logger->debug('moveFileWithinStorage');

        return '';
    }

    #[\Override]
    public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName): string
    {
        // TODO: Implement later
        $this->logger->debug('copyFileWithinStorage');

        return '';
    }

    #[\Override]
    public function replaceFile($fileIdentifier, $localFilePath): bool
    {
        // TODO: Implement later
        $this->logger->debug('replaceFile');

        return true;
    }

    #[\Override]
    public function deleteFile($fileIdentifier)
    {
        return $this->removeFileByIdentifier($fileIdentifier);
    }

    #[\Override]
    public function deleteFolder($folderIdentifier, $deleteRecursively = false)
    {
        return true;
    }

    #[\Override]
    public function getFileForLocalProcessing($fileIdentifier, $writable = true): string
    {
        $this->normalizeIdentifier($fileIdentifier);

        if (isset($this->temporaryPaths[$fileIdentifier])) {
            return $this->temporaryPaths[$fileIdentifier];
        }

        $asset = $this->getAssetData($fileIdentifier);

        if ($asset === []) {
            $asset = $this->getAsset($fileIdentifier);
        }

        return $this->processFile($fileIdentifier, $asset);
    }

    #[\Override]
    public function createFile($fileName, $parentFolderIdentifier): string
    {
        // TODO: Implement later
        $this->logger->debug('createFile');

        return '';
    }

    #[\Override]
    public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = false): string
    {
        return '';
    }

    #[\Override]
    public function getFileContents($fileIdentifier): string
    {
        // TODO: Implement later
        $this->logger->debug('getFileContents');

        return '';
    }

    #[\Override]
    public function setFileContents($fileIdentifier, $contents): int
    {
        // TODO: Implement later
        $this->logger->debug('setFileContents');

        return 0;
    }

    #[\Override]
    public function renameFile($fileIdentifier, $newName): string
    {
        // TODO: Implement later
        $this->logger->debug('renameFile');

        return '';
    }

    #[\Override]
    public function renameFolder($folderIdentifier, $newName): array
    {
        // TODO: Implement later
        $this->logger->debug('renameFolder');

        return [];
    }

    #[\Override]
    public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName): array
    {
        // TODO: Implement later
        $this->logger->debug('moveFolderWithinStorage');

        return [];
    }

    #[\Override]
    public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName): bool
    {
        // TODO: Implement later
        $this->logger->debug('copyFolderWithinStorage');

        return true;
    }

    #[\Override]
    public function isFolderEmpty($folderIdentifier): bool
    {
        return $this->countFilesInFolder($folderIdentifier) > 0;
    }

    /**
     * @throws InvalidPathException
     * @see LocalDriver
     */
    #[\Override]
    public function isWithin($folderIdentifier, $identifier): bool
    {
        $folderIdentifier = $this->canonicalizeAndCheckFileIdentifier($folderIdentifier);
        $entryIdentifier = $this->canonicalizeAndCheckFileIdentifier($identifier);

        if ($folderIdentifier === $entryIdentifier) {
            return true;
        }

        // File identifier canonicalization will not modify a single slash so
        // we must not append another slash in that case.
        if ($folderIdentifier !== '/') {
            $folderIdentifier .= '/';
        }

        return \str_starts_with($entryIdentifier, $folderIdentifier);
    }

    #[\Override]
    public function getFolderInfoByIdentifier($folderIdentifier): array
    {
        $this->normalizeIdentifier($folderIdentifier);

        return [
            'identifier' => $folderIdentifier,
            'name' => basename(rtrim((string)$folderIdentifier, '/')),
            'storage' => $this->storageUid,
        ];
    }

    #[\Override]
    public function getFileInFolder($fileName, $folderIdentifier): string
    {
        $folderIdentifier = $folderIdentifier . '/' . $fileName;
        $this->normalizeIdentifier($folderIdentifier);

        return $folderIdentifier;
    }

    #[\Override]
    public function getFilesInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $filenameFilterCallbacks = [], $sort = '', $sortRev = false): array
    {
        if (($sort !== '' && $sort !== 'file') || $sortRev === true || $this->assetsLoaded === false) {
            $order = $this->getOrder($sort);
            $orderByDir = $sortRev ? 'DESC' : 'ASC';
            $this->rebuildAssetCache($this->getAssetRepository()->list('', $start, $numberOfItems, $order, $orderByDir));
            $this->assetsLoaded = true;
        }

        if ($this->cleanUp === true) {
            $this->removeObsoleteFiles();
            $this->cleanUp = false;
        }

        return array_keys($this->assets);
    }

    #[\Override]
    public function countFilesInFolder($folderIdentifier, $recursive = false, array $filenameFilterCallbacks = []): int
    {
        return count($this->getFilesInFolder($folderIdentifier, 0, 0, false, $filenameFilterCallbacks));
    }

    #[\Override]
    public function getFoldersInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = false, array $folderNameFilterCallbacks = [], $sort = '', $sortRev = false): array
    {
        return [];
    }

    #[\Override]
    public function countFoldersInFolder($folderIdentifier, $recursive = false, array $folderNameFilterCallbacks = []): int
    {
        return count($this->getFoldersInFolder($folderIdentifier, 0, 0, $recursive, $folderNameFilterCallbacks));
    }

    #[\Override]
    public function dumpFileContents($identifier): string
    {
        $this->logger->debug('dumpFileContents');

        return '';
    }

    #[\Override]
    public function getPermissions($identifier): array
    {
        $read = true;
        $write = false;

        if (($this->objectExists($identifier) && $identifier) || $identifier === self::ROOT_LEVEL_FOLDER) {
            // TODO: Support editing files later
            $write = false;
        }

        if ($this->shouldDelete($identifier)) {
            $write = true;
        }

        return [
            'r' => $read,
            'w' => $write,
        ];
    }

    protected function rebuildAssetCache(array $assets): void
    {
        $this->assets = [];
        $this->buildAssetCache($assets);
    }

    protected function buildAssetCache(array $assets): void
    {
        foreach ($assets as $asset) {
            $data = $this->getAssetDataFromResponse($asset);
            $identifier = $data['identifier'];
            $this->normalizeIdentifier($identifier);
            $this->assets[$identifier] = $data;
        }
    }

    protected function objectExists(string $identifier): bool
    {
        return $identifier === self::ROOT_LEVEL_FOLDER || $this->getAssetData($identifier);
    }

    protected function shouldDelete(string $identifier): bool
    {
        $this->normalizeIdentifier($identifier);

        return isset($this->assetsToDelete[$identifier]);
    }

    protected function getAssetData(string $identifier): array
    {
        $this->normalizeIdentifier($identifier);

        if (!isset($this->assets[$identifier])) {
            if ($this->assetsLoaded === false) {
                $asset = $this->getAsset($identifier);
                if ($asset !== []) {
                    $this->assets[$identifier] = $asset;

                    return $asset;
                }
            }

            // Find file in database (should only happen when mautic asset was deleted).
            $file = $this->getFileByIdentifier($identifier);
            if ($file !== []) {
                $this->assetsToDelete[$identifier] = $file;
                $this->assets[$identifier] = $file;
            }
        }

        return $this->assets[$identifier] ?? [];
    }

    protected function getAsset(string $identifier): array
    {
        $identifier = PathUtility::basename($identifier);
        $assets = $this->getAssetRepository()->list($identifier, 0, 1);
        $asset = [];

        if ($assets !== []) {
            $asset = array_shift($assets);
        }

        return $asset;
    }

    protected function getAssetDataFromResponse(array $asset): array
    {
        try {
            $dateAdded = new \DateTime((string)$asset['dateAdded']);
            $dateModified = ($asset['dateModified'] !== null) ? new \DateTime((string)$asset['dateModified']) : $dateAdded;
        } catch (\Exception) {
            $dateAdded = new \DateTime();
            $dateModified = new \DateTime();
        }

        $identifier = '/asset/' . $asset['alias'];

        $item['name'] = $asset['title'];
        $item['mimetype'] = $asset['mime'];
        $item['ctime'] = $dateAdded->getTimestamp();
        $item['mtime'] = $dateModified->getTimestamp();
        $item['size'] = $asset['size'];
        $item['extension'] = $asset['extension'];
        $item['identifier'] = $identifier;
        $item['identifier_hash'] = $this->hashIdentifier($identifier);
        $item['storage'] = $this->storageUid;
        $item['folder_hash'] = $this->hashIdentifier(PathUtility::dirname($identifier));

        return $item;
    }

    protected function normalizeIdentifier(string &$identifier): void
    {
        $identifier = str_replace('//', '/', $identifier);
        if ($identifier !== '/') {
            $identifier = ltrim($identifier, '/');
        }
    }

    protected function getOrder(string $sorting): string
    {
        return match ($sorting) {
            'file' => 'alias',
            'fileext' => 'extension',
            'tstamp' => 'dateModified',
            'size' => 'size',
            default => 'alias',
        };
    }

    protected function removeObsoleteFiles(): void
    {
        $files = $this->getFilesFromDatabase();

        foreach ($files as $file) {
            $identifier = $file['identifier'];
            $this->normalizeIdentifier($identifier);

            if (!isset($this->assets[$identifier])) {
                $this->removeFileFromDatabase($file['uid']);
            }
        }
    }

    protected function getFilesFromDatabase(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');

        return $queryBuilder
            ->select('*')
            ->from('sys_file')->where($queryBuilder->expr()->eq('storage', $this->storageUid))->executeQuery()->fetchAllAssociative();
    }

    protected function getFileByIdentifier(string $identifier): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');

        $file = $queryBuilder
            ->select('*')
            ->from('sys_file')
            ->where($queryBuilder->expr()->eq('storage', $this->storageUid))->andWhere($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter('/' . $identifier)))->executeQuery()->fetchAssociative();

        return ($file === false) ? [] : $file;
    }

    protected function removeFileByIdentifier(string $identifier): bool
    {
        $this->normalizeIdentifier($identifier);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');

        return (bool)$queryBuilder
            ->delete('sys_file')
            ->where($queryBuilder->expr()->eq('storage', $this->storageUid))->andWhere($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter('/' . $identifier)))->executeStatement();
    }

    protected function removeFileFromDatabase(int $uid): void
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $file = $fileRepository->findByIdentifier($uid);
        $file->getStorage()->deleteFile($file);
    }

    protected function getAssetRepository(): AssetRepository
    {
        if (!$this->assetRepository instanceof AssetRepository) {
            $this->assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
        }

        return $this->assetRepository;
    }

    protected function processFile(string $fileIdentifier, array $asset): string
    {
        $temporaryPath = $this->getTemporaryPathForFile($fileIdentifier . '.' . $asset['extension']);
        $content = GeneralUtility::getUrl($asset['downloadUrl']);
        GeneralUtility::writeFile($temporaryPath, $content);

        if (!is_file($temporaryPath)) {
            throw new \RuntimeException('Copying file ' . $fileIdentifier . ' to temporary path failed.', 1555571767);
        }

        if (!isset($this->temporaryPaths[$fileIdentifier])) {
            $this->temporaryPaths[$fileIdentifier] = $temporaryPath;
        }

        return $temporaryPath;
    }
}
