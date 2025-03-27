<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326090739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iluo (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_operator (iluo_id INT NOT NULL, operator_id INT NOT NULL, INDEX IDX_1BBCE0BE37038DA1 (iluo_id), INDEX IDX_1BBCE0BE584598A3 (operator_id), PRIMARY KEY(iluo_id, operator_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_levels (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT DEFAULT NULL, training_material_type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_upload (steps_id INT NOT NULL, upload_id INT NOT NULL, INDEX IDX_F0AAF06A1EBBD054 (steps_id), INDEX IDX_F0AAF06ACCCFBA31 (upload_id), PRIMARY KEY(steps_id, upload_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_subheadings (id INT AUTO_INCREMENT NOT NULL, heading VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_title (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE iluo_operator ADD CONSTRAINT FK_1BBCE0BE37038DA1 FOREIGN KEY (iluo_id) REFERENCES iluo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iluo_operator ADD CONSTRAINT FK_1BBCE0BE584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06A1EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06ACCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo_operator DROP FOREIGN KEY FK_1BBCE0BE37038DA1');
        $this->addSql('ALTER TABLE iluo_operator DROP FOREIGN KEY FK_1BBCE0BE584598A3');
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06A1EBBD054');
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06ACCCFBA31');
        $this->addSql('DROP TABLE iluo');
        $this->addSql('DROP TABLE iluo_operator');
        $this->addSql('DROP TABLE iluo_levels');
        $this->addSql('DROP TABLE steps');
        $this->addSql('DROP TABLE steps_upload');
        $this->addSql('DROP TABLE steps_subheadings');
        $this->addSql('DROP TABLE steps_title');
    }
}
