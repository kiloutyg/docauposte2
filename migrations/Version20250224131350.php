<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224131350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operator ADD inactive_since DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE settings ADD incident_auto_display TINYINT(1) DEFAULT 1, ADD incident_auto_display_timer VARCHAR(255) DEFAULT \'P00Y00M00DT00H10M00S\', ADD operator_inactivity_delay VARCHAR(255) DEFAULT \'P00Y03M00DT00H00M00S\', ADD operator_auto_delete_delay VARCHAR(255) DEFAULT \'P00Y03M00DT00H00M00S\', DROP auto_display_incident, DROP auto_display_incident_timer, DROP auto_delete_operator_delay, CHANGE upload_validation upload_validation TINYINT(1) DEFAULT 1, CHANGE validator_number validator_number INT DEFAULT 4, CHANGE operator_retraining_delay operator_retraining_delay VARCHAR(255) DEFAULT \'P00Y06M00DT00H00M00S\'');
        $this->addSql('ALTER TABLE trainer ADD demoted TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings ADD auto_display_incident TINYINT(1) DEFAULT NULL, ADD auto_display_incident_timer VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', ADD auto_delete_operator_delay VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', DROP incident_auto_display, DROP incident_auto_display_timer, DROP operator_inactivity_delay, DROP operator_auto_delete_delay, CHANGE upload_validation upload_validation TINYINT(1) DEFAULT NULL, CHANGE validator_number validator_number INT DEFAULT NULL, CHANGE operator_retraining_delay operator_retraining_delay VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE operator DROP inactive_since');
        $this->addSql('ALTER TABLE trainer DROP demoted');
    }
}
