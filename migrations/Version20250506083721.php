<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506083721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, program_id INT NOT NULL, user_id INT NOT NULL, uuid VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_219CDA4AD17F50A6 (uuid), INDEX IDX_219CDA4A3EB8070A (program_id), INDEX IDX_219CDA4AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A3EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A3EB8070A');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('DROP TABLE certificate');
    }
}
