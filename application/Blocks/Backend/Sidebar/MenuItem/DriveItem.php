<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * DriveItem - Drive item cho menu (đường phân cách)
 */
class DriveItem extends BaseMenuItem
{
    protected string $type = 'drive';
    protected int $width;
    protected int $margin;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->width = is_numeric($data['width'] ?? null) ? (int)$data['width'] : 1;
        $this->margin = is_numeric($data['margin'] ?? null) ? (int)$data['margin'] : 8;
    }

    public function validate(): bool
    {
        return $this->width > 0 && $this->margin >= 0;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getMargin(): int
    {
        return $this->margin;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function setMargin(int $margin): self
    {
        $this->margin = $margin;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'width' => $this->width,
            'margin' => $this->margin
        ]);
    }
}
