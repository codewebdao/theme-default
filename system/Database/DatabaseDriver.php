<?php
namespace System\Database;

/**
 * DatabaseDriver
 *
 * Minimal contract used by DB facade & Builder.
 * Concrete driver (PdoDriver in Part 2) handles routing, retries, and transactions.
 */
interface DatabaseDriver
{
    /**
     * Execute SQL with optional bindings.
     *
     * INPUT:
     *  - string $sql        SQL with placeholders
     *  - array  $params     Positional or named bindings
     * OUTPUT:
     *  - SELECT-like: array<array<string,mixed>>
     *  - DML: int (affected rows)
     */
    public function query($sql, array $params = array());

    /**
     * Get last inserted ID from the write connection.
     *
     * @return string
     */
    public function lastInsertId();

    /**
     * Begin a transaction on write connection.
     * @return bool
     */
    public function beginTransaction();

    /**
     * Commit current transaction.
     * @return bool
     */
    public function commit();

    /**
     * Rollback current transaction.
     * @return bool
     */
    public function rollBack();

    /**
     * Optional: Force-run callback on WRITE connection
     * (for read-after-write sensitive reads).
     * Input:
     *  - callable $fn
     * Output: mixed (return value of callback)
     */
    public function withForceWrite(callable $fn);
}
