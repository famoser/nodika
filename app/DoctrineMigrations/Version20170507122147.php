<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170507122147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_active BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649217BBB47 ON user (person_id)');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP INDEX IDX_3BAE0AA77597D3FE');
        $this->addSql('DROP INDEX IDX_3BAE0AA7217BBB47');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_3BAE0AA77597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7C82CDCED FOREIGN KEY (event_line_id) REFERENCES event_line (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event (id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag) SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('DROP INDEX IDX_CEDFF85D9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line AS SELECT id, organisation_id, name, description FROM event_line');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_CEDFF85D9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_line (id, organisation_id, name, description) SELECT id, organisation_id, name, description FROM __temp__event_line');
        $this->addSql('DROP TABLE __temp__event_line');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('DROP INDEX IDX_A44E1BE0C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line_generation AS SELECT id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json FROM event_line_generation');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, generation_date DATETIME NOT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL COLLATE BINARY, distribution_output_json CLOB NOT NULL COLLATE BINARY, generation_result_json CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_A44E1BE0C82CDCED FOREIGN KEY (event_line_id) REFERENCES event_line (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_line_generation (id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json) SELECT id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json FROM __temp__event_line_generation');
        $this->addSql('DROP TABLE __temp__event_line_generation');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('DROP INDEX IDX_68CD3612CC914ED9');
        $this->addSql('DROP INDEX IDX_68CD3612987D2660');
        $this->addSql('DROP INDEX IDX_68CD361269F7084D');
        $this->addSql('DROP INDEX IDX_68CD36123D1B60F4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, description CLOB NOT NULL COLLATE BINARY, open_date_time DATETIME NOT NULL, close_date_time DATETIME NOT NULL, status INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_68CD3612CC914ED9 FOREIGN KEY (offered_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612987D2660 FOREIGN KEY (offered_by_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD361269F7084D FOREIGN KEY (offered_to_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD36123D1B60F4 FOREIGN KEY (offered_to_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer (id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status) SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('DROP INDEX IDX_3853319152A9D3E');
        $this->addSql('DROP INDEX IDX_385331971F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_entry AS SELECT id, event_offer_id, event_id FROM event_offer_entry');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_3853319152A9D3E FOREIGN KEY (event_offer_id) REFERENCES event_offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_385331971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer_entry (id, event_offer_id, event_id) SELECT id, event_offer_id, event_id FROM __temp__event_offer_entry');
        $this->addSql('DROP TABLE __temp__event_offer_entry');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, event_id, change_date_time, change_type, change_configuration_json, event_json FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, event_id INTEGER DEFAULT NULL, change_date_time DATETIME NOT NULL, change_type INTEGER NOT NULL, change_configuration_json CLOB NOT NULL COLLATE BINARY, event_json CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_4FDF0D2C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_past (id, event_id, change_date_time, change_type, change_configuration_json, event_json) SELECT id, event_id, change_date_time, change_type, change_configuration_json, event_json FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('DROP INDEX IDX_906517449E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice AS SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description FROM invoice');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL COLLATE BINARY, street CLOB DEFAULT NULL COLLATE BINARY, street_nr CLOB DEFAULT NULL COLLATE BINARY, address_line CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_906517449E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO invoice (id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description) SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description FROM __temp__invoice');
        $this->addSql('DROP TABLE __temp__invoice');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('DROP INDEX IDX_70E4FA789E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, job_title CLOB DEFAULT NULL COLLATE BINARY, given_name CLOB NOT NULL COLLATE BINARY, family_name CLOB NOT NULL COLLATE BINARY, street CLOB DEFAULT NULL COLLATE BINARY, street_nr CLOB DEFAULT NULL COLLATE BINARY, address_line CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, phone CLOB DEFAULT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, webpage CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_70E4FA789E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO member (id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage) SELECT id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE INDEX IDX_70E4FA789E6B1585 ON member (organisation_id)');
        $this->addSql('DROP INDEX IDX_B6C70B6B9E6B1585');
        $this->addSql('DROP INDEX IDX_B6C70B6B217BBB47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__organisation_person AS SELECT organisation_id, person_id FROM organisation_person');
        $this->addSql('DROP TABLE organisation_person');
        $this->addSql('CREATE TABLE organisation_person (organisation_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(organisation_id, person_id), CONSTRAINT FK_B6C70B6B9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6C70B6B217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organisation_person (organisation_id, person_id) SELECT organisation_id, person_id FROM __temp__organisation_person');
        $this->addSql('DROP TABLE __temp__organisation_person');
        $this->addSql('CREATE INDEX IDX_B6C70B6B9E6B1585 ON organisation_person (organisation_id)');
        $this->addSql('CREATE INDEX IDX_B6C70B6B217BBB47 ON organisation_person (person_id)');
        $this->addSql('DROP INDEX IDX_5EFD2F9217BBB47');
        $this->addSql('DROP INDEX IDX_5EFD2F99E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_organisation AS SELECT person_id, organisation_id FROM person_organisation');
        $this->addSql('DROP TABLE person_organisation');
        $this->addSql('CREATE TABLE person_organisation (person_id INTEGER NOT NULL, organisation_id INTEGER NOT NULL, PRIMARY KEY(person_id, organisation_id), CONSTRAINT FK_5EFD2F9217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5EFD2F99E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person_organisation (person_id, organisation_id) SELECT person_id, organisation_id FROM __temp__person_organisation');
        $this->addSql('DROP TABLE __temp__person_organisation');
        $this->addSql('CREATE INDEX IDX_5EFD2F9217BBB47 ON person_organisation (person_id)');
        $this->addSql('CREATE INDEX IDX_5EFD2F99E6B1585 ON person_organisation (organisation_id)');
        $this->addSql('DROP INDEX IDX_AD938BEB217BBB47');
        $this->addSql('DROP INDEX IDX_AD938BEB7597D3FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__product_attributes_product AS SELECT person_id, member_id FROM product_attributes_product');
        $this->addSql('DROP TABLE product_attributes_product');
        $this->addSql('CREATE TABLE product_attributes_product (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id), CONSTRAINT FK_AD938BEB217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD938BEB7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO product_attributes_product (person_id, member_id) SELECT person_id, member_id FROM __temp__product_attributes_product');
        $this->addSql('DROP TABLE __temp__product_attributes_product');
        $this->addSql('CREATE INDEX IDX_AD938BEB217BBB47 ON product_attributes_product (person_id)');
        $this->addSql('CREATE INDEX IDX_AD938BEB7597D3FE ON product_attributes_product (member_id)');
        $this->addSql('DROP INDEX IDX_9F74B898A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, user_id, "key", content FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL COLLATE BINARY, content CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_9F74B898A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setting (id, user_id, "key", content) SELECT id, user_id, "key", content FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B898A76ED395 ON setting (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE fos_user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, password_hash CLOB NOT NULL COLLATE BINARY, is_active BOOLEAN NOT NULL, email CLOB NOT NULL COLLATE BINARY, reset_hash CLOB NOT NULL COLLATE BINARY, registration_date DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_957A6479217BBB47 ON fos_user (person_id)');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_3BAE0AA77597D3FE');
        $this->addSql('DROP INDEX IDX_3BAE0AA7217BBB47');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event (id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag) SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('DROP INDEX IDX_CEDFF85D9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line AS SELECT id, organisation_id, name, description FROM event_line');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_line (id, organisation_id, name, description) SELECT id, organisation_id, name, description FROM __temp__event_line');
        $this->addSql('DROP TABLE __temp__event_line');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('DROP INDEX IDX_A44E1BE0C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line_generation AS SELECT id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json FROM event_line_generation');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, generation_date DATETIME NOT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL, distribution_output_json CLOB NOT NULL, generation_result_json CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_line_generation (id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json) SELECT id, event_line_id, generation_date, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json FROM __temp__event_line_generation');
        $this->addSql('DROP TABLE __temp__event_line_generation');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('DROP INDEX IDX_68CD3612CC914ED9');
        $this->addSql('DROP INDEX IDX_68CD3612987D2660');
        $this->addSql('DROP INDEX IDX_68CD361269F7084D');
        $this->addSql('DROP INDEX IDX_68CD36123D1B60F4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, description CLOB NOT NULL, open_date_time DATETIME NOT NULL, close_date_time DATETIME NOT NULL, status INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_offer (id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status) SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, open_date_time, close_date_time, status FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('DROP INDEX IDX_3853319152A9D3E');
        $this->addSql('DROP INDEX IDX_385331971F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_entry AS SELECT id, event_offer_id, event_id FROM event_offer_entry');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_offer_entry (id, event_offer_id, event_id) SELECT id, event_offer_id, event_id FROM __temp__event_offer_entry');
        $this->addSql('DROP TABLE __temp__event_offer_entry');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, event_id, change_date_time, change_type, change_configuration_json, event_json FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, event_id INTEGER DEFAULT NULL, change_date_time DATETIME NOT NULL, change_type INTEGER NOT NULL, change_configuration_json CLOB NOT NULL, event_json CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_past (id, event_id, change_date_time, change_type, change_configuration_json, event_json) SELECT id, event_id, change_date_time, change_type, change_configuration_json, event_json FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('DROP INDEX IDX_906517449E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice AS SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description FROM invoice');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO invoice (id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description) SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description FROM __temp__invoice');
        $this->addSql('DROP TABLE __temp__invoice');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('DROP INDEX IDX_70E4FA789E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO member (id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage) SELECT id, organisation_id, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE INDEX IDX_70E4FA789E6B1585 ON member (organisation_id)');
        $this->addSql('DROP INDEX IDX_B6C70B6B9E6B1585');
        $this->addSql('DROP INDEX IDX_B6C70B6B217BBB47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__organisation_person AS SELECT organisation_id, person_id FROM organisation_person');
        $this->addSql('DROP TABLE organisation_person');
        $this->addSql('CREATE TABLE organisation_person (organisation_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(organisation_id, person_id))');
        $this->addSql('INSERT INTO organisation_person (organisation_id, person_id) SELECT organisation_id, person_id FROM __temp__organisation_person');
        $this->addSql('DROP TABLE __temp__organisation_person');
        $this->addSql('CREATE INDEX IDX_B6C70B6B9E6B1585 ON organisation_person (organisation_id)');
        $this->addSql('CREATE INDEX IDX_B6C70B6B217BBB47 ON organisation_person (person_id)');
        $this->addSql('DROP INDEX IDX_5EFD2F9217BBB47');
        $this->addSql('DROP INDEX IDX_5EFD2F99E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_organisation AS SELECT person_id, organisation_id FROM person_organisation');
        $this->addSql('DROP TABLE person_organisation');
        $this->addSql('CREATE TABLE person_organisation (person_id INTEGER NOT NULL, organisation_id INTEGER NOT NULL, PRIMARY KEY(person_id, organisation_id))');
        $this->addSql('INSERT INTO person_organisation (person_id, organisation_id) SELECT person_id, organisation_id FROM __temp__person_organisation');
        $this->addSql('DROP TABLE __temp__person_organisation');
        $this->addSql('CREATE INDEX IDX_5EFD2F9217BBB47 ON person_organisation (person_id)');
        $this->addSql('CREATE INDEX IDX_5EFD2F99E6B1585 ON person_organisation (organisation_id)');
        $this->addSql('DROP INDEX IDX_AD938BEB217BBB47');
        $this->addSql('DROP INDEX IDX_AD938BEB7597D3FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__product_attributes_product AS SELECT person_id, member_id FROM product_attributes_product');
        $this->addSql('DROP TABLE product_attributes_product');
        $this->addSql('CREATE TABLE product_attributes_product (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id))');
        $this->addSql('INSERT INTO product_attributes_product (person_id, member_id) SELECT person_id, member_id FROM __temp__product_attributes_product');
        $this->addSql('DROP TABLE __temp__product_attributes_product');
        $this->addSql('CREATE INDEX IDX_AD938BEB217BBB47 ON product_attributes_product (person_id)');
        $this->addSql('CREATE INDEX IDX_AD938BEB7597D3FE ON product_attributes_product (member_id)');
    }
}
