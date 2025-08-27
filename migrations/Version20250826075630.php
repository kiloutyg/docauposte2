<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826075630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo ADD updated_at DATETIME DEFAULT NULL, ADD last_level_known_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4AB202DE52A FOREIGN KEY (last_level_known_id) REFERENCES iluo_levels (id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4AB202DE52A ON iluo (last_level_known_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4AB202DE52A');
        $this->addSql('DROP INDEX IDX_A9E2A4AB202DE52A ON iluo');
        $this->addSql('ALTER TABLE iluo DROP updated_at, DROP last_level_known_id');
    }
}
