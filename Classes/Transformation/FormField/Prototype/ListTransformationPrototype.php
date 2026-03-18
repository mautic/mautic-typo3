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

namespace Leuchtfeuer\Mautic\Transformation\FormField\Prototype;

use Leuchtfeuer\Mautic\Exception\TransformationException;
use Leuchtfeuer\Mautic\Transformation\FormField\AbstractFormFieldTransformation;

class ListTransformationPrototype extends AbstractFormFieldTransformation
{
    protected string $listIdentifier = 'list';

    protected int $multiple = 0;

    protected int $syncList = 0;

    protected array $customFieldProperties = [];

    protected array $customFieldValues = [];

    protected bool $updateCustomFieldsProperties = true;

    /**
     * @throws TransformationException
     */
    #[\Override]
    public function transform(): void
    {
        parent::transform();

        $properties = [];

        if (isset($this->fieldData['leadField'])) {
            $this->syncList = 1;
            $properties['syncList'] = $this->syncList;
        }

        if ($this->updateCustomFieldsProperties || $this->syncList === 0) {
            $properties[$this->listIdentifier] = [
                'list' => [],
            ];

            if ($this->multiple === 1) {
                $properties['multiple'] = 1;
            }

            if (isset($this->fieldDefinition['properties']['options'])) {
                foreach ((array)$this->fieldDefinition['properties']['options'] as $value => $label) {
                    $properties[$this->listIdentifier]['list'][] = [
                        'value' => $value,
                        'label' => $label,
                    ];
                }
            } else {
                $properties[$this->listIdentifier]['list'][] = [
                    'value' => '1',
                    'label' => $this->fieldDefinition['label'],
                ];
            }
        }

        $this->customFieldValues = $properties[$this->listIdentifier]['list'];

        // Remove form values if they should not be synced.
        if ($this->syncList === 1) {
            unset($properties[$this->listIdentifier]);
        }

        $this->fieldData['properties'] = $properties;
    }

    public function hasCustomFieldValues(): bool
    {
        return !empty($this->customFieldValues);
    }

    public function getCustomFieldValues(): array
    {
        return [$this->fieldDefinition['identifier'] => $this->customFieldValues];
    }
}
