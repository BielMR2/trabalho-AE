<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818194303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation_criterion (id UUID NOT NULL, name TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE evaluation_rating (id UUID NOT NULL, rating SMALLINT NOT NULL, evaluation_id UUID NOT NULL, criterion_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_12F613D5456C5646 ON evaluation_rating (evaluation_id)');
        $this->addSql('CREATE INDEX IDX_12F613D597766307 ON evaluation_rating (criterion_id)');
        $this->addSql('ALTER TABLE evaluation_rating ADD CONSTRAINT FK_12F613D5456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation_rating ADD CONSTRAINT FK_12F613D597766307 FOREIGN KEY (criterion_id) REFERENCES evaluation_criterion (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation DROP rating');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('ALTER TABLE evaluation_rating DROP CONSTRAINT FK_12F613D5456C5646');
        $this->addSql('ALTER TABLE evaluation_rating DROP CONSTRAINT FK_12F613D597766307');
        $this->addSql('DROP TABLE evaluation_criterion');
        $this->addSql('DROP TABLE evaluation_rating');
        $this->addSql('ALTER TABLE evaluation ADD rating SMALLINT NOT NULL');
    }
}
