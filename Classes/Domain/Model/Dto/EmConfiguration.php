<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Model\Dto;

/**
 * @deprecated Use YamlConfiguration instead.
 */
class EmConfiguration extends YamlConfiguration
{
    public function __construct()
    {
        parent::__construct();

        trigger_error("Use YamlConfiguration instead.", E_USER_DEPRECATED);
    }
}
