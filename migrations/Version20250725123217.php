<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725123217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE training_material_type ADD upload_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE training_material_type ADD CONSTRAINT FK_A61F558DCCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A61F558DCCCFBA31 ON training_material_type (upload_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE training_material_type DROP FOREIGN KEY FK_A61F558DCCCFBA31');
        $this->addSql('DROP INDEX UNIQ_A61F558DCCCFBA31 ON training_material_type');
        $this->addSql('ALTER TABLE training_material_type DROP upload_id');
    }
}
