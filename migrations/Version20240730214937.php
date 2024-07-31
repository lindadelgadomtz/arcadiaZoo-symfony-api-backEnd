<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240730214937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery DROP animal_id, CHANGE habitat_id habitat_id INT NOT NULL');
        $this->addSql('ALTER TABLE habitat CHANGE description description VARCHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE service DROP nom, DROP description, DROP created_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery ADD animal_id INT DEFAULT NULL, CHANGE habitat_id habitat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE habitat CHANGE description description VARCHAR(200) NOT NULL');
        $this->addSql('ALTER TABLE service ADD nom VARCHAR(50) NOT NULL, ADD description VARCHAR(150) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
