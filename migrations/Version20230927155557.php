<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927155557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE approbation (id INT AUTO_INCREMENT NOT NULL, validation_id INT NOT NULL, user_approbator_id INT DEFAULT NULL, approval TINYINT(1) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, approved_at DATETIME DEFAULT NULL, INDEX IDX_65E61847A2274850 (validation_id), INDEX IDX_65E61847F5791B37 (user_approbator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE old_upload (id INT AUTO_INCREMENT NOT NULL, button_id INT NOT NULL, olduploader_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, olduploaded_at DATETIME NOT NULL, validated TINYINT(1) DEFAULT NULL, revision INT DEFAULT NULL, INDEX IDX_5E723598A123E519 (button_id), INDEX IDX_5E723598C90F4B51 (olduploader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE validation (id INT AUTO_INCREMENT NOT NULL, upload_id INT NOT NULL, status TINYINT(1) DEFAULT NULL, validated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_16AC5B6ECCCFBA31 (upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE approbation ADD CONSTRAINT FK_65E61847A2274850 FOREIGN KEY (validation_id) REFERENCES validation (id)');
        $this->addSql('ALTER TABLE approbation ADD CONSTRAINT FK_65E61847F5791B37 FOREIGN KEY (user_approbator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE old_upload ADD CONSTRAINT FK_5E723598A123E519 FOREIGN KEY (button_id) REFERENCES button (id)');
        $this->addSql('ALTER TABLE old_upload ADD CONSTRAINT FK_5E723598C90F4B51 FOREIGN KEY (olduploader_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE validation ADD CONSTRAINT FK_16AC5B6ECCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE display_option');
        $this->addSql('ALTER TABLE incident ADD uploader_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A16678C77 FOREIGN KEY (uploader_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3D03A11A16678C77 ON incident (uploader_id)');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F7B75FA05');
        $this->addSql('DROP INDEX IDX_17BDE61F7B75FA05 ON upload');
        $this->addSql('ALTER TABLE upload ADD old_upload_id INT DEFAULT NULL, ADD validated TINYINT(1) DEFAULT NULL, ADD revision INT DEFAULT NULL, DROP expiry_date, CHANGE display_option_id uploader_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F16678C77 FOREIGN KEY (uploader_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F897BFE80 FOREIGN KEY (old_upload_id) REFERENCES old_upload (id)');
        $this->addSql('CREATE INDEX IDX_17BDE61F16678C77 ON upload (uploader_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_17BDE61F897BFE80 ON upload (old_upload_id)');
        $this->addSql('ALTER TABLE user ADD department_id INT DEFAULT NULL, ADD email_address VARCHAR(255) DEFAULT NULL, ADD blocked TINYINT(1) DEFAULT NULL, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649AE80F5DF ON user (department_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F897BFE80');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE display_option (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE approbation DROP FOREIGN KEY FK_65E61847A2274850');
        $this->addSql('ALTER TABLE approbation DROP FOREIGN KEY FK_65E61847F5791B37');
        $this->addSql('ALTER TABLE old_upload DROP FOREIGN KEY FK_5E723598A123E519');
        $this->addSql('ALTER TABLE old_upload DROP FOREIGN KEY FK_5E723598C90F4B51');
        $this->addSql('ALTER TABLE validation DROP FOREIGN KEY FK_16AC5B6ECCCFBA31');
        $this->addSql('DROP TABLE approbation');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE old_upload');
        $this->addSql('DROP TABLE validation');
        $this->addSql('DROP INDEX IDX_8D93D649AE80F5DF ON user');
        $this->addSql('ALTER TABLE user DROP department_id, DROP email_address, DROP blocked, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11A16678C77');
        $this->addSql('DROP INDEX IDX_3D03A11A16678C77 ON incident');
        $this->addSql('ALTER TABLE incident DROP uploader_id');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F16678C77');
        $this->addSql('DROP INDEX IDX_17BDE61F16678C77 ON upload');
        $this->addSql('DROP INDEX UNIQ_17BDE61F897BFE80 ON upload');
        $this->addSql('ALTER TABLE upload ADD display_option_id INT DEFAULT NULL, ADD expiry_date DATETIME DEFAULT NULL, DROP uploader_id, DROP old_upload_id, DROP validated, DROP revision');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F7B75FA05 FOREIGN KEY (display_option_id) REFERENCES diplay_option (id)');
        $this->addSql('CREATE INDEX IDX_17BDE61F7B75FA05 ON upload (display_option_id)');
    }
}
