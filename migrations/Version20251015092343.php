<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015092343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD partner_firstname VARCHAR(100) DEFAULT NULL, ADD partner_lastname VARCHAR(100) DEFAULT NULL, ADD partner_patronyms VARCHAR(255) DEFAULT NULL, ADD partner_birthdate DATE NOT NULL, CHANGE evaluated_person_firstname evaluated_person_firstname VARCHAR(100) DEFAULT NULL, CHANGE evaluated_person_lastname evaluated_person_lastname VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP partner_firstname, DROP partner_lastname, DROP partner_patronyms, DROP partner_birthdate, CHANGE evaluated_person_firstname evaluated_person_firstname VARCHAR(100) NOT NULL, CHANGE evaluated_person_lastname evaluated_person_lastname VARCHAR(100) NOT NULL');
    }
}
