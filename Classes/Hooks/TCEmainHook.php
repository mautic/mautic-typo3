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

namespace Leuchtfeuer\Mautic\Hooks;

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\TagRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TCEmainHook
{
    /**
     * Create tags in Mautic and synchronize them with TYPO3 to receive proper IDs
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string $id, array $fields, DataHandler &$dataHandler): void
    {
        if ($status === 'new' && $table === 'tx_mautic_domain_model_tag' && !empty($fields['title'])) {
            // Dirty way to create tags in Mautic
            $config = GeneralUtility::makeInstance(YamlConfiguration::class);
            // @extensionScannerIgnoreLine
            $url = sprintf('%s/mtracking.gif?tags=%s', $config->getBaseUrl(), $fields['title']);
            GeneralUtility::getUrl($url);

            // Synchronize tags to receive proper ids
            $tagRepository = GeneralUtility::makeInstance(TagRepository::class);
            $tagRepository->synchronizeTags();

            // update record UID to display edit-form after syncing the new tag with Mautic to avoid error in case the
            // AUTO_INCREMENT value of table 'tx_mautic_domain_model_tag' is different from Mautic's 'lead_tags' table
            // (see issue https://github.com/mautic/mautic-typo3/issues/82)
            $newTag = $tagRepository->findTagByTitle($fields['title']);
            if (!empty($newTag) && $newTag['uid'] !== $dataHandler->substNEWwithIDs[$id]) {
                $dataHandler->substNEWwithIDs[$id] = $newTag['uid'];
            }
        }
    }
}
