<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725122630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06A1EBBD054');
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06ACCCFBA31');
        $this->addSql('DROP TABLE steps_upload');
        $this->addSql('ALTER TABLE operator DROP employment_type');
        $this->addSql('ALTER TABLE training_material_type ADD category VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE steps_upload (steps_id INT NOT NULL, upload_id INT NOT NULL, INDEX IDX_F0AAF06ACCCFBA31 (upload_id), INDEX IDX_F0AAF06A1EBBD054 (steps_id), PRIMARY KEY(steps_id, upload_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06A1EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06ACCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operator ADD employment_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE training_material_type DROP category');
    }
}
