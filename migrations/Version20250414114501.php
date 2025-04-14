<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414114501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE iluo (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME DEFAULT NULL, operator_id INT DEFAULT NULL, product_id INT DEFAULT NULL, workstation_id INT DEFAULT NULL, trainer_id INT DEFAULT NULL, shift_leader_id INT DEFAULT NULL, quality_rep_id INT DEFAULT NULL, INDEX IDX_A9E2A4AB584598A3 (operator_id), INDEX IDX_A9E2A4AB4584665A (product_id), INDEX IDX_A9E2A4ABE29BB7D (workstation_id), INDEX IDX_A9E2A4ABFB08EDF6 (trainer_id), INDEX IDX_A9E2A4ABA2879DBD (shift_leader_id), INDEX IDX_A9E2A4ABEB018634 (quality_rep_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_checklist (id INT AUTO_INCREMENT NOT NULL, validation_date DATETIME DEFAULT NULL, iluo_level_id INT DEFAULT NULL, trainer_id INT DEFAULT NULL, shift_leader_id INT DEFAULT NULL, quality_rep_id INT DEFAULT NULL, iluo_id INT DEFAULT NULL, INDEX IDX_EAC35A5593AB0C98 (iluo_level_id), INDEX IDX_EAC35A55FB08EDF6 (trainer_id), INDEX IDX_EAC35A55A2879DBD (shift_leader_id), INDEX IDX_EAC35A55EB018634 (quality_rep_id), UNIQUE INDEX UNIQ_EAC35A5537038DA1 (iluo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_checklist_steps (iluo_checklist_id INT NOT NULL, steps_id INT NOT NULL, INDEX IDX_5077BC5E4E0F0F55 (iluo_checklist_id), INDEX IDX_5077BC5E1EBBD054 (steps_id), PRIMARY KEY(iluo_checklist_id, steps_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE iluo_levels (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B3BA5A5A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quality_rep (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7EDCDB5BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shift_leaders (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6E1D57EFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT DEFAULT NULL, training_material_type VARCHAR(255) DEFAULT NULL, subheading_id INT DEFAULT NULL, title_id INT DEFAULT NULL, iluo_level_id INT DEFAULT NULL, INDEX IDX_34220A723D6F834F (subheading_id), INDEX IDX_34220A72A9F87BD (title_id), INDEX IDX_34220A7293AB0C98 (iluo_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_upload (steps_id INT NOT NULL, upload_id INT NOT NULL, INDEX IDX_F0AAF06A1EBBD054 (steps_id), INDEX IDX_F0AAF06ACCCFBA31 (upload_id), PRIMARY KEY(steps_id, upload_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_subheadings (id INT AUTO_INCREMENT NOT NULL, heading VARCHAR(255) DEFAULT NULL, title_id INT DEFAULT NULL, iluo_level_id INT DEFAULT NULL, INDEX IDX_B0E983B9A9F87BD (title_id), INDEX IDX_B0E983B993AB0C98 (iluo_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE steps_title (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, iluo_level_id INT DEFAULT NULL, INDEX IDX_840D0A6593AB0C98 (iluo_level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE workstation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, upload_id INT DEFAULT NULL, products_id INT DEFAULT NULL, department_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, uap_id INT DEFAULT NULL, INDEX IDX_CDF3D910CCCFBA31 (upload_id), INDEX IDX_CDF3D9106C8A81A9 (products_id), INDEX IDX_CDF3D910AE80F5DF (department_id), INDEX IDX_CDF3D9109F2C3FAB (zone_id), INDEX IDX_CDF3D910BC40DF92 (uap_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4AB584598A3 FOREIGN KEY (operator_id) REFERENCES operator (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4AB4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABE29BB7D FOREIGN KEY (workstation_id) REFERENCES workstation (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABFB08EDF6 FOREIGN KEY (trainer_id) REFERENCES trainer (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABA2879DBD FOREIGN KEY (shift_leader_id) REFERENCES shift_leaders (id)');
        $this->addSql('ALTER TABLE iluo ADD CONSTRAINT FK_A9E2A4ABEB018634 FOREIGN KEY (quality_rep_id) REFERENCES quality_rep (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A5593AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55FB08EDF6 FOREIGN KEY (trainer_id) REFERENCES trainer (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55A2879DBD FOREIGN KEY (shift_leader_id) REFERENCES shift_leaders (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A55EB018634 FOREIGN KEY (quality_rep_id) REFERENCES quality_rep (id)');
        $this->addSql('ALTER TABLE iluo_checklist ADD CONSTRAINT FK_EAC35A5537038DA1 FOREIGN KEY (iluo_id) REFERENCES iluo (id)');
        $this->addSql('ALTER TABLE iluo_checklist_steps ADD CONSTRAINT FK_5077BC5E4E0F0F55 FOREIGN KEY (iluo_checklist_id) REFERENCES iluo_checklist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE iluo_checklist_steps ADD CONSTRAINT FK_5077BC5E1EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quality_rep ADD CONSTRAINT FK_7EDCDB5BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shift_leaders ADD CONSTRAINT FK_6E1D57EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A723D6F834F FOREIGN KEY (subheading_id) REFERENCES steps_subheadings (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A72A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7293AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06A1EBBD054 FOREIGN KEY (steps_id) REFERENCES steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_upload ADD CONSTRAINT FK_F0AAF06ACCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B9A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B993AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('ALTER TABLE steps_title ADD CONSTRAINT FK_840D0A6593AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9106C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D9109F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE workstation ADD CONSTRAINT FK_CDF3D910BC40DF92 FOREIGN KEY (uap_id) REFERENCES uap (id)');
        $this->addSql('ALTER TABLE operator ADD employment_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE training_record DROP iluo');
        $this->addSql('ALTER TABLE uap ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE uap ADD CONSTRAINT FK_81985873AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_81985873AE80F5DF ON uap (department_id)');
        $this->addSql('ALTER TABLE user CHANGE email_address email_address VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B08E074E ON user (email_address)');
        $this->addSql('ALTER TABLE zone ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_A0EBC007AE80F5DF ON zone (department_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4AB584598A3');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4AB4584665A');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABE29BB7D');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABFB08EDF6');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABA2879DBD');
        $this->addSql('ALTER TABLE iluo DROP FOREIGN KEY FK_A9E2A4ABEB018634');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A5593AB0C98');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55FB08EDF6');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55A2879DBD');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A55EB018634');
        $this->addSql('ALTER TABLE iluo_checklist DROP FOREIGN KEY FK_EAC35A5537038DA1');
        $this->addSql('ALTER TABLE iluo_checklist_steps DROP FOREIGN KEY FK_5077BC5E4E0F0F55');
        $this->addSql('ALTER TABLE iluo_checklist_steps DROP FOREIGN KEY FK_5077BC5E1EBBD054');
        $this->addSql('ALTER TABLE quality_rep DROP FOREIGN KEY FK_7EDCDB5BA76ED395');
        $this->addSql('ALTER TABLE shift_leaders DROP FOREIGN KEY FK_6E1D57EFA76ED395');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A723D6F834F');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A72A9F87BD');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7293AB0C98');
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06A1EBBD054');
        $this->addSql('ALTER TABLE steps_upload DROP FOREIGN KEY FK_F0AAF06ACCCFBA31');
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B9A9F87BD');
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B993AB0C98');
        $this->addSql('ALTER TABLE steps_title DROP FOREIGN KEY FK_840D0A6593AB0C98');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910CCCFBA31');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9106C8A81A9');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910AE80F5DF');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D9109F2C3FAB');
        $this->addSql('ALTER TABLE workstation DROP FOREIGN KEY FK_CDF3D910BC40DF92');
        $this->addSql('DROP TABLE iluo');
        $this->addSql('DROP TABLE iluo_checklist');
        $this->addSql('DROP TABLE iluo_checklist_steps');
        $this->addSql('DROP TABLE iluo_levels');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE quality_rep');
        $this->addSql('DROP TABLE shift_leaders');
        $this->addSql('DROP TABLE steps');
        $this->addSql('DROP TABLE steps_upload');
        $this->addSql('DROP TABLE steps_subheadings');
        $this->addSql('DROP TABLE steps_title');
        $this->addSql('DROP TABLE workstation');
        $this->addSql('ALTER TABLE uap DROP FOREIGN KEY FK_81985873AE80F5DF');
        $this->addSql('DROP INDEX IDX_81985873AE80F5DF ON uap');
        $this->addSql('ALTER TABLE uap DROP department_id');
        $this->addSql('ALTER TABLE training_record ADD iluo VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D649B08E074E ON user');
        $this->addSql('ALTER TABLE user CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE operator DROP employment_type');
        $this->addSql('ALTER TABLE zone DROP FOREIGN KEY FK_A0EBC007AE80F5DF');
        $this->addSql('DROP INDEX IDX_A0EBC007AE80F5DF ON zone');
        $this->addSql('ALTER TABLE zone DROP department_id');
    }
}
