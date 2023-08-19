<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230222192334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clients (id INT NOT NULL, rate_id INT DEFAULT NULL, instance_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, ratePeriodStart DATETIME DEFAULT NULL, ratePeriodEnd DATETIME DEFAULT NULL, vpnKey VARCHAR(255) DEFAULT NULL, INDEX IDX_C82E74BC999F9F (rate_id), INDEX IDX_C82E743A51721D (instance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hostings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, provider VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instances (id INT AUTO_INCREMENT NOT NULL, hosting_id INT DEFAULT NULL, country VARCHAR(255) NOT NULL, capacity INT NOT NULL, protocol VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, connection LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_7A270069AE9044EA (hosting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instances_rates (instance_id INT NOT NULL, rate_id INT NOT NULL, INDEX IDX_859463333A51721D (instance_id), INDEX IDX_85946333BC999F9F (rate_id), PRIMARY KEY(instance_id, rate_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mails (id INT AUTO_INCREMENT NOT NULL, author VARCHAR(255) NOT NULL, receiver VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, template VARCHAR(255) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', sendAt DATETIME NOT NULL, sendedAt DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, bucket VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, UNIQUE INDEX url_idx (bucket, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, duration INT NOT NULL, description VARCHAR(255) NOT NULL, price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role INT NOT NULL, lastLogin DATETIME NOT NULL, secretToken VARCHAR(64) DEFAULT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E74BC999F9F FOREIGN KEY (rate_id) REFERENCES rates (id)');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E743A51721D FOREIGN KEY (instance_id) REFERENCES instances (id)');
        $this->addSql('ALTER TABLE instances ADD CONSTRAINT FK_7A270069AE9044EA FOREIGN KEY (hosting_id) REFERENCES hostings (id)');
        $this->addSql('ALTER TABLE instances_rates ADD CONSTRAINT FK_859463333A51721D FOREIGN KEY (instance_id) REFERENCES instances (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE instances_rates ADD CONSTRAINT FK_85946333BC999F9F FOREIGN KEY (rate_id) REFERENCES rates (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E74BC999F9F');
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E743A51721D');
        $this->addSql('ALTER TABLE instances DROP FOREIGN KEY FK_7A270069AE9044EA');
        $this->addSql('ALTER TABLE instances_rates DROP FOREIGN KEY FK_859463333A51721D');
        $this->addSql('ALTER TABLE instances_rates DROP FOREIGN KEY FK_85946333BC999F9F');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE hostings');
        $this->addSql('DROP TABLE instances');
        $this->addSql('DROP TABLE instances_rates');
        $this->addSql('DROP TABLE mails');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE rates');
        $this->addSql('DROP TABLE users');
    }
}
