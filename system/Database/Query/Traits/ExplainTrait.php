<?php
namespace System\Database\Query\Traits;

/** EXPLAIN helpers (grammar-aware). */
trait ExplainTrait
{
    /**
     * Explain current SELECT query.
     * @param array $options Grammar-specific options
     * @return array<int,array<string,mixed>> Raw explain rows
     */
    public function explain(array $options = array())
    {
        list($sql, $bindings) = $this->compileSelect();
        list($explainSql, $explainBindings) = $this->grammar->compileExplain($sql, $bindings, $options);
        $rows = $this->driver->query($explainSql, $explainBindings);
        return \is_array($rows) ? $rows : array();
    }
}
