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

namespace Leuchtfeuer\Mautic\Transformation\FormField;

class IgnoreTransformation extends AbstractFormFieldTransformation
{
    #[\Override]
    public function transform(): void
    {
        $this->logger->notice(sprintf(
            'Skip transformation of "%s". No information synced with Mautic.',
            $this->fieldDefinition['identifier']
        ));
    }
}
