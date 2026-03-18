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

use Psr\Log\LoggerAwareInterface;

interface FormFieldTransformationInterface extends LoggerAwareInterface
{
    public function getFieldData(): array;
}
