<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629024834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add invoice_id to jobs table for Job-Invoice relationship';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__jobs AS SELECT id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated FROM jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE jobs
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE jobs (id BLOB NOT NULL --(DC2Type:ulid)
            , quote_id BLOB NOT NULL --(DC2Type:ulid)
            , client_id BLOB NOT NULL --(DC2Type:ulid)
            , company_id BLOB NOT NULL --(DC2Type:ulid)
            , invoice_id BLOB DEFAULT NULL --(DC2Type:ulid)
            , status VARCHAR(25) NOT NULL, description CLOB DEFAULT NULL, scheduled_date DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A8936DC5DB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC519EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO jobs (id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated) SELECT id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated FROM __temp__jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__jobs
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC5979B1AD6 ON jobs (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC519EB6921 ON jobs (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC5DB805178 ON jobs (quote_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A8936DC52989F1FD ON jobs (invoice_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__jobs AS SELECT id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated FROM jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE jobs
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE jobs (id BLOB NOT NULL --(DC2Type:ulid)
            , quote_id BLOB NOT NULL --(DC2Type:ulid)
            , client_id BLOB NOT NULL --(DC2Type:ulid)
            , company_id BLOB NOT NULL --(DC2Type:ulid)
            , status VARCHAR(25) NOT NULL, description CLOB DEFAULT NULL, scheduled_date DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A8936DC5DB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC519EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A8936DC5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO jobs (id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated) SELECT id, quote_id, client_id, company_id, status, description, scheduled_date, created, updated FROM __temp__jobs
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
            CREATE INDEX IDX_A8936DC5979B1AD6 ON jobs (company_id)
        SQL);
    }
}
