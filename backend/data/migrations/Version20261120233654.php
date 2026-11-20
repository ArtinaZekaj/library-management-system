<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120233654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE system_logs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE system_logs (id INT NOT NULL, actor_id INT NOT NULL, action_type VARCHAR(255) NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E14375AA10DAF24A ON system_logs (actor_id)');
        $this->addSql('ALTER TABLE system_logs ADD CONSTRAINT FK_E14375AA10DAF24A FOREIGN KEY (actor_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE system_logs_id_seq CASCADE');
        $this->addSql('ALTER TABLE system_logs DROP CONSTRAINT FK_E14375AA10DAF24A');
        $this->addSql('DROP TABLE system_logs');
    }
}
