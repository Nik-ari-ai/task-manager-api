<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create status and task tables with a restricting foreign key from task to status.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE status (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                title VARCHAR(255) NOT NULL,
                is_system BOOLEAN DEFAULT false NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_status_name ON status (name)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE task (
                id SERIAL NOT NULL,
                status_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_task_status_id ON task (status_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE task
                ADD CONSTRAINT fk_task_status
                FOREIGN KEY (status_id)
                REFERENCES status (id)
                ON DELETE RESTRICT
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP CONSTRAINT fk_task_status');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE status');
    }
}
