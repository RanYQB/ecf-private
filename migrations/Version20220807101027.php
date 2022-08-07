<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220807101027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partner DROP is_active');
        $this->addSql('ALTER TABLE structure DROP is_active');
        $this->addSql('ALTER TABLE user ADD is_active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partner ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE structure ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE `user` DROP is_active');
    }
}
