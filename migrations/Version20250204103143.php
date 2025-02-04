<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204103143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE faq_content ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE faq_content ADD CONSTRAINT FK_E5B45BF212469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_E5B45BF212469DE2 ON faq_content (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE faq_content DROP FOREIGN KEY FK_E5B45BF212469DE2');
        $this->addSql('DROP INDEX IDX_E5B45BF212469DE2 ON faq_content');
        $this->addSql('ALTER TABLE faq_content DROP category_id');
    }
}
