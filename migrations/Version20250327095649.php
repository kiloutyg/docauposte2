<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327095649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE uap ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE uap ADD CONSTRAINT FK_81985873AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_81985873AE80F5DF ON uap (department_id)');
        $this->addSql('ALTER TABLE zone ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_A0EBC007AE80F5DF ON zone (department_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE uap DROP FOREIGN KEY FK_81985873AE80F5DF');
        $this->addSql('DROP INDEX IDX_81985873AE80F5DF ON uap');
        $this->addSql('ALTER TABLE uap DROP department_id');
        $this->addSql('ALTER TABLE zone DROP FOREIGN KEY FK_A0EBC007AE80F5DF');
        $this->addSql('DROP INDEX IDX_A0EBC007AE80F5DF ON zone');
        $this->addSql('ALTER TABLE zone DROP department_id');
    }
}
