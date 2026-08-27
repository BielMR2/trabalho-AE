<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818181424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bookmark DROP CONSTRAINT fk_da62921d16a2b381');
        $this->addSql('ALTER TABLE bookmark DROP CONSTRAINT fk_da62921da76ed395');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c616a2b381');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c6a76ed395');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE bookmark');
        $this->addSql('DROP TABLE parchment');
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE establishment ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE establishment ADD phone_number TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE establishment ADD website TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE TABLE book (id UUID NOT NULL, book VARCHAR(255) NOT NULL, title TEXT NOT NULL, author VARCHAR(255) DEFAULT NULL, condition VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_cbe5a331cbe5a331 ON book (book)');
        $this->addSql('COMMENT ON COLUMN book.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bookmark (id UUID NOT NULL, user_id UUID NOT NULL, book_id UUID NOT NULL, bookmarked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_da62921da76ed395 ON bookmark (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_da62921da76ed39516a2b381 ON bookmark (user_id, book_id)');
        $this->addSql('CREATE INDEX idx_da62921d16a2b381 ON bookmark (book_id)');
        $this->addSql('COMMENT ON COLUMN bookmark.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.book_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.bookmarked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE parchment (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('COMMENT ON COLUMN parchment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE review (id UUID NOT NULL, user_id UUID NOT NULL, book_id UUID NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, body TEXT NOT NULL, rating SMALLINT NOT NULL, letter VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_794381c6a76ed39516a2b381 ON review (user_id, book_id)');
        $this->addSql('CREATE INDEX idx_794381c616a2b381 ON review (book_id)');
        $this->addSql('CREATE INDEX idx_794381c6a76ed395 ON review (user_id)');
        $this->addSql('COMMENT ON COLUMN review.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.book_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bookmark ADD CONSTRAINT fk_da62921d16a2b381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookmark ADD CONSTRAINT fk_da62921da76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c616a2b381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c6a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE establishment DROP address');
        $this->addSql('ALTER TABLE establishment DROP phone_number');
        $this->addSql('ALTER TABLE establishment DROP website');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
    }
}
