<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814192919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE establishment (id UUID NOT NULL, google_place_id TEXT DEFAULT NULL, name TEXT NOT NULL, location geometry(GEOMETRY, 0) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DBEFB1EE983C031 ON establishment (google_place_id)');
        $this->addSql('CREATE TABLE evaluation (id UUID NOT NULL, active BOOLEAN NOT NULL, comment TEXT DEFAULT NULL, rating SMALLINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, establishment_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1323A5758565851 ON evaluation (establishment_id)');
        $this->addSql('CREATE TABLE file (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE image (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5758565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP CONSTRAINT FK_1323A5758565851');
        $this->addSql('DROP TABLE establishment');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE image');
    }
}
