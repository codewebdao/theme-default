<?php
namespace System\Database\Query\Traits;

/** Row-level locking helpers (grammar-aware). */
trait LockingTrait
{
    /** @var string|null */
    protected $lockClause = null;

    /** @return $this */
    public function forUpdate()
    {
        if (!$this->grammar->supportsLocking()) {
            throw new \LogicException('FOR UPDATE not supported by this grammar.');
        }
        $this->lockClause = 'FOR UPDATE';
        return $this;
    }

    /** @return $this */
    public function forShare()
    {
        if (!$this->grammar->supportsLocking()) {
            throw new \LogicException('SHARE lock not supported by this grammar.');
        }
        $this->lockClause = $this->grammar->shareLockKeyword(); // e.g. FOR SHARE or LOCK IN SHARE MODE
        return $this;
    }

    /** @return $this */
    public function skipLocked()
    {
        if (!$this->grammar->supportsSkipLocked()) {
            throw new \LogicException('SKIP LOCKED not supported by this grammar.');
        }
        $this->lockClause = \trim(($this->lockClause ?: 'FOR UPDATE').' SKIP LOCKED');
        return $this;
    }
}
