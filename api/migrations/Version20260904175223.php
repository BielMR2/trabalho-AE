<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260904175223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_establishment_location_gist');
        $this->addSql('ALTER TABLE establishment ADD country_code VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation ADD country_code VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('ALTER TABLE establishment DROP country_code');
        $this->addSql('CREATE INDEX idx_establishment_location_gist ON establishment (location)');
        $this->addSql('ALTER TABLE evaluation DROP country_code');
    }
}
