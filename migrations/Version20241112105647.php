<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112105647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE settings (id INT NOT NULL, upload_validation TINYINT(1) DEFAULT NULL, validator_number INT DEFAULT NULL, auto_display_incident TINYINT(1) DEFAULT NULL, auto_display_incident_timer VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', training TINYINT(1) DEFAULT NULL, operator_retraining_delay VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', auto_delete_operator_delay VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE incident ADD activate_auto_display TINYINT(1) DEFAULT NULL, ADD auto_display_priority INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE settings');
        $this->addSql('ALTER TABLE incident DROP activate_auto_display, DROP auto_display_priority');
    }
}
