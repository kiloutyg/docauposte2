<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230707121257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE button (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_3A06AC3D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, product_line_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_64C19C19CA26EF2 (product_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diplay_option (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, incident_category_id INT NOT NULL, product_line_id INT NOT NULL, name VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, active TINYINT(1) DEFAULT NULL, path VARCHAR(255) NOT NULL, INDEX IDX_3D03A11AAE6ED38F (incident_category_id), INDEX IDX_3D03A11A9CA26EF2 (product_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_line (id INT AUTO_INCREMENT NOT NULL, zone_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_5CFC96579F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upload (id INT AUTO_INCREMENT NOT NULL, button_id INT NOT NULL, display_option_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, expiry_date DATETIME DEFAULT NULL, uploaded_at DATETIME NOT NULL, INDEX IDX_17BDE61FA123E519 (button_id), INDEX IDX_17BDE61F7B75FA05 (display_option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE button ADD CONSTRAINT FK_3A06AC3D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C19CA26EF2 FOREIGN KEY (product_line_id) REFERENCES product_line (id)');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AAE6ED38F FOREIGN KEY (incident_category_id) REFERENCES incident_category (id)');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A9CA26EF2 FOREIGN KEY (product_line_id) REFERENCES product_line (id)');
        $this->addSql('ALTER TABLE product_line ADD CONSTRAINT FK_5CFC96579F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61FA123E519 FOREIGN KEY (button_id) REFERENCES button (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F7B75FA05 FOREIGN KEY (display_option_id) REFERENCES diplay_option (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE button DROP FOREIGN KEY FK_3A06AC3D12469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C19CA26EF2');
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11AAE6ED38F');
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11A9CA26EF2');
        $this->addSql('ALTER TABLE product_line DROP FOREIGN KEY FK_5CFC96579F2C3FAB');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61FA123E519');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F7B75FA05');
        $this->addSql('DROP TABLE button');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE diplay_option');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE incident_category');
        $this->addSql('DROP TABLE product_line');
        $this->addSql('DROP TABLE upload');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
