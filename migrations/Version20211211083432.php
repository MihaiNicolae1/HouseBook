<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211211083432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE989D9B62 ON project (slug)');
        $this->addSql('ALTER TABLE stage ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C27C9369989D9B62 ON stage (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE989D9B62 ON project');
        $this->addSql('DROP INDEX UNIQ_C27C9369989D9B62 ON stage');
        $this->addSql('ALTER TABLE stage DROP created_at');
    }
    public function isTransactional(): bool
    {
        return false;
    }
}
