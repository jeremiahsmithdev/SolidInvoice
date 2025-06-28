<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627082242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simplify client structure: merge contact info into client, remove currency and VAT fields';
    }

    public function up(Schema $schema): void
    {
        // Step 1: Add new columns to existing table
        $this->addSql('ALTER TABLE clients ADD COLUMN firstName VARCHAR(125)');
        $this->addSql('ALTER TABLE clients ADD COLUMN lastName VARCHAR(125)');
        $this->addSql('ALTER TABLE clients ADD COLUMN email VARCHAR(255)');
        
        // Step 2: Migrate data from existing name field and primary contact
        $this->addSql(<<<'SQL'
            UPDATE clients SET 
                firstName = CASE 
                    WHEN INSTR(name, ' ') > 0 THEN SUBSTR(name, 1, INSTR(name, ' ') - 1)
                    ELSE name 
                END,
                lastName = CASE 
                    WHEN INSTR(name, ' ') > 0 THEN SUBSTR(name, INSTR(name, ' ') + 1)
                    ELSE NULL 
                END,
                email = COALESCE(
                    (SELECT c.email FROM contacts c WHERE c.client_id = clients.id LIMIT 1),
                    'noemail@example.com'
                )
        SQL);
        
        // Step 3: Make firstName and email NOT NULL after data migration
        $this->addSql('UPDATE clients SET firstName = "Unknown" WHERE firstName IS NULL OR firstName = ""');
        $this->addSql('UPDATE clients SET email = "noemail@example.com" WHERE email IS NULL OR email = ""');
        
        // Step 4: Drop old columns
        $this->addSql('ALTER TABLE clients DROP COLUMN name');
        $this->addSql('ALTER TABLE clients DROP COLUMN currency');
        $this->addSql('ALTER TABLE clients DROP COLUMN vat_number');
        $this->addSql('ALTER TABLE clients DROP COLUMN website');
        
        // Step 5: Create new unique constraint on email + company_id
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C82E74E7927C74979B1AD6 ON clients (email, company_id)');
    }

    public function down(Schema $schema): void
    {
        // Reverse migration - restore old structure
        $this->addSql('DROP INDEX UNIQ_C82E74E7927C74979B1AD6');
        
        // Add back old columns
        $this->addSql('ALTER TABLE clients ADD COLUMN name VARCHAR(125)');
        $this->addSql('ALTER TABLE clients ADD COLUMN currency VARCHAR(3)');
        $this->addSql('ALTER TABLE clients ADD COLUMN vat_number VARCHAR(255)');
        $this->addSql('ALTER TABLE clients ADD COLUMN website VARCHAR(125)');
        
        // Migrate data back
        $this->addSql('UPDATE clients SET name = firstName || CASE WHEN lastName IS NOT NULL THEN " " || lastName ELSE "" END');
        
        // Drop new columns
        $this->addSql('ALTER TABLE clients DROP COLUMN firstName');
        $this->addSql('ALTER TABLE clients DROP COLUMN lastName');
        $this->addSql('ALTER TABLE clients DROP COLUMN email');
        
        // Restore old unique constraint
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C82E745E237E06979B1AD6 ON clients (name, company_id)');
    }
}
