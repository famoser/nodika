<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170430211212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, generation_date DATETIME NOT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL, distribution_output_json CLOB NOT NULL, generation_result_json CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, description CLOB NOT NULL, open_date_time DATETIME NOT NULL, close_date_time DATETIME NOT NULL, status INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, event_id INTEGER DEFAULT NULL, change_date_time DATETIME NOT NULL, change_type INTEGER NOT NULL, change_configuration_json CLOB NOT NULL, event_json CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70E4FA789E6B1585 ON member (organisation_id)');
        $this->addSql('CREATE TABLE newsletter (id INTEGER NOT NULL, choice INTEGER NOT NULL, message CLOB DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation (id INTEGER NOT NULL, is_active BOOLEAN NOT NULL, active_end DATETIME NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation_person (organisation_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(organisation_id, person_id))');
        $this->addSql('CREATE INDEX IDX_B6C70B6B9E6B1585 ON organisation_person (organisation_id)');
        $this->addSql('CREATE INDEX IDX_B6C70B6B217BBB47 ON organisation_person (person_id)');
        $this->addSql('CREATE TABLE person (id INTEGER NOT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person_organisation (person_id INTEGER NOT NULL, organisation_id INTEGER NOT NULL, PRIMARY KEY(person_id, organisation_id))');
        $this->addSql('CREATE INDEX IDX_5EFD2F9217BBB47 ON person_organisation (person_id)');
        $this->addSql('CREATE INDEX IDX_5EFD2F99E6B1585 ON person_organisation (organisation_id)');
        $this->addSql('CREATE TABLE product_attributes_product (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id))');
        $this->addSql('CREATE INDEX IDX_AD938BEB217BBB47 ON product_attributes_product (person_id)');
        $this->addSql('CREATE INDEX IDX_AD938BEB7597D3FE ON product_attributes_product (member_id)');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL, content CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F74B898A76ED395 ON setting (user_id)');
        $this->addSql('DROP INDEX UNIQ_957A6479C05FB297');
        $this->addSql('DROP INDEX UNIQ_957A6479A0D96FBF');
        $this->addSql('DROP INDEX UNIQ_957A647992FC23A8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__fos_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles FROM fos_user');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('CREATE TABLE fos_user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, username_canonical VARCHAR(180) NOT NULL COLLATE BINARY, email VARCHAR(180) NOT NULL COLLATE BINARY, email_canonical VARCHAR(180) NOT NULL COLLATE BINARY, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL COLLATE BINARY, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_957A6479217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO fos_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles FROM __temp__fos_user');
        $this->addSql('DROP TABLE __temp__fos_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479C05FB297 ON fos_user (confirmation_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479A0D96FBF ON fos_user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A647992FC23A8 ON fos_user (username_canonical)');
        $this->addSql('CREATE INDEX IDX_957A6479217BBB47 ON fos_user (person_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE organisation_person');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_organisation');
        $this->addSql('DROP TABLE product_attributes_product');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP INDEX UNIQ_957A647992FC23A8');
        $this->addSql('DROP INDEX UNIQ_957A6479A0D96FBF');
        $this->addSql('DROP INDEX UNIQ_957A6479C05FB297');
        $this->addSql('DROP INDEX IDX_957A6479217BBB47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__fos_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles FROM fos_user');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('CREATE TABLE fos_user (id INTEGER NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO fos_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles FROM __temp__fos_user');
        $this->addSql('DROP TABLE __temp__fos_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A647992FC23A8 ON fos_user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479A0D96FBF ON fos_user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479C05FB297 ON fos_user (confirmation_token)');
    }
}
