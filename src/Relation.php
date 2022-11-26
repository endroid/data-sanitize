<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\DBAL\Connection;

final class Relation
{
    public function __construct(
        private Connection $connection,
        private string $tableName,
        private string $columnName
    ) {
    }

    /** @param array<string> $sourceIds */
    public function merge(array $sourceIds, string $targetId): void
    {
        $targetIdValue = '' === $targetId ? 'NULL' : $targetId;

        // Update existing values with target value
        // In case of duplicate or invalid NULL ignore
        $query = '
            UPDATE IGNORE '.$this->tableName.'
            SET '.$this->columnName.' = '.$targetIdValue.'
            WHERE '.$this->columnName.' IN ("'.implode('","', $sourceIds).'");';

        $this->connection->executeUpdate($query);

        // Make sure failed updates because of duplicate or invalid NULL are deleted
        // This is the only way to ensure that the source entities can be removed
        $query = '
            DELETE FROM '.$this->tableName.'
            WHERE '.$this->columnName.' IN ("'.implode('","', $sourceIds).'");';

        $this->connection->executeUpdate($query);
    }
}
