<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220807154032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E16A76ED395');
        $this->addSql('CREATE FULLTEXT INDEX Partner_index ON partner (name)');
        $this->addSql('DROP INDEX uniq_312b3e16a76ed395 ON partner');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE96078AA76ED395 ON partner (user_id)');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E16A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX Partner_index ON Partner');
        $this->addSql('ALTER TABLE Partner DROP FOREIGN KEY FK_FE96078AA76ED395');
        $this->addSql('DROP INDEX uniq_fe96078aa76ed395 ON Partner');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_312B3E16A76ED395 ON Partner (user_id)');
        $this->addSql('ALTER TABLE Partner ADD CONSTRAINT FK_FE96078AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }
}
