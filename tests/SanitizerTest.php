<?php

declare(strict_types=1);

namespace Endroid\DataSanitize\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Endroid\DataSanitize\RelationFinder;
use Endroid\DataSanitize\Sanitizer;
use PHPUnit\Framework\TestCase;

final class SanitizerTest extends TestCase
{
    public function testMergeKeepsDistinctNumericStringSourceIds(): void
    {
        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new AttributeDriver([__DIR__]));
        if (method_exists($configuration, 'enableNativeLazyObjects')) {
            $configuration->enableNativeLazyObjects(true);
        } else {
            $configuration->setProxyDir(sys_get_temp_dir());
            $configuration->setProxyNamespace(__NAMESPACE__ . '\\Proxies');
        }
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $entityManager = new EntityManager($connection, $configuration);
        new SchemaTool($entityManager)->createSchema([$entityManager->getClassMetadata(MergeItem::class)]);
        $connection->insert('merge_items', ['id' => '1']);
        $connection->insert('merge_items', ['id' => '01']);

        $sanitizer = new Sanitizer(MergeItem::class, $entityManager, new RelationFinder($entityManager));
        $sanitizer->merge(['1', '01'], '01');

        self::assertSame(['01'], $connection->fetchFirstColumn('SELECT id FROM merge_items'));
        $entityManager->close();
    }
}

#[ORM\Entity]
#[ORM\Table(name: 'merge_items')]
class MergeItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    public string $id;
}
