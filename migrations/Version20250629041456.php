<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Uid\Ulid;

/**
 * Add setting to enable/disable recurring invoices feature
 */
final class Version20250629041456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring invoices enable/disable setting';
    }

    public function up(Schema $schema): void
    {
        // For now, we'll handle this setting creation through the settings system
        // to ensure proper company association
        $this->addSql('SELECT 1'); // no-op
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM app_config WHERE setting_key = 'invoice/recurring_invoices_enabled'");
    }
}
