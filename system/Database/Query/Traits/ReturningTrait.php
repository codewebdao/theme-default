<?php
namespace System\Database\Query\Traits;

/** RETURNING clause support (grammar-aware). */
trait ReturningTrait
{
    /** @var array<int,string>|null */
    protected $returning = null;

    /**
     * Request returning columns after DML.
     * @param array<int,string> $cols
     * @return $this
     */
    public function returning(array $cols)
    {
        if (!$this->grammar->supportsReturning()) {
            // No-op for grammars without RETURNING
            return $this;
        }
        $this->returning = $cols;
        return $this;
    }
}
