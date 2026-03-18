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

namespace Leuchtfeuer\Mautic\Domain\Model\Dto;

/**
 * @deprecated Use YamlConfiguration instead.
 */
class EmConfiguration extends YamlConfiguration
{
    public function __construct()
    {
        parent::__construct();

        trigger_error('Use YamlConfiguration instead.', E_USER_DEPRECATED);
    }
}
