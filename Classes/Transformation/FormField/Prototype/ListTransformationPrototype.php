<?php
declare(strict_types=1);
namespace Bitmotion\Mautic\Transformation\FormField\Prototype;

use Bitmotion\Mautic\Exception\TransformationException;
use Bitmotion\Mautic\Transformation\FormField\AbstractFormFieldTransformation;

class ListTransformationPrototype extends AbstractFormFieldTransformation
{
    /**
     * @var string
     */
    protected $listIdentifier = 'list';

    /**
     * @var int
     */
    protected $multiple = 0;

    /**
     * @var int
     */
    protected $syncList = 1;

    /**
     * @var array
     */
    protected $customFieldProperties = [];

    /**
     * @var array
     */
    protected $customFieldValues = [];

    /**
     * @var bool
     */
    protected $updateCustomFieldsProperties = true;

    /**
     * @throws TransformationException
     */
    public function transform()
    {
        parent::transform();

        $properties = [];
        $properties['syncList'] = $this->syncList;

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
