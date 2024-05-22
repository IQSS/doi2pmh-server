<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122071819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add column excluded_types to configuration and to_ignore to doi';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE configuration ADD excluded_types JSON NOT NULL DEFAULT \'[]\' COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE doi ADD to_ignore TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE configuration DROP excluded_types');
        $this->addSql('ALTER TABLE doi DROP to_ignore');
    }
}
