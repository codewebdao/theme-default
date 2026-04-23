<?php
namespace System\Database\Query\Traits;

/** GROUP BY & DISTINCT helpers. */
trait GroupTrait
{
    /** @return $this */
    public function groupBy(...$cols)
    {
        foreach ($cols as $c) $this->groups[] = $c;
        return $this;
    }

    /**
     * GROUP BY raw expression.
     * @param string $expression
     * @return $this
     */
    public function groupByRaw($expression)
    {
        $this->groups[] = $expression;
        return $this;
    }

    /** Distinct rows (simple). @return $this */
    public function distinct($on = true)
    {
        $this->distinct = (bool)$on;
        return $this;
    }
}
