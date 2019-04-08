<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Bitmotion\Mautic\Transformation\TransformationInterface;
use Psr\Log\LoggerAwareInterface;

interface FormFieldTransformationInterface extends TransformationInterface, LoggerAwareInterface
{
    public function getFieldData(): array;
}
