<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractTransformation implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    abstract public function transform();
}
