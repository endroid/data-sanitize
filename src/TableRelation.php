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

final class TableRelation extends AbstractRelation
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $tableName;

    /** @var string */
    private $columnName;

    /** @var string */
    private $inverseColumnName;

    public function __construct(Connection $connection, string $tableName, string $columnName, string $inverseColumnName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->inverseColumnName = $inverseColumnName;
    }

    public function merge(array $sources, string $target): void
    {
        $query = '
            UPDATE IGNORE '.$this->tableName.'
            SET '.$this->columnName.' = "'.$target.'"
            WHERE '.$this->columnName.' IN ("'.implode('","', $sources).'");
            
            DELETE FROM '.$this->tableName.'
            WHERE '.$this->columnName.' IN ("'.implode('","', $sources).'");';

        $this->connection->executeUpdate($query);
    }
}
