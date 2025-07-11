<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250711124315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A72A9F87BD');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A723D6F834F');
        $this->addSql('DROP INDEX IDX_34220A72A9F87BD ON steps');
        $this->addSql('DROP INDEX IDX_34220A723D6F834F ON steps');
        $this->addSql('ALTER TABLE steps ADD steps_subheadings_id INT DEFAULT NULL, ADD steps_title_id INT DEFAULT NULL, DROP subheading_id, DROP title_id');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7265B6914B FOREIGN KEY (steps_subheadings_id) REFERENCES steps_subheadings (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A72AC035848 FOREIGN KEY (steps_title_id) REFERENCES steps_title (id)');
        $this->addSql('CREATE INDEX IDX_34220A7265B6914B ON steps (steps_subheadings_id)');
        $this->addSql('CREATE INDEX IDX_34220A72AC035848 ON steps (steps_title_id)');
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B9A9F87BD');
        $this->addSql('DROP INDEX IDX_B0E983B9A9F87BD ON steps_subheadings');
        $this->addSql('ALTER TABLE steps_subheadings CHANGE title_id steps_title_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B9AC035848 FOREIGN KEY (steps_title_id) REFERENCES steps_title (id)');
        $this->addSql('CREATE INDEX IDX_B0E983B9AC035848 ON steps_subheadings (steps_title_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps_subheadings DROP FOREIGN KEY FK_B0E983B9AC035848');
        $this->addSql('DROP INDEX IDX_B0E983B9AC035848 ON steps_subheadings');
        $this->addSql('ALTER TABLE steps_subheadings CHANGE steps_title_id title_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steps_subheadings ADD CONSTRAINT FK_B0E983B9A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('CREATE INDEX IDX_B0E983B9A9F87BD ON steps_subheadings (title_id)');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7265B6914B');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A72AC035848');
        $this->addSql('DROP INDEX IDX_34220A7265B6914B ON steps');
        $this->addSql('DROP INDEX IDX_34220A72AC035848 ON steps');
        $this->addSql('ALTER TABLE steps ADD subheading_id INT DEFAULT NULL, ADD title_id INT DEFAULT NULL, DROP steps_subheadings_id, DROP steps_title_id');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A72A9F87BD FOREIGN KEY (title_id) REFERENCES steps_title (id)');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A723D6F834F FOREIGN KEY (subheading_id) REFERENCES steps_subheadings (id)');
        $this->addSql('CREATE INDEX IDX_34220A72A9F87BD ON steps (title_id)');
        $this->addSql('CREATE INDEX IDX_34220A723D6F834F ON steps (subheading_id)');
    }
}
