<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use SolidInvoice\CoreBundle\Form\Type\BillingIdConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Add job settings to app_config table
 */
final class Version20250629050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add job settings for ID generation and auto-creation on quote acceptance';
    }

    public function up(Schema $schema): void
    {
        // Add job ID generation settings
        $this->addSql("
            INSERT INTO app_config (setting_key, setting_value, description, field_type, company_id) 
            SELECT 'job/id_generation/strategy', 'auto_increment', '', ?, company_id 
            FROM app_config 
            WHERE setting_key = 'system/company/company_name' 
            AND NOT EXISTS (
                SELECT 1 FROM app_config ac2 
                WHERE ac2.setting_key = 'job/id_generation/strategy' 
                AND ac2.company_id = app_config.company_id
            )
        ", [BillingIdConfigurationType::class]);

        $this->addSql("
            INSERT INTO app_config (setting_key, setting_value, description, field_type, company_id) 
            SELECT 'job/id_generation/id_prefix', 'JOB-', 'Example: JOB-', ?, company_id 
            FROM app_config 
            WHERE setting_key = 'system/company/company_name' 
            AND NOT EXISTS (
                SELECT 1 FROM app_config ac2 
                WHERE ac2.setting_key = 'job/id_generation/id_prefix' 
                AND ac2.company_id = app_config.company_id
            )
        ", [TextType::class]);

        $this->addSql("
            INSERT INTO app_config (setting_key, setting_value, description, field_type, company_id) 
            SELECT 'job/id_generation/id_suffix', '', 'Example: -JOB', ?, company_id 
            FROM app_config 
            WHERE setting_key = 'system/company/company_name' 
            AND NOT EXISTS (
                SELECT 1 FROM app_config ac2 
                WHERE ac2.setting_key = 'job/id_generation/id_suffix' 
                AND ac2.company_id = app_config.company_id
            )
        ", [TextType::class]);

        $this->addSql("
            INSERT INTO app_config (setting_key, setting_value, description, field_type, company_id) 
            SELECT 'job/auto_create_on_quote_acceptance', '1', 'Automatically create a job when a quote is accepted', ?, company_id 
            FROM app_config 
            WHERE setting_key = 'system/company/company_name' 
            AND NOT EXISTS (
                SELECT 1 FROM app_config ac2 
                WHERE ac2.setting_key = 'job/auto_create_on_quote_acceptance' 
                AND ac2.company_id = app_config.company_id
            )
        ", [CheckboxType::class]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM app_config WHERE setting_key IN ('job/id_generation/strategy', 'job/id_generation/id_prefix', 'job/id_generation/id_suffix', 'job/auto_create_on_quote_acceptance')");
    }
}