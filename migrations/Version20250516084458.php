<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250516084458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift_leaders ADD operator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shift_leaders ADD CONSTRAINT FK_6E1D57EF584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6E1D57EF584598A3 ON shift_leaders (operator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift_leaders DROP FOREIGN KEY FK_6E1D57EF584598A3');
        $this->addSql('DROP INDEX UNIQ_6E1D57EF584598A3 ON shift_leaders');
        $this->addSql('ALTER TABLE shift_leaders DROP operator_id');
    }
}
