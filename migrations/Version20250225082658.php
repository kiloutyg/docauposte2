<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225082658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE uap_operator (uap_id INT NOT NULL, operator_id INT NOT NULL, INDEX IDX_BF400AA4BC40DF92 (uap_id), INDEX IDX_BF400AA4584598A3 (operator_id), PRIMARY KEY(uap_id, operator_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE uap_operator ADD CONSTRAINT FK_BF400AA4BC40DF92 FOREIGN KEY (uap_id) REFERENCES uap (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE uap_operator ADD CONSTRAINT FK_BF400AA4584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE uap_operator DROP FOREIGN KEY FK_BF400AA4BC40DF92');
        $this->addSql('ALTER TABLE uap_operator DROP FOREIGN KEY FK_BF400AA4584598A3');
        $this->addSql('DROP TABLE uap_operator');
    }
}
