<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractTransformation implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    abstract public function transform();
}
