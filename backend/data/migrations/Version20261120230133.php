<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120230133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE penalties_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE penalties (id INT NOT NULL, loan_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, days_late INT NOT NULL, paid BOOLEAN NOT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B89F70AFCE73868F ON penalties (loan_id)');
        $this->addSql('ALTER TABLE penalties ADD CONSTRAINT FK_B89F70AFCE73868F FOREIGN KEY (loan_id) REFERENCES loans (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE penalties_id_seq CASCADE');
        $this->addSql('ALTER TABLE penalties DROP CONSTRAINT FK_B89F70AFCE73868F');
        $this->addSql('DROP TABLE penalties');
    }
}
