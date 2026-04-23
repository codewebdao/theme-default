<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * HRItem - Horizontal rule separator
 */
class HRItem extends BaseMenuItem
{
    protected string $type = 'hr';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function validate(): bool
    {
        return true; // HR items luôn valid
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'order' => $this->order
        ];
    }
}
