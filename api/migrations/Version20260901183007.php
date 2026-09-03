<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901183007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE EXTENSION IF NOT EXISTS postgis');
        $this->addSql('CREATE TABLE establishment (id UUID NOT NULL, google_place_id TEXT DEFAULT NULL, name TEXT NOT NULL, address TEXT DEFAULT NULL, phone_number TEXT DEFAULT NULL, website TEXT DEFAULT NULL, location geometry(GEOMETRY, 0) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DBEFB1EE983C031 ON establishment (google_place_id)');
        $this->addSql('CREATE TABLE evaluation (id UUID NOT NULL, comment TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN NOT NULL, establishment_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1323A5758565851 ON evaluation (establishment_id)');
        $this->addSql('CREATE TABLE evaluation_rating (id UUID NOT NULL, criterion VARCHAR(255) NOT NULL, rating SMALLINT NOT NULL, evaluation_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_12F613D5456C5646 ON evaluation_rating (evaluation_id)');
        $this->addSql('CREATE TABLE evaluation_rating_image (evaluation_rating_id UUID NOT NULL, image_id UUID NOT NULL, PRIMARY KEY (evaluation_rating_id, image_id))');
        $this->addSql('CREATE INDEX IDX_9619B668D8322442 ON evaluation_rating_image (evaluation_rating_id)');
        $this->addSql('CREATE INDEX IDX_9619B6683DA5256D ON evaluation_rating_image (image_id)');
        $this->addSql('CREATE TABLE evaluation_vote (id UUID NOT NULL, value SMALLINT NOT NULL, evaluation_rating_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1756CADCD8322442 ON evaluation_vote (evaluation_rating_id)');
        $this->addSql('CREATE INDEX IDX_1756CADCA76ED395 ON evaluation_vote (user_id)');
        $this->addSql('CREATE TABLE file (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE image (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5758565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation_rating ADD CONSTRAINT FK_12F613D5456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation_rating_image ADD CONSTRAINT FK_9619B668D8322442 FOREIGN KEY (evaluation_rating_id) REFERENCES evaluation_rating (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation_rating_image ADD CONSTRAINT FK_9619B6683DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation_vote ADD CONSTRAINT FK_1756CADCD8322442 FOREIGN KEY (evaluation_rating_id) REFERENCES evaluation_rating (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation_vote ADD CONSTRAINT FK_1756CADCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('ALTER TABLE evaluation DROP CONSTRAINT FK_1323A5758565851');
        $this->addSql('ALTER TABLE evaluation_rating DROP CONSTRAINT FK_12F613D5456C5646');
        $this->addSql('ALTER TABLE evaluation_rating_image DROP CONSTRAINT FK_9619B668D8322442');
        $this->addSql('ALTER TABLE evaluation_rating_image DROP CONSTRAINT FK_9619B6683DA5256D');
        $this->addSql('ALTER TABLE evaluation_vote DROP CONSTRAINT FK_1756CADCD8322442');
        $this->addSql('ALTER TABLE evaluation_vote DROP CONSTRAINT FK_1756CADCA76ED395');
        $this->addSql('DROP TABLE establishment');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE evaluation_rating');
        $this->addSql('DROP TABLE evaluation_rating_image');
        $this->addSql('DROP TABLE evaluation_vote');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE "user"');
    }
}
