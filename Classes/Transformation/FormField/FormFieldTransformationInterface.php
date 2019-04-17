<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\FormField;

use Psr\Log\LoggerAwareInterface;

interface FormFieldTransformationInterface extends LoggerAwareInterface
{
    public function getFieldData(): array;
}
