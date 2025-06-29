<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629034424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE jobs ADD COLUMN job_id VARCHAR(255) NULL
        SQL);
        
        // Populate job_id for existing records
        $this->addSql(<<<'SQL'
            UPDATE jobs SET job_id = '#' || (ROW_NUMBER() OVER (ORDER BY created)) WHERE job_id IS NULL OR job_id = ''
        SQL);
        
        // Make job_id NOT NULL after populating
        $this->addSql(<<<'SQL'
            ALTER TABLE jobs ALTER COLUMN job_id SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__jobs AS SELECT id, quote_id, client_id, invoice_id, company_id, status, description, scheduled_date, created, updated FROM jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE jobs
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE jobs (id BLOB NOT NULL --(DC2Type:ulid)
            , quote_id BLOB DEFAULT NULL --(DC2Type:ulid)
            , client_id BLOB NOT NULL --(DC2Type:ulid)
            , invoice_id BLOB DEFAULT NULL --(DC2Type:ulid)
            , company_id BLOB NOT NULL --(DC2Type:ulid)
            , status VARCHAR(25) NOT NULL, description CLOB DEFAULT NULL, scheduled_date DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A8936DC5DB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC519EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO jobs (id, quote_id, client_id, invoice_id, company_id, status, description, scheduled_date, created, updated) SELECT id, quote_id, client_id, invoice_id, company_id, status, description, scheduled_date, created, updated FROM __temp__jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__jobs
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC5DB805178 ON jobs (quote_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC519EB6921 ON jobs (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC52989F1FD ON jobs (invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC5979B1AD6 ON jobs (company_id)
        SQL);
    }
}
