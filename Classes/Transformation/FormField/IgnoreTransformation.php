<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\FormField;

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
