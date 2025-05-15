<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515092251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD1DE18A5E237E06 ON department (name)');
        $this->addSql('ALTER TABLE user ADD operator_id INT DEFAULT NULL, CHANGE email_address email_address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B08E074E ON user (email_address)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649584598A3 ON user (operator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CD1DE18A5E237E06 ON department');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649584598A3');
        $this->addSql('DROP INDEX UNIQ_8D93D649B08E074E ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649584598A3 ON user');
        $this->addSql('ALTER TABLE user DROP operator_id, CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');
    }
}
