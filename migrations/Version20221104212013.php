<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221104212013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds I485Entry.officeCode';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE i485_entry ADD office_code VARCHAR(255) NOT NULL DEFAULT ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE i485_entry DROP office_code');
    }
}
