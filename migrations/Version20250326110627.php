<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326110627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE workstation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, upload_id INT DEFAULT NULL, products_id INT DEFAULT NULL, department_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, INDEX IDX_CDF3D910CCCFBA31 (upload_id), INDEX IDX_CDF3D9106C8A81A9 (products_id), INDEX IDX_CDF3D910AE80F5DF (department_id), INDEX IDX_CDF3D9109F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9106C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9109F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910CCCFBA31');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9106C8A81A9');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910AE80F5DF');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9109F2C3FAB');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE workstation');
    }
}
