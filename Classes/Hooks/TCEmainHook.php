<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Hooks;

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

use Bitmotion\Mautic\Domain\Model\Dto\YamlConfiguration;
use Bitmotion\Mautic\Domain\Repository\TagRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TCEmainHook
{
    /**
     * Create tags in Mautic and synchronize them with TYPO3 to receive proper IDs
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string $id, array $fields, DataHandler &$dataHandler)
    {
        if ($status === 'new' && $table === 'tx_mautic_domain_model_tag' && !empty($fields['title'])) {
            // Dirty way to create tags in Mautic
            $config = GeneralUtility::makeInstance(YamlConfiguration::class);
            $url = sprintf('%s/mtracking.gif?tags=%s', $config->getBaseUrl(), $fields['title']);
            GeneralUtility::getUrl($url);

            // Synchronize tags to receive proper ids
            $tagRepository = GeneralUtility::makeInstance(TagRepository::class);
            $tagRepository->synchronizeTags();
        }
    }
}
