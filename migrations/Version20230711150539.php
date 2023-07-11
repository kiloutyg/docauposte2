<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230711150539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diplay_option (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F7B75FA05');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F7B75FA05 FOREIGN KEY (display_option_id) REFERENCES diplay_option (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F7B75FA05');
        $this->addSql('DROP TABLE diplay_option');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F7B75FA05');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F7B75FA05 FOREIGN KEY (display_option_id) REFERENCES display_option (id)');
    }
}
