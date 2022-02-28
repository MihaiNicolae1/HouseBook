<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228142812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD project_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766C1197C9 FOREIGN KEY (project_id_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_D8698A766C1197C9 ON document (project_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766C1197C9');
        $this->addSql('DROP INDEX IDX_D8698A766C1197C9 ON document');
        $this->addSql('ALTER TABLE document DROP project_id_id');
    }
}
