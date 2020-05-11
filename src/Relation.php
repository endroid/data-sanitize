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

    public function merge(array $sourceIds, string $targetId): void
    {
        $query = '
            UPDATE IGNORE '.$this->tableName.'
            SET '.$this->columnName.' = "'.$targetId.'"
            WHERE '.$this->columnName.' IN ("'.implode('","', $sourceIds).'");';

        $this->connection->executeUpdate($query);

        $query = '
            DELETE FROM '.$this->tableName.'
            WHERE '.$this->columnName.' IN ("'.implode('","', $sourceIds).'");';

        $this->connection->executeUpdate($query);
    }
}
