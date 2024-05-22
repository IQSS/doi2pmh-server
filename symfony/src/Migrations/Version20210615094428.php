<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615094428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set up database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE configuration (id INT AUTO_INCREMENT NOT NULL, repository_name VARCHAR(255) NOT NULL, admin_email VARCHAR(255) NOT NULL, earliest_datestamp DATE NOT NULL, cas_authentication TINYINT(1) NOT NULL, cas_version VARCHAR(255) DEFAULT NULL, cas_host VARCHAR(255) DEFAULT NULL, cas_port INT DEFAULT NULL, cas_uri VARCHAR(255) DEFAULT NULL, updated_doi_logs VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doi (id INT AUTO_INCREMENT NOT NULL, folder_id INT DEFAULT NULL, uri VARCHAR(255) NOT NULL, citation LONGTEXT DEFAULT NULL, json_content LONGTEXT DEFAULT NULL, deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6694147A841CB121 (uri), INDEX IDX_6694147A162CB942 (folder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folder (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_ECA209CD727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, root_folder_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, is_admin TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, first_connexion TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6495F3EA365 (root_folder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doi ADD CONSTRAINT FK_6694147A162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id)');
        $this->addSql('ALTER TABLE folder ADD CONSTRAINT FK_ECA209CD727ACA70 FOREIGN KEY (parent_id) REFERENCES folder (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495F3EA365 FOREIGN KEY (root_folder_id) REFERENCES folder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doi DROP FOREIGN KEY FK_6694147A162CB942');
        $this->addSql('ALTER TABLE folder DROP FOREIGN KEY FK_ECA209CD727ACA70');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495F3EA365');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE doi');
        $this->addSql('DROP TABLE folder');
        $this->addSql('DROP TABLE user');
    }
}
