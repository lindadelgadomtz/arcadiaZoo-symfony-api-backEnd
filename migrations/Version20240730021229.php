<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240730021229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_animal ADD CONSTRAINT FK_C828CFBD4E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gallery_animal ADD CONSTRAINT FK_C828CFBD8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE habitat CHANGE description description VARCHAR(80) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_animal DROP FOREIGN KEY FK_C828CFBD4E7AF8F');
        $this->addSql('ALTER TABLE gallery_animal DROP FOREIGN KEY FK_C828CFBD8E962C16');
        $this->addSql('ALTER TABLE habitat CHANGE description description VARCHAR(200) NOT NULL');
    }
}
