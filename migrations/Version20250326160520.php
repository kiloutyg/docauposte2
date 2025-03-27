<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326160520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iluo_checklist (id INT AUTO_INCREMENT NOT NULL, validation_date DATETIME DEFAULT NULL, iluo_level_id INT DEFAULT NULL, trainer_id INT DEFAULT NULL, shift_leader_id INT DEFAULT NULL, quality_rep_id INT DEFAULT NULL, iluo_id INT DEFAULT NULL, INDEX IDX_EAC35A5593AB0C98 (iluo_level_id), INDEX IDX_EAC35A55FB08EDF6 (trainer_id), INDEX IDX_EAC35A55A2879DBD (shift_leader_id), INDEX IDX_EAC35A55EB018634 (quality_rep_id), UNIQUE INDEX UNIQ_EAC35A5537038DA1 (iluo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_checklist_steps (iluo_checklist_id INT NOT NULL, steps_id INT NOT NULL, INDEX IDX_5077BC5E4E0F0F55 (iluo_checklist_id), INDEX IDX_5077BC5E1EBBD054 (steps_id), PRIMARY KEY(iluo_checklist_id, steps_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A5593AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55FB08EDF6 FOREIGN KEY (trainer_id) REFERENCES trainer (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55A2879DBD FOREIGN KEY (shift_leader_id) REFERENCES shift_leaders (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55EB018634 FOREIGN KEY (quality_rep_id) REFERENCES quality_rep (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A5537038DA1 FOREIGN KEY (iluo_id) REFERENCES iluo (id)');
        $this->addSql('ALTER TABLE iluo_checklist_steps ADD CONSTRAINT FK_5077BC5E4E0F0F55 FOREIGN KEY (iluo_checklist_id) REFERENCES iluo_checklist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iluo_checklist_steps ADD CONSTRAINT FK_5077BC5E1EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A5593AB0C98');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55FB08EDF6');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55A2879DBD');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55EB018634');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A5537038DA1');
        $this->addSql('ALTER TABLE iluo_checklist_steps DROP FOREIGN KEY FK_5077BC5E4E0F0F55');
        $this->addSql('ALTER TABLE iluo_checklist_steps DROP FOREIGN KEY FK_5077BC5E1EBBD054');
        $this->addSql('DROP TABLE iluo_checklist');
        $this->addSql('DROP TABLE iluo_checklist_steps');
    }
}
