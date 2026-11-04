<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261104035357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach (['Politics', 'Culture', 'Sport', 'Technology'] as $categoryName) {
            $this->addSql('INSERT INTO category (name) VALUES (?)', [$categoryName]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM category WHERE name IN ('Politics', 'Culture', 'Sport', 'Technology')");
    }
}
