<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121082142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add created_at, updated_at and deleted_at to doi';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE doi ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE doi SET created_at=now()');
        $this->addSql("UPDATE doi set deleted_at=ADDTIME(now(),'00:01') where deleted = 1");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doi DROP created_at, DROP updated_at, DROP deleted_at');
    }
}
