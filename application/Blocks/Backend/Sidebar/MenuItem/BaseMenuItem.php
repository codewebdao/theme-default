<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * BaseMenuItem - Abstract class cho tất cả menu items
 */
abstract class BaseMenuItem
{
    protected string $type;
    protected int $order;
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->order = (int)($data['order'] ?? 9999);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge($this->data, [
            'type' => $this->type,
            'order' => $this->order
        ]);
    }

    abstract public function validate(): bool;
}
