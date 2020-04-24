<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Transformation\FormField;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Psr\Log\LoggerAwareInterface;

interface FormFieldTransformationInterface extends LoggerAwareInterface
{
    public function getFieldData(): array;
}
