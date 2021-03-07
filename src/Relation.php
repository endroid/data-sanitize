<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize;

use Doctrine\DBAL\Connection;

final class Relation
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $tableName;

    /** @var string */
    private $columnName;

    public function __construct(Connection $connection, string $tableName, string $columnName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
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
