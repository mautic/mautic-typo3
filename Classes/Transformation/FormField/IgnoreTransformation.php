<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

class IgnoreTransformation extends AbstractFormFieldTransformation
{
    public function transform()
    {
        $this->logger->notice(sprintf(
            'Skip transformation of "%s". No information synced with Mautic.',
            $this->fieldDefinition['identifier']
        ));
    }
}
