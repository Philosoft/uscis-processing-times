<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221104162827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds table for I485 entries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE i485_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            <<<'SQL'
            CREATE TABLE i485_entry (
                id INT NOT NULL,
                processing_center VARCHAR(255) NOT NULL,
                raw_response TEXT NOT NULL,
                wait_time DOUBLE PRECISION NOT NULL,
                created_at DATE NOT NULL,
                publication_date DATE NOT NULL,
                service_request_date DATE NOT NULL,

                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql('CREATE INDEX IDX_10DC4608B8E8428 ON i485_entry (created_at)');
        $this->addSql('COMMENT ON COLUMN i485_entry.created_at IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN i485_entry.publication_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN i485_entry.service_request_date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE i485_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE i485_entry');
    }
}
