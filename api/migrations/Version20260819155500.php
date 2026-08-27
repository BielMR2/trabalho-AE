<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819155500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_rating DROP CONSTRAINT fk_12f613d597766307');
        $this->addSql('DROP TABLE evaluation_criterion');
        $this->addSql('DROP INDEX idx_12f613d597766307');
        $this->addSql('ALTER TABLE evaluation_rating ADD criterion VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE evaluation_rating DROP criterion_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE TABLE evaluation_criterion (id UUID NOT NULL, name TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE evaluation_rating ADD criterion_id UUID NOT NULL');
        $this->addSql('ALTER TABLE evaluation_rating DROP criterion');
        $this->addSql('ALTER TABLE evaluation_rating ADD CONSTRAINT fk_12f613d597766307 FOREIGN KEY (criterion_id) REFERENCES evaluation_criterion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_12f613d597766307 ON evaluation_rating (criterion_id)');
    }
}
