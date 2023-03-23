<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323120754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_line (id INT AUTO_INCREMENT NOT NULL, zone_id_id INT NOT NULL, zone_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_5CFC9657D96A31F1 (zone_id_id), INDEX IDX_5CFC96579F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_line ADD CONSTRAINT FK_5CFC9657D96A31F1 FOREIGN KEY (zone_id_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE product_line ADD CONSTRAINT FK_5CFC96579F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7613A7E6FF');
        $this->addSql('ALTER TABLE product_line DROP FOREIGN KEY FK_5CFC9657D96A31F1');
        $this->addSql('ALTER TABLE product_line DROP FOREIGN KEY FK_5CFC96579F2C3FAB');
        $this->addSql('DROP TABLE product_line');
    }
}
