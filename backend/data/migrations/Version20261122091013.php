<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261122091013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default library system settings';
    }

    public function up(Schema $schema): void
    {
        $settings = [
            'borrow_fee' => '2.00',
            'penalty_rate' => '1.00',
            'borrowing_period' => '14',
            'borrowing_limit' => '3',
        ];

        foreach ($settings as $key => $value) {
            $this->addSql(
                'INSERT INTO system_settings ("key", "value")
                 VALUES (:key, :value)
                 ON CONFLICT ("key") DO NOTHING',
                [
                    'key' => $key,
                    'value' => $value,
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Default settings may have been modified; automatic removal is unsafe'
        );
    }
}
