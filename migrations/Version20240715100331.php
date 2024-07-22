<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240715100331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop utilisateurs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE utilisateurs');
    }

    public function down(Schema $schema): void
    {
        // If you need to revert this migration, you can recreate the table here.
        // The following is an example, adjust the columns as needed.
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(50) NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, role_id INT NOT NULL, PRIMARY KEY(id))');
    }
}
