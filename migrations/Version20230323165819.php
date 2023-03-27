<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323165819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE upload ADD productline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61FD634D365 FOREIGN KEY (productline_id) REFERENCES product_line (id)');
        $this->addSql('CREATE INDEX IDX_17BDE61FD634D365 ON upload (productline_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61FD634D365');
        $this->addSql('DROP INDEX IDX_17BDE61FD634D365 ON upload');
        $this->addSql('ALTER TABLE upload DROP productline_id');
    }
}
