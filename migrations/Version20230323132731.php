<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323132731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7613A7E6FF');
        $this->addSql('DROP INDEX IDX_D8698A7613A7E6FF ON document');
        $this->addSql('ALTER TABLE document CHANGE product_line_id_id productline_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76D634D365 FOREIGN KEY (productline_id) REFERENCES product_line (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76D634D365 ON document (productline_id)');
        $this->addSql('ALTER TABLE product_line DROP FOREIGN KEY FK_5CFC9657D96A31F1');
        $this->addSql('DROP INDEX IDX_5CFC9657D96A31F1 ON product_line');
        $this->addSql('ALTER TABLE product_line DROP zone_id_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76D634D365');
        $this->addSql('DROP INDEX IDX_D8698A76D634D365 ON document');
        $this->addSql('ALTER TABLE document CHANGE productline_id product_line_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7613A7E6FF FOREIGN KEY (product_line_id_id) REFERENCES product_line (id)');
        $this->addSql('CREATE INDEX IDX_D8698A7613A7E6FF ON document (product_line_id_id)');
        $this->addSql('ALTER TABLE product_line ADD zone_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_line ADD CONSTRAINT FK_5CFC9657D96A31F1 FOREIGN KEY (zone_id_id) REFERENCES zone (id)');
        $this->addSql('CREATE INDEX IDX_5CFC9657D96A31F1 ON product_line (zone_id_id)');
    }
}
