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

use GuzzleHttp\Exception\ConnectException;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\AssetRepository;
use Leuchtfeuer\Mautic\Http\MauticHttpClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Capabilities;
use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InvalidPathException;
use TYPO3\CMS\Core\Resource\FileRepository;
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

    protected Capabilities $capabilities;

    protected string $baseUrl;

    protected array $assets = [];

    protected array $assetsToDelete = [];

    protected bool $cleanUp = false;

    protected bool $assetsLoaded = false;

    protected array $temporaryPaths = [];

    protected array $publicUrls = [];

    protected AssetRepository $assetRepository;

    public function __construct(array $configuration = [])
    {
        parent::__construct($configuration);

        $this->capabilities = new Capabilities(
            Capabilities::CAPABILITY_BROWSABLE
            | Capabilities::CAPABILITY_PUBLIC
        );

        $this->assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
    }

    public function __destruct()
    {
        foreach ($this->temporaryPaths as $temporaryPath) {
            unlink($temporaryPath);
        }
    }

    #[\Override]
    public function mergeConfigurationCapabilities(Capabilities $capabilities): Capabilities
    {
        $this->capabilities->and($capabilities);

        return $this->capabilities;
    }

    #[\Override]
    public function processConfiguration(): void {}

    #[\Override]
    public function sanitizeFileName(string $fileName, string $charset = ''): string
    {
        $fileName = ltrim($fileName, '.');
        $cleanFileName = (string)preg_replace('/[\\x00-\\x1F\\x7F\\/\\\\:*?"<>|]/', '_', trim($fileName));

        return $cleanFileName === '' ? '_' : $cleanFileName;
    }

    #[\Override]
    public function initialize(): void
    {
        // @extensionScannerIgnoreLine
        $this->baseUrl = GeneralUtility::makeInstance(YamlConfiguration::class)->getBaseUrl();
    }

    #[\Override]
    public function getPublicUrl(string $identifier): string
    {
        if (!isset($this->publicUrls[$identifier])) {
            $uriParts = GeneralUtility::trimExplode('/', ltrim($identifier, '/'), true);
            $uriParts = array_map(rawurlencode(...), $uriParts);
            // @extensionScannerIgnoreLine
            $this->publicUrls[$identifier] = $this->baseUrl . '/' . implode('/', $uriParts);
        }

        return $this->publicUrls[$identifier];
    }

    #[\Override]
    public function hash(string $fileIdentifier, string $hashAlgorithm): string
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
    public function getFileInfoByIdentifier(string $fileIdentifier, array $propertiesToExtract = []): array
    {
        $fileInfo = $this->getAssetData($fileIdentifier);

        if ($fileInfo === []) {
            throw new FileDoesNotExistException('File does not exist', 1555571365);
        }

        if ($propertiesToExtract !== []) {
            $fileInfo = array_intersect_key($fileInfo, array_flip($propertiesToExtract));
        }

        return $fileInfo;
    }

    #[\Override]
    public function fileExists(string $identifier): bool
    {
        if (str_ends_with($identifier, '/')) {
            return false;
        }

        $this->normalizeIdentifier($identifier);

        return $this->objectExists($identifier);
    }

    #[\Override]
    public function folderExists(string $identifier): bool
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
    public function fileExistsInFolder(string $fileName, string $folderIdentifier): bool
    {
        return $this->objectExists($folderIdentifier . $fileName);
    }

    #[\Override]
    public function folderExistsInFolder(string $folderName, string $folderIdentifier): bool
    {
        return $this->objectExists($folderIdentifier . $folderName . '/');
    }

    #[\Override]
    public function getFolderInFolder(string $folderName, string $folderIdentifier): string
    {
        $identifier = $folderIdentifier . '/' . $folderName . '/';
        $this->normalizeIdentifier($identifier);

        return $identifier;
    }

    #[\Override]
    public function addFile(string $localFilePath, string $targetFolderIdentifier, string $newFileName = '', bool $removeOriginal = true): string
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

        if ($removeOriginal) {
            unlink($targetPath);
        }

        return $targetIdentifier;
    }

    #[\Override]
    public function moveFileWithinStorage(string $fileIdentifier, string $targetFolderIdentifier, string $newFileName): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $fileIdentifier;
    }

    #[\Override]
    public function copyFileWithinStorage(string $fileIdentifier, string $targetFolderIdentifier, string $fileName): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $fileIdentifier;
    }

    #[\Override]
    public function replaceFile(string $fileIdentifier, string $localFilePath): bool
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return true;
    }

    #[\Override]
    public function deleteFile(string $fileIdentifier): bool
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $this->removeFileByIdentifier($fileIdentifier);
    }

    #[\Override]
    public function deleteFolder(string $folderIdentifier, bool $deleteRecursively = false): bool
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return true;
    }

    #[\Override]
    public function getFileForLocalProcessing(string $fileIdentifier, bool $writable = true): string
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
    public function createFile(string $fileName, string $parentFolderIdentifier): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $parentFolderIdentifier . $fileName;
    }

    #[\Override]
    public function createFolder(string $newFolderName, string $parentFolderIdentifier = '', bool $recursive = false): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $parentFolderIdentifier . $newFolderName . '/';
    }

    #[\Override]
    public function getFileContents(string $fileIdentifier): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return '';
    }

    #[\Override]
    public function setFileContents(string $fileIdentifier, string $contents): int
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return 0;
    }

    #[\Override]
    public function renameFile(string $fileIdentifier, string $newName): string
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return $newName;
    }

    #[\Override]
    public function renameFolder(string $folderIdentifier, string $newName): array
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return [];
    }

    #[\Override]
    public function moveFolderWithinStorage(string $sourceFolderIdentifier, string $targetFolderIdentifier, string $newFolderName): array
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return [];
    }

    #[\Override]
    public function copyFolderWithinStorage(string $sourceFolderIdentifier, string $targetFolderIdentifier, string $newFolderName): bool
    {
        // Dummy implementation: The driver is supposed to be read-only.
        return true;
    }

    #[\Override]
    public function isFolderEmpty(string $folderIdentifier): bool
    {
        return $this->countFilesInFolder($folderIdentifier) > 0;
    }

    /**
     * @throws InvalidPathException
     * @see LocalDriver
     */
    #[\Override]
    public function isWithin(string $folderIdentifier, string $identifier): bool
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
    public function getFolderInfoByIdentifier(string $folderIdentifier): array
    {
        $this->normalizeIdentifier($folderIdentifier);

        return [
            'identifier' => $folderIdentifier ?: '/',
            'name' => basename(rtrim($folderIdentifier, '/')),
            'mtime' => 0,
            'ctime' => 0,
            'storage' => $this->storageUid ?? 0,
        ];
    }

    #[\Override]
    public function getFileInFolder(string $fileName, string $folderIdentifier): string
    {
        $folderIdentifier = $folderIdentifier . '/' . $fileName;
        $this->normalizeIdentifier($folderIdentifier);

        return $folderIdentifier;
    }

    #[\Override]
    public function getFilesInFolder(string $folderIdentifier, int $start = 0, int $numberOfItems = 0, bool $recursive = false, array $filenameFilterCallbacks = [], string $sort = '', bool $sortRev = false): array
    {
        if (($sort !== '' && $sort !== 'file') || $sortRev || $this->assetsLoaded === false) {
            $order = $this->getOrder($sort);
            $orderByDir = $sortRev ? 'DESC' : 'ASC';
            $this->rebuildAssetCache($this->getAssetRepository()->list('', $start, $numberOfItems, $order, $orderByDir));
            $this->assetsLoaded = true;
        }

        if ($this->cleanUp) {
            $this->removeObsoleteFiles();
            $this->cleanUp = false;
        }

        return array_keys($this->assets);
    }

    #[\Override]
    public function countFilesInFolder(string $folderIdentifier, bool $recursive = false, array $filenameFilterCallbacks = []): int
    {
        return count($this->getFilesInFolder($folderIdentifier, 0, 0, false, $filenameFilterCallbacks));
    }

    #[\Override]
    public function getFoldersInFolder(string $folderIdentifier, int $start = 0, int $numberOfItems = 0, bool $recursive = false, array $folderNameFilterCallbacks = [], string $sort = '', bool $sortRev = false): array
    {
        return [];
    }

    #[\Override]
    public function countFoldersInFolder(string $folderIdentifier, bool $recursive = false, array $folderNameFilterCallbacks = []): int
    {
        return count($this->getFoldersInFolder($folderIdentifier, 0, 0, $recursive, $folderNameFilterCallbacks));
    }

    #[\Override]
    public function dumpFileContents(string $identifier): void
    {
        $this->logger->debug('dumpFileContents');
    }

    #[\Override]
    public function getPermissions(string $identifier): array
    {
        $read = true;
        $write = false;

        if (($this->objectExists($identifier) && $identifier) || $identifier === self::ROOT_LEVEL_FOLDER) {
            // Dummy implementation: The driver is supposed to be read-only.
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

        $item['downloadUrl'] = $asset['downloadUrl'] ?? '';

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
        $file = $fileRepository->findByUid($uid);
        $file->getStorage()->deleteFile($file);
    }

    protected function getAssetRepository(): AssetRepository
    {
        return $this->assetRepository;
    }

    protected function processFile(string $fileIdentifier, array $asset): string
    {
        $temporaryPath = $this->getTemporaryPathForFile($fileIdentifier . '.' . $asset['extension']);
        try {
            $content = (string)GeneralUtility::makeInstance(MauticHttpClient::class)->request($asset['downloadUrl'])->getBody();
        } catch (\Exception $exception) {
            if (!$exception instanceof ConnectException) {
                $this->logger->warning('Downloading Mautic asset {identifier} failed: {message}', [
                    'identifier' => $fileIdentifier,
                    'message' => $exception->getMessage(),
                ]);
            }
            throw new \RuntimeException('Downloading file ' . $fileIdentifier . ' failed.', 1555571766, $exception);
        }
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
