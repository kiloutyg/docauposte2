<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327091026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo_operator DROP FOREIGN KEY FK_1BBCE0BE584598A3');
        $this->addSql('ALTER TABLE iluo_operator DROP FOREIGN KEY FK_1BBCE0BE37038DA1');
        $this->addSql('DROP TABLE iluo_operator');
        $this->addSql('ALTER TABLE iluo ADD operator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4AB584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4AB584598A3 ON iluo (operator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iluo_operator (iluo_id INT NOT NULL, operator_id INT NOT NULL, INDEX IDX_1BBCE0BE37038DA1 (iluo_id), INDEX IDX_1BBCE0BE584598A3 (operator_id), PRIMARY KEY(iluo_id, operator_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE iluo_operator ADD CONSTRAINT FK_1BBCE0BE584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iluo_operator ADD CONSTRAINT FK_1BBCE0BE37038DA1 FOREIGN KEY (iluo_id) REFERENCES iluo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4AB584598A3');
        $this->addSql('DROP INDEX IDX_A9E2A4AB584598A3 ON iluo');
        $this->addSql('ALTER TABLE iluo DROP operator_id');
    }
}
