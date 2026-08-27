<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821185823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation_vote (id UUID NOT NULL, value SMALLINT NOT NULL, evaluation_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1756CADC456C5646 ON evaluation_vote (evaluation_id)');
        $this->addSql('CREATE INDEX IDX_1756CADCA76ED395 ON evaluation_vote (user_id)');
        $this->addSql('ALTER TABLE evaluation_vote ADD CONSTRAINT FK_1756CADC456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE evaluation_vote ADD CONSTRAINT FK_1756CADCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_vote DROP CONSTRAINT FK_1756CADC456C5646');
        $this->addSql('ALTER TABLE evaluation_vote DROP CONSTRAINT FK_1756CADCA76ED395');
        $this->addSql('DROP TABLE evaluation_vote');
    }
}
