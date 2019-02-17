<?php

namespace Authters\ServiceBus\Support\Transaction;

use Illuminate\Database\Connection;

class TransactionManager
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function begin(): void
    {
        $this->connection->beginTransaction();
    }

    public function rollback(int $toLevel = null): void
    {
        $this->connection->rollBack($toLevel);
    }

    public function commit(): void
    {
        $this->connection->commit();
    }
}