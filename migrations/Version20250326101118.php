<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326101118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps ADD subheading_id INT DEFAULT NULL, ADD title_id INT DEFAULT NULL, ADD iluo_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A723D6F834F FOREIGN KEY (subheading_id) REFERENCES steps_subheadings (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A72A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7293AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('CREATE INDEX IDX_34220A723D6F834F ON steps (subheading_id)');
        $this->addSql('CREATE INDEX IDX_34220A72A9F87BD ON steps (title_id)');
        $this->addSql('CREATE INDEX IDX_34220A7293AB0C98 ON steps (iluo_level_id)');
        $this->addSql('ALTER TABLE steps_subheadings ADD title_id INT DEFAULT NULL, ADD iluo_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B9A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B993AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('CREATE INDEX IDX_B0E983B9A9F87BD ON steps_subheadings (title_id)');
        $this->addSql('CREATE INDEX IDX_B0E983B993AB0C98 ON steps_subheadings (iluo_level_id)');
        $this->addSql('ALTER TABLE steps_title ADD iluo_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps_title ADD CONSTRAINT FK_840D0A6593AB0C98 FOREIGN KEY (iluo_level_id) REFERENCES iluo_levels (id)');
        $this->addSql('CREATE INDEX IDX_840D0A6593AB0C98 ON steps_title (iluo_level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A723D6F834F');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A72A9F87BD');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7293AB0C98');
        $this->addSql('DROP INDEX IDX_34220A723D6F834F ON steps');
        $this->addSql('DROP INDEX IDX_34220A72A9F87BD ON steps');
        $this->addSql('DROP INDEX IDX_34220A7293AB0C98 ON steps');
        $this->addSql('ALTER TABLE steps DROP subheading_id, DROP title_id, DROP iluo_level_id');
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B9A9F87BD');
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B993AB0C98');
        $this->addSql('DROP INDEX IDX_B0E983B9A9F87BD ON steps_subheadings');
        $this->addSql('DROP INDEX IDX_B0E983B993AB0C98 ON steps_subheadings');
        $this->addSql('ALTER TABLE steps_subheadings DROP title_id, DROP iluo_level_id');
        $this->addSql('ALTER TABLE steps_title DROP FOREIGN KEY FK_840D0A6593AB0C98');
        $this->addSql('DROP INDEX IDX_840D0A6593AB0C98 ON steps_title');
        $this->addSql('ALTER TABLE steps_title DROP iluo_level_id');
    }
}
