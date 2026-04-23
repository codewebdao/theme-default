<?php

namespace App\Blocks\Backend\Sidebar\MenuItem;

/**
 * MenuItem - Menu item chính với href, icon, children
 */
class MenuItem extends BaseMenuItem
{
    protected string $type = 'menu';
    protected string $id;
    protected string $label;
    protected string $href;
    protected ?string $icon;
    protected array $children = [];
    protected bool $expanded = false;
    protected bool $permissionCheck = true;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        
        $this->id = $data['id'] ?? '';
        $this->label = $data['label'] ?? '';
        $this->href = $data['href'] ?? '#';
        $this->icon = $data['icon'] ?? null;
        $this->children = is_array($data['children'] ?? null) ? $data['children'] : [];
        $this->expanded = (bool)($data['expanded'] ?? false);
        $this->permissionCheck = (bool)($data['permission_check'] ?? true);
    }

    public function validate(): bool
    {
        return !empty($this->id) && !empty($this->label);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    public function setExpanded(bool $expanded): self
    {
        $this->expanded = $expanded;
        return $this;
    }

    public function needsPermissionCheck(): bool
    {
        return $this->permissionCheck;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'id' => $this->id,
            'label' => $this->label,
            'href' => $this->href,
            'icon' => $this->icon,
            'children' => $this->children,
            'expanded' => $this->expanded,
            'permission_check' => $this->permissionCheck
        ]);
    }
}
