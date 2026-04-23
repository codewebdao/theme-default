<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * LabelItem - Label item cho menu
 */
class LabelItem extends BaseMenuItem
{
    protected string $type = 'label';
    protected string $label;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->label = $data['label'] ?? '';
    }

    public function validate(): bool
    {
        return !empty($this->label);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'label' => $this->label
        ]);
    }
}
