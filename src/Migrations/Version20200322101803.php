<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200322101803 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE doi ADD COLUMN citation VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__doi AS SELECT id, uri, folder_id FROM doi');
        $this->addSql('DROP TABLE doi');
        $this->addSql('CREATE TABLE doi (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, uri VARCHAR(255) NOT NULL, folder_id INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO doi (id, uri, folder_id) SELECT id, uri, folder_id FROM __temp__doi');
        $this->addSql('DROP TABLE __temp__doi');
    }
}
