<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert the default system statuses: new, in_progress and done.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO status (name, title, is_system) VALUES ('new', 'New', true)
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO status (name, title, is_system) VALUES ('in_progress', 'In progress', true)
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO status (name, title, is_system) VALUES ('done', 'Done', true)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE FROM status WHERE name IN ('new', 'in_progress', 'done')
        SQL);
    }
}
