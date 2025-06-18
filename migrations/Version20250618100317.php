<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618100317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE about_translation (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_107BB9AE232D562B (object_id), UNIQUE INDEX lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE about_translation ADD CONSTRAINT FK_107BB9AE232D562B FOREIGN KEY (object_id) REFERENCES about (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE program_translation CHANGE object_id object_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON program_translation (locale, object_id, field)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_translation DROP FOREIGN KEY FK_107BB9AE232D562B');
        $this->addSql('DROP TABLE about_translation');
        $this->addSql('DROP INDEX lookup_unique_idx ON program_translation');
        $this->addSql('ALTER TABLE program_translation CHANGE object_id object_id INT NOT NULL');
    }
}
