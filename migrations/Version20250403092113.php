<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403092113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_result (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, section_id INT DEFAULT NULL, completed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', score INT NOT NULL, INDEX IDX_FE2E314AA76ED395 (user_id), INDEX IDX_FE2E314AD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AD823E37A FOREIGN KEY (section_id) REFERENCES sections (id)');
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE section_id section_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314AA76ED395');
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314AD823E37A');
        $this->addSql('DROP TABLE quiz_result');
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT NOT NULL');
        $this->addSql('ALTER TABLE question CHANGE section_id section_id INT NOT NULL');
    }
}
