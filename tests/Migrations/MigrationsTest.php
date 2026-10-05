<?php

namespace App\Tests\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MigrationsTest extends KernelTestCase
{
    public function testMigrationsRunOnACleanDatabaseAndCreateExpectedSchema(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $this->dropEverything($entityManager);

        $exitCode = $application->run(
            new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]),
            new BufferedOutput()
        );

        self::assertSame(0, $exitCode, 'The migrations must run without errors on a clean database.');

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $statusCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM status');
        self::assertSame(3, $statusCount, 'The default statuses must be inserted by the data migration.');

        $defaultNames = $connection->fetchFirstColumn('SELECT name FROM status ORDER BY name');
        self::assertSame(['done', 'in_progress', 'new'], $defaultNames);

        $systemCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM status WHERE is_system = true');
        self::assertSame(3, $systemCount, 'The default statuses must be marked as system statuses.');

        $taskCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM task');
        self::assertSame(0, $taskCount, 'The task table must exist and be empty after migrations.');
    }

    private function dropEverything(EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();

        $connection->executeStatement('DROP TABLE IF EXISTS task');
        $connection->executeStatement('DROP TABLE IF EXISTS status');
        $connection->executeStatement('DROP TABLE IF EXISTS doctrine_migration_versions');
    }
}
