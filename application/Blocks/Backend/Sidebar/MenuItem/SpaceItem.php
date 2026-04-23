<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * SpaceItem - Space item cho menu (khoảng trống)
 */
class SpaceItem extends BaseMenuItem
{
    protected string $type = 'space';
    protected int $space;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->space = is_numeric($data['space'] ?? null) ? (int)$data['space'] : 20;
    }

    public function validate(): bool
    {
        return $this->space > 0;
    }

    public function getSpace(): int
    {
        return $this->space;
    }

    public function setSpace(int $space): self
    {
        $this->space = $space;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'space' => $this->space
        ]);
    }
}
