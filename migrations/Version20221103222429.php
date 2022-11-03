<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221103222429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds basic I140Entry table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE i140_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<'SQL'
            CREATE TABLE i140_entry (
                id INT NOT NULL,
                processing_center VARCHAR(255) NOT NULL,
                raw_response TEXT NOT NULL,
                wait_time DOUBLE PRECISION NOT NULL,
                created_at DATE NOT NULL,

                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql('COMMENT ON COLUMN i140_entry.created_at IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE i140_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE i140_entry');
    }
}
