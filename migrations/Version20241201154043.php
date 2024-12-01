<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201154043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor AS SELECT id, is_administrator, receives_administrator_mail, email, password_hash, reset_hash, is_enabled, registration_date, last_login_date, invitation_identifier, last_invitation, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at FROM doctor');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_administrator BOOLEAN NOT NULL, receives_administrator_mail BOOLEAN NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME DEFAULT NULL, last_login_date DATETIME DEFAULT NULL, invitation_identifier CLOB DEFAULT NULL, last_invitation DATETIME DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO doctor (id, is_administrator, receives_administrator_mail, email, password_hash, reset_hash, is_enabled, registration_date, last_login_date, invitation_identifier, last_invitation, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at) SELECT id, is_administrator, receives_administrator_mail, email, password_hash, reset_hash, is_enabled, registration_date, last_login_date, invitation_identifier, last_invitation, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at FROM __temp__doctor');
        $this->addSql('DROP TABLE __temp__doctor');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) NOT NULL COLLATE "BINARY", executed_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(version))');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor AS SELECT id, is_administrator, receives_administrator_mail, street, street_nr, address_line, postal_code, city, country, phone, email, invitation_identifier, last_invitation, job_title, given_name, family_name, deleted_at, password_hash, reset_hash, is_enabled, registration_date, last_login_date FROM doctor');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_administrator BOOLEAN NOT NULL, receives_administrator_mail BOOLEAN NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, invitation_identifier CLOB DEFAULT NULL, last_invitation DATETIME DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME DEFAULT NULL, last_login_date DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO doctor (id, is_administrator, receives_administrator_mail, street, street_nr, address_line, postal_code, city, country, phone, email, invitation_identifier, last_invitation, job_title, given_name, family_name, deleted_at, password_hash, reset_hash, is_enabled, registration_date, last_login_date) SELECT id, is_administrator, receives_administrator_mail, street, street_nr, address_line, postal_code, city, country, phone, email, invitation_identifier, last_invitation, job_title, given_name, family_name, deleted_at, password_hash, reset_hash, is_enabled, registration_date, last_login_date FROM __temp__doctor');
        $this->addSql('DROP TABLE __temp__doctor');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AE7927C74 ON doctor (email)');
    }
}
