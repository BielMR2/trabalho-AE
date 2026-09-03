<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Set SRID 4326 on existing establishment locations and create GiST spatial index.
 */
final class Version20260902190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set SRID 4326 on establishment locations and create GiST spatial index for viewport queries';
    }

    public function up(Schema $schema): void
    {
        // Update SRID on existing points to WGS84 (4326)
        $this->addSql('UPDATE establishment SET location = ST_SetSRID(location, 4326) WHERE ST_SRID(location) != 4326');

        // Create GiST spatial index for efficient bounding box queries
        $this->addSql('CREATE INDEX idx_establishment_location_gist ON establishment USING GIST(location)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_establishment_location_gist');
        $this->addSql('UPDATE establishment SET location = ST_SetSRID(location, 0) WHERE ST_SRID(location) = 4326');
    }
}

