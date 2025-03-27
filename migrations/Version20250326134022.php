<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326134022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quality_rep (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7EDCDB5BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shift_leaders (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6E1D57EFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quality_rep ADD CONSTRAINT FK_7EDCDB5BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shift_leaders ADD CONSTRAINT FK_6E1D57EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE iluo ADD start_date DATETIME DEFAULT NULL, ADD product_id INT DEFAULT NULL, ADD workstation_id INT DEFAULT NULL, ADD trainer_id INT DEFAULT NULL, ADD shift_leader_id INT DEFAULT NULL, ADD quality_rep_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4AB4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABE29BB7D FOREIGN KEY (workstation_id) REFERENCES workstation (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABFB08EDF6 FOREIGN KEY (trainer_id) REFERENCES trainer (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABA2879DBD FOREIGN KEY (shift_leader_id) REFERENCES shift_leaders (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABEB018634 FOREIGN KEY (quality_rep_id) REFERENCES quality_rep (id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4AB4584665A ON iluo (product_id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4ABE29BB7D ON iluo (workstation_id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4ABFB08EDF6 ON iluo (trainer_id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4ABA2879DBD ON iluo (shift_leader_id)');
        $this->addSql('CREATE INDEX IDX_A9E2A4ABEB018634 ON iluo (quality_rep_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quality_rep DROP FOREIGN KEY FK_7EDCDB5BA76ED395');
        $this->addSql('ALTER TABLE shift_leaders DROP FOREIGN KEY FK_6E1D57EFA76ED395');
        $this->addSql('DROP TABLE quality_rep');
        $this->addSql('DROP TABLE shift_leaders');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4AB4584665A');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABE29BB7D');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABFB08EDF6');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABA2879DBD');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABEB018634');
        $this->addSql('DROP INDEX IDX_A9E2A4AB4584665A ON iluo');
        $this->addSql('DROP INDEX IDX_A9E2A4ABE29BB7D ON iluo');
        $this->addSql('DROP INDEX IDX_A9E2A4ABFB08EDF6 ON iluo');
        $this->addSql('DROP INDEX IDX_A9E2A4ABA2879DBD ON iluo');
        $this->addSql('DROP INDEX IDX_A9E2A4ABEB018634 ON iluo');
        $this->addSql('ALTER TABLE iluo DROP start_date, DROP product_id, DROP workstation_id, DROP trainer_id, DROP shift_leader_id, DROP quality_rep_id');
    }
}
