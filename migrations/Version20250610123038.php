<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610123038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP INDEX FK_8D93D649584598A3, ADD UNIQUE INDEX UNIQ_8D93D649584598A3 (operator_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B08E074E ON user (email_address)');
        $this->addSql('ALTER TABLE zone ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_A0EBC007AE80F5DF ON zone (department_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zone DROP FOREIGN KEY FK_A0EBC007AE80F5DF');
        $this->addSql('DROP INDEX IDX_A0EBC007AE80F5DF ON zone');
        $this->addSql('ALTER TABLE zone DROP department_id');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D649584598A3, ADD INDEX FK_8D93D649584598A3 (operator_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649B08E074E ON user');
    }
}
