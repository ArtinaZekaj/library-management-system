<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix automatic ID generation for users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id'
        );

        $this->addSql(
            "ALTER TABLE public.users ALTER COLUMN id
             SET DEFAULT nextval('public.users_id_seq')"
        );

        $this->addSql(
            "SELECT setval(
                'public.users_id_seq',
                GREATEST(
                    COALESCE((SELECT MAX(id) FROM public.users), 1),
                    (SELECT last_value FROM public.users_id_seq)
                ),
                (SELECT EXISTS(SELECT 1 FROM public.users)
                    OR is_called FROM public.users_id_seq)
            )"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE public.users ALTER COLUMN id DROP DEFAULT'
        );

        $this->addSql(
            'ALTER SEQUENCE public.users_id_seq OWNED BY NONE'
        );
    }
}
