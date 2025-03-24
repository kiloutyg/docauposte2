<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324091657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3D03A11A5E237E06 ON incident (name)');
        $this->addSql('ALTER TABLE operator DROP FOREIGN KEY FK_D7A6A781BC40DF92');
        $this->addSql('DROP INDEX IDX_D7A6A781BC40DF92 ON operator');
        $this->addSql('ALTER TABLE operator DROP uap_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61F5E237E06 ON team (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_819858735E237E06 ON uap (name)');
        $this->addSql('ALTER TABLE uap_operator ADD PRIMARY KEY (uap_id, operator_id)');
        $this->addSql('ALTER TABLE uap_operator ADD CONSTRAINT FK_BF400AA4BC40DF92 FOREIGN KEY (uap_id) REFERENCES uap (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE uap_operator ADD CONSTRAINT FK_BF400AA4584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF400AA4BC40DF92 ON uap_operator (uap_id)');
        $this->addSql('CREATE INDEX IDX_BF400AA4584598A3 ON uap_operator (operator_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_17BDE61F3C0BE965 ON upload (filename)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_3D03A11A5E237E06 ON incident');
        $this->addSql('DROP INDEX UNIQ_C4E0A61F5E237E06 ON team');
        $this->addSql('DROP INDEX UNIQ_819858735E237E06 ON uap');
        $this->addSql('ALTER TABLE operator ADD uap_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE operator ADD CONSTRAINT FK_D7A6A781BC40DF92 FOREIGN KEY (uap_id) REFERENCES uap (id)');
        $this->addSql('CREATE INDEX IDX_D7A6A781BC40DF92 ON operator (uap_id)');
        $this->addSql('DROP INDEX UNIQ_17BDE61F3C0BE965 ON upload');
        $this->addSql('ALTER TABLE uap_operator DROP FOREIGN KEY FK_BF400AA4BC40DF92');
        $this->addSql('ALTER TABLE uap_operator DROP FOREIGN KEY FK_BF400AA4584598A3');
        $this->addSql('DROP INDEX IDX_BF400AA4BC40DF92 ON uap_operator');
        $this->addSql('DROP INDEX IDX_BF400AA4584598A3 ON uap_operator');
        $this->addSql('DROP INDEX `primary` ON uap_operator');
    }
}
