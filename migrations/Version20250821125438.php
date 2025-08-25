<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821125438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo_levels ADD priority_order INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9106C8A81A9');
        $this->addSql('DROP INDEX IDX_CDF3D9106C8A81A9 ON workstation');
        $this->addSql('ALTER TABLE workstation CHANGE products_id product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9104584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('CREATE INDEX IDX_CDF3D9104584665A ON workstation (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo_levels DROP priority_order');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9104584665A');
        $this->addSql('DROP INDEX IDX_CDF3D9104584665A ON workstation');
        $this->addSql('ALTER TABLE workstation CHANGE product_id products_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9106C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
        $this->addSql('CREATE INDEX IDX_CDF3D9106C8A81A9 ON workstation (products_id)');
    }
}
