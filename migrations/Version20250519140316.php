<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519140316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE steps_training_material_type (steps_id INT NOT NULL, training_material_type_id INT NOT NULL, INDEX IDX_B52D1F821EBBD054 (steps_id), INDEX IDX_B52D1F82C568E9A1 (training_material_type_id), PRIMARY KEY(steps_id, training_material_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE steps_training_material_type ADD CONSTRAINT FK_B52D1F821EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_training_material_type ADD CONSTRAINT FK_B52D1F82C568E9A1 FOREIGN KEY (training_material_type_id) REFERENCES training_material_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps_training_material_type DROP FOREIGN KEY FK_B52D1F821EBBD054');
        $this->addSql('ALTER TABLE steps_training_material_type DROP FOREIGN KEY FK_B52D1F82C568E9A1');
        $this->addSql('DROP TABLE steps_training_material_type');
    }
}
