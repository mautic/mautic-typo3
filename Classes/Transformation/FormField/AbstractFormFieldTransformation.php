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

use Leuchtfeuer\Mautic\Exception\TransformationException;
use Leuchtfeuer\Mautic\Transformation\AbstractTransformation;

abstract class AbstractFormFieldTransformation extends AbstractTransformation implements FormFieldTransformationInterface
{
    protected string $type = '';

    protected array $fieldData = [];

    public function __construct(protected array $fieldDefinition = []) {}

    public function getFieldDefinition(): array
    {
        return $this->fieldDefinition;
    }

    public function setFieldDefinition(array $fieldDefinition): void
    {
        $this->fieldDefinition = $fieldDefinition;
    }

    #[\Override]
    public function getFieldData(): array
    {
        return $this->fieldData;
    }

    /**
     * @throws TransformationException
     */
    #[\Override]
    public function transform(): void
    {
        if ($this->type === '') {
            throw new TransformationException(
                sprintf(
                    'No type given for field with identifier "%s".',
                    $this->fieldDefinition['identifier']
                ),
                1539014283
            );
        }

        $fieldData = [
            'label' => (empty($this->fieldDefinition['label']) ? $this->fieldDefinition['identifier'] : $this->fieldDefinition['label']),
            'alias' => str_replace('-', '_', $this->fieldDefinition['identifier']),
            'type' => $this->type,
        ];

        if (!empty($this->fieldDefinition['properties']['mauticId'])) {
            $fieldData['id'] = $this->fieldDefinition['properties']['mauticId'];
            // TODO: Add check whether Mautic field exists - remove mauticId and mauticAlias from properties
        }

        if (!empty($this->fieldDefinition['properties']['mauticTable'])) {
            $fieldData['leadField'] = $this->fieldDefinition['properties']['mauticTable'];
        }

        if (!empty($this->fieldDefinition['defaultValue'])) {
            $fieldData['defaultValue'] = $this->fieldDefinition['defaultValue'];
        }

        if (!empty($this->fieldDefinition['properties']['fluidAdditionalAttributes']['placeholder'])) {
            $fieldData['properties']['placeholder'] = $this->fieldDefinition['properties']['fluidAdditionalAttributes']['placeholder'];
        }

        if (!empty($this->fieldDefinition['properties']['elementDescription'])) {
            $fieldData['helpMessage'] = $this->fieldDefinition['properties']['elementDescription'];
        }

        if (isset($this->fieldDefinition['validators'])) {
            foreach ((array)$this->fieldDefinition['validators'] as $validator) {
                // TODO: Set required flag only for 'NotEmpty' validator?
                if ($validator['identifier'] === 'NotEmpty') {
                    $fieldData['isRequired'] = 1;
                }
            }
        } else {
            $fieldData['isRequired'] = 0;
        }

        $this->fieldData = $fieldData;
    }
}
