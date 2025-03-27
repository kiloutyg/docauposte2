<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327133904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workstation ADD uap_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910BC40DF92 FOREIGN KEY (uap_id) REFERENCES uap (id)');
        $this->addSql('CREATE INDEX IDX_CDF3D910BC40DF92 ON workstation (uap_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910BC40DF92');
        $this->addSql('DROP INDEX IDX_CDF3D910BC40DF92 ON workstation');
        $this->addSql('ALTER TABLE workstation DROP uap_id');
    }
}
