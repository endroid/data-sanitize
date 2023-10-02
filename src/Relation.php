<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\DBAL\Connection;

final class Relation
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
        private readonly string $columnName
    ) {
    }

    /** @param array<string> $sourceIds */
    public function merge(array $sourceIds, string $targetId): void
    {
        $targetIdValue = '' === $targetId ? 'NULL' : $targetId;

        foreach ($sourceIds as $sourceId) {
            // Update existing values with target value
            // In case of duplicate or invalid NULL ignore
            $this->connection->executeStatement('
                UPDATE '.$this->tableName.'
                SET '.$this->columnName.' = '.$targetIdValue.'
                WHERE '.$this->columnName.' = :sourceId
            ', ['sourceId' => $sourceId]);

            // Make sure failed updates because of duplicate or invalid NULL are deleted
            // This is the only way to ensure that the source entities can be removed
            $this->connection->executeStatement('
                DELETE FROM '.$this->tableName.'
                WHERE '.$this->columnName.' = :sourceId
            ', ['sourceId' => $sourceId]);
        }
    }
}
