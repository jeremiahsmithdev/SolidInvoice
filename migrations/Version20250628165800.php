<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add phone field to clients table
 */
final class Version20250628165800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phone field to clients table for merged contact information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients ADD COLUMN phone VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clients DROP COLUMN phone');
    }
}