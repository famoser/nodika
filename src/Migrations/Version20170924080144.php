<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170924080144 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_D83AEDDD9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__application_event AS SELECT id, organisation_id, application_event_type, deleted_at, occurred_at_date_time FROM application_event');
        $this->addSql('DROP TABLE application_event');
        $this->addSql('CREATE TABLE application_event (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, application_event_type INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, occurred_at_date_time DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_D83AEDDD9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO application_event (id, organisation_id, application_event_type, deleted_at, occurred_at_date_time) SELECT id, organisation_id, application_event_type, deleted_at, occurred_at_date_time FROM __temp__application_event');
        $this->addSql('DROP TABLE __temp__application_event');
        $this->addSql('CREATE INDEX IDX_D83AEDDD9E6B1585 ON application_event (organisation_id)');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C82CDCED');
        $this->addSql('DROP INDEX IDX_3BAE0AA7217BBB47');
        $this->addSql('DROP INDEX IDX_3BAE0AA77597D3FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag, deleted_at, is_confirmed, is_confirmed_date_time FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, is_confirmed BOOLEAN DEFAULT \'0\' NOT NULL, is_confirmed_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_3BAE0AA77597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7C82CDCED FOREIGN KEY (event_line_id) REFERENCES event_line (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event (id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag, deleted_at, is_confirmed, is_confirmed_date_time) SELECT id, member_id, person_id, event_line_id, start_date_time, end_date_time, trade_tag, deleted_at, is_confirmed, is_confirmed_date_time FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('DROP INDEX IDX_CEDFF85D9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line AS SELECT id, organisation_id, name, description, display_order, deleted_at FROM event_line');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, display_order INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_CEDFF85D9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_line (id, organisation_id, name, description, display_order, deleted_at) SELECT id, organisation_id, name, description, display_order, deleted_at FROM __temp__event_line');
        $this->addSql('DROP TABLE __temp__event_line');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('DROP INDEX IDX_A44E1BE07B7EBE2A');
        $this->addSql('DROP INDEX IDX_A44E1BE0C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line_generation AS SELECT id, event_line_id, created_by_person_id, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at, created_at_date_time FROM event_line_generation');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, created_by_person_id INTEGER DEFAULT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL COLLATE BINARY, distribution_output_json CLOB NOT NULL COLLATE BINARY, generation_result_json CLOB NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, created_at_date_time DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A44E1BE0C82CDCED FOREIGN KEY (event_line_id) REFERENCES event_line (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A44E1BE07B7EBE2A FOREIGN KEY (created_by_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_line_generation (id, event_line_id, created_by_person_id, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at, created_at_date_time) SELECT id, event_line_id, created_by_person_id, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at, created_at_date_time FROM __temp__event_line_generation');
        $this->addSql('DROP TABLE __temp__event_line_generation');
        $this->addSql('CREATE INDEX IDX_A44E1BE07B7EBE2A ON event_line_generation (created_by_person_id)');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('DROP INDEX IDX_68CD36123D1B60F4');
        $this->addSql('DROP INDEX IDX_68CD361269F7084D');
        $this->addSql('DROP INDEX IDX_68CD3612987D2660');
        $this->addSql('DROP INDEX IDX_68CD3612CC914ED9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, status, deleted_at, description, open_date_time, close_date_time, create_date_time FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, description CLOB DEFAULT NULL COLLATE BINARY, open_date_time DATETIME DEFAULT NULL, close_date_time DATETIME DEFAULT NULL, create_date_time DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_68CD3612CC914ED9 FOREIGN KEY (offered_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612987D2660 FOREIGN KEY (offered_by_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD361269F7084D FOREIGN KEY (offered_to_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD36123D1B60F4 FOREIGN KEY (offered_to_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer (id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, status, deleted_at, description, open_date_time, close_date_time, create_date_time) SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, status, deleted_at, description, open_date_time, close_date_time, create_date_time FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('DROP INDEX IDX_385331971F7E88B');
        $this->addSql('DROP INDEX IDX_3853319152A9D3E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_entry AS SELECT id, event_offer_id, event_id, deleted_at FROM event_offer_entry');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_3853319152A9D3E FOREIGN KEY (event_offer_id) REFERENCES event_offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_385331971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer_entry (id, event_offer_id, event_id, deleted_at) SELECT id, event_offer_id, event_id, deleted_at FROM __temp__event_offer_entry');
        $this->addSql('DROP TABLE __temp__event_offer_entry');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C17ECF0EB');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, event_id, changed_by_person_id, deleted_at, changed_at_date_time, event_change_type, before_event_json, after_event_json FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, event_id INTEGER DEFAULT NULL, changed_by_person_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, changed_at_date_time DATETIME NOT NULL, event_change_type INTEGER NOT NULL, before_event_json CLOB NOT NULL COLLATE BINARY, after_event_json CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_4FDF0D2C17ECF0EB FOREIGN KEY (changed_by_person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_past (id, event_id, changed_by_person_id, deleted_at, changed_at_date_time, event_change_type, before_event_json, after_event_json) SELECT id, event_id, changed_by_person_id, deleted_at, changed_at_date_time, event_change_type, before_event_json, after_event_json FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C17ECF0EB ON event_past (changed_by_person_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('DROP INDEX UNIQ_E2D1DEA217BBB47');
        $this->addSql('DROP INDEX UNIQ_E2D1DEAE7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__frontend_user AS SELECT id, person_id, email, password_hash, reset_hash, is_active, registration_date, deleted_at, agb_accepted FROM frontend_user');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, email CLOB NOT NULL COLLATE BINARY, password_hash CLOB NOT NULL COLLATE BINARY, reset_hash CLOB NOT NULL COLLATE BINARY, is_active BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_E2D1DEA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO frontend_user (id, person_id, email, password_hash, reset_hash, is_active, registration_date, deleted_at, agb_accepted) SELECT id, person_id, email, password_hash, reset_hash, is_active, registration_date, deleted_at, agb_accepted FROM __temp__frontend_user');
        $this->addSql('DROP TABLE __temp__frontend_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEA217BBB47 ON frontend_user (person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('DROP INDEX IDX_906517449E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice AS SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description, deleted_at FROM invoice');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL COLLATE BINARY, street CLOB DEFAULT NULL COLLATE BINARY, street_nr CLOB DEFAULT NULL COLLATE BINARY, address_line CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_906517449E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO invoice (id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description, deleted_at) SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, street, street_nr, address_line, postal_code, city, country, name, description, deleted_at FROM __temp__invoice');
        $this->addSql('DROP TABLE __temp__invoice');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('DROP INDEX IDX_70E4FA789E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, organisation_id, street, street_nr, address_line, postal_code, city, country, phone, email, webpage, description, name, deleted_at, has_been_invited, invitation_hash FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, street CLOB DEFAULT NULL COLLATE BINARY, street_nr CLOB DEFAULT NULL COLLATE BINARY, address_line CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, phone CLOB DEFAULT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, webpage CLOB DEFAULT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, has_been_invited BOOLEAN DEFAULT NULL, invitation_hash CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_70E4FA789E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO member (id, organisation_id, street, street_nr, address_line, postal_code, city, country, phone, email, webpage, description, name, deleted_at, has_been_invited, invitation_hash) SELECT id, organisation_id, street, street_nr, address_line, postal_code, city, country, phone, email, webpage, description, name, deleted_at, has_been_invited, invitation_hash FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE INDEX IDX_70E4FA789E6B1585 ON member (organisation_id)');
        $this->addSql('DROP INDEX IDX_B6C70B6B217BBB47');
        $this->addSql('DROP INDEX IDX_B6C70B6B9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__organisation_person AS SELECT organisation_id, person_id FROM organisation_person');
        $this->addSql('DROP TABLE organisation_person');
        $this->addSql('CREATE TABLE organisation_person (organisation_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(organisation_id, person_id), CONSTRAINT FK_B6C70B6B9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6C70B6B217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organisation_person (organisation_id, person_id) SELECT organisation_id, person_id FROM __temp__organisation_person');
        $this->addSql('DROP TABLE __temp__organisation_person');
        $this->addSql('CREATE INDEX IDX_B6C70B6B217BBB47 ON organisation_person (person_id)');
        $this->addSql('CREATE INDEX IDX_B6C70B6B9E6B1585 ON organisation_person (organisation_id)');
        $this->addSql('DROP INDEX UNIQ_FCF0CF9B9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__organisation_setting AS SELECT id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, last_confirm_event_email_send, deleted_at, trade_event_days FROM organisation_setting');
        $this->addSql('DROP TABLE organisation_setting');
        $this->addSql('CREATE TABLE organisation_setting (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, receiver_of_remainders_id INTEGER DEFAULT NULL, invite_email_subject CLOB DEFAULT NULL COLLATE BINARY, invite_email_message CLOB DEFAULT NULL COLLATE BINARY, must_confirm_event_before_days INTEGER NOT NULL, can_confirm_event_before_days INTEGER DEFAULT NULL, send_confirm_event_email_days INTEGER DEFAULT NULL, last_confirm_event_email_send DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, trade_event_days INTEGER DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_FCF0CF9B9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FCF0CF9BFDFCFDB5 FOREIGN KEY (receiver_of_remainders_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organisation_setting (id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, last_confirm_event_email_send, deleted_at, trade_event_days) SELECT id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, last_confirm_event_email_send, deleted_at, trade_event_days FROM __temp__organisation_setting');
        $this->addSql('DROP TABLE __temp__organisation_setting');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCF0CF9B9E6B1585 ON organisation_setting (organisation_id)');
        $this->addSql('CREATE INDEX IDX_FCF0CF9BFDFCFDB5 ON organisation_setting (receiver_of_remainders_id)');
        $this->addSql('DROP INDEX IDX_673E61767597D3FE');
        $this->addSql('DROP INDEX IDX_673E6176217BBB47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_members AS SELECT person_id, member_id FROM person_members');
        $this->addSql('DROP TABLE person_members');
        $this->addSql('CREATE TABLE person_members (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id), CONSTRAINT FK_673E6176217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_673E61767597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person_members (person_id, member_id) SELECT person_id, member_id FROM __temp__person_members');
        $this->addSql('DROP TABLE __temp__person_members');
        $this->addSql('CREATE INDEX IDX_673E61767597D3FE ON person_members (member_id)');
        $this->addSql('CREATE INDEX IDX_673E6176217BBB47 ON person_members (person_id)');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content, deleted_at FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL COLLATE BINARY, content CLOB NOT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_9F74B8987887A021 FOREIGN KEY (frontend_user_id) REFERENCES frontend_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content, deleted_at) SELECT id, frontend_user_id, "key", content, deleted_at FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_D83AEDDD9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__application_event AS SELECT id, organisation_id, application_event_type, occurred_at_date_time, deleted_at FROM application_event');
        $this->addSql('DROP TABLE application_event');
        $this->addSql('CREATE TABLE application_event (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, application_event_type INTEGER NOT NULL, occurred_at_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO application_event (id, organisation_id, application_event_type, occurred_at_date_time, deleted_at) SELECT id, organisation_id, application_event_type, occurred_at_date_time, deleted_at FROM __temp__application_event');
        $this->addSql('DROP TABLE __temp__application_event');
        $this->addSql('CREATE INDEX IDX_D83AEDDD9E6B1585 ON application_event (organisation_id)');
        $this->addSql('DROP INDEX IDX_3BAE0AA77597D3FE');
        $this->addSql('DROP INDEX IDX_3BAE0AA7217BBB47');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C82CDCED');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, member_id, person_id, event_line_id, start_date_time, is_confirmed, is_confirmed_date_time, end_date_time, trade_tag, deleted_at FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, is_confirmed BOOLEAN DEFAULT \'0\' NOT NULL, is_confirmed_date_time DATETIME DEFAULT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event (id, member_id, person_id, event_line_id, start_date_time, is_confirmed, is_confirmed_date_time, end_date_time, trade_tag, deleted_at) SELECT id, member_id, person_id, event_line_id, start_date_time, is_confirmed, is_confirmed_date_time, end_date_time, trade_tag, deleted_at FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('DROP INDEX IDX_CEDFF85D9E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line AS SELECT id, organisation_id, display_order, deleted_at, name, description FROM event_line');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, display_order INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_line (id, organisation_id, display_order, deleted_at, name, description) SELECT id, organisation_id, display_order, deleted_at, name, description FROM __temp__event_line');
        $this->addSql('DROP TABLE __temp__event_line');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('DROP INDEX IDX_A44E1BE0C82CDCED');
        $this->addSql('DROP INDEX IDX_A44E1BE07B7EBE2A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_line_generation AS SELECT id, event_line_id, created_by_person_id, created_at_date_time, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at FROM event_line_generation');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, created_by_person_id INTEGER DEFAULT NULL, created_at_date_time DATETIME NOT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL, distribution_output_json CLOB NOT NULL, generation_result_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_line_generation (id, event_line_id, created_by_person_id, created_at_date_time, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at) SELECT id, event_line_id, created_by_person_id, created_at_date_time, distribution_type, distribution_configuration_json, distribution_output_json, generation_result_json, deleted_at FROM __temp__event_line_generation');
        $this->addSql('DROP TABLE __temp__event_line_generation');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('CREATE INDEX IDX_A44E1BE07B7EBE2A ON event_line_generation (created_by_person_id)');
        $this->addSql('DROP INDEX IDX_68CD3612CC914ED9');
        $this->addSql('DROP INDEX IDX_68CD3612987D2660');
        $this->addSql('DROP INDEX IDX_68CD361269F7084D');
        $this->addSql('DROP INDEX IDX_68CD36123D1B60F4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, create_date_time, open_date_time, close_date_time, status, deleted_at FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, description CLOB DEFAULT NULL, create_date_time DATETIME NOT NULL, open_date_time DATETIME DEFAULT NULL, close_date_time DATETIME DEFAULT NULL, status INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_offer (id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, create_date_time, open_date_time, close_date_time, status, deleted_at) SELECT id, offered_by_member_id, offered_by_person_id, offered_to_member_id, offered_to_person_id, description, create_date_time, open_date_time, close_date_time, status, deleted_at FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('DROP INDEX IDX_3853319152A9D3E');
        $this->addSql('DROP INDEX IDX_385331971F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_entry AS SELECT id, event_offer_id, event_id, deleted_at FROM event_offer_entry');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_offer_entry (id, event_offer_id, event_id, deleted_at) SELECT id, event_offer_id, event_id, deleted_at FROM __temp__event_offer_entry');
        $this->addSql('DROP TABLE __temp__event_offer_entry');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C17ECF0EB');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, changed_by_person_id, event_id, changed_at_date_time, event_change_type, before_event_json, after_event_json, deleted_at FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, changed_by_person_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, changed_at_date_time DATETIME NOT NULL, event_change_type INTEGER NOT NULL, before_event_json CLOB NOT NULL, after_event_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO event_past (id, changed_by_person_id, event_id, changed_at_date_time, event_change_type, before_event_json, after_event_json, deleted_at) SELECT id, changed_by_person_id, event_id, changed_at_date_time, event_change_type, before_event_json, after_event_json, deleted_at FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C17ECF0EB ON event_past (changed_by_person_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('DROP INDEX UNIQ_E2D1DEAE7927C74');
        $this->addSql('DROP INDEX UNIQ_E2D1DEA217BBB47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__frontend_user AS SELECT id, person_id, deleted_at, email, password_hash, reset_hash, is_active, registration_date, agb_accepted FROM frontend_user');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_active BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO frontend_user (id, person_id, deleted_at, email, password_hash, reset_hash, is_active, registration_date, agb_accepted) SELECT id, person_id, deleted_at, email, password_hash, reset_hash, is_active, registration_date, agb_accepted FROM __temp__frontend_user');
        $this->addSql('DROP TABLE __temp__frontend_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEA217BBB47 ON frontend_user (person_id)');
        $this->addSql('DROP INDEX IDX_906517449E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice AS SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, deleted_at, street, street_nr, address_line, postal_code, city, country, name, description FROM invoice');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO invoice (id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, deleted_at, street, street_nr, address_line, postal_code, city, country, name, description) SELECT id, organisation_id, invoice_date_time, payment_date_time, payment_status, invoice_type, invoice_data_json, deleted_at, street, street_nr, address_line, postal_code, city, country, name, description FROM __temp__invoice');
        $this->addSql('DROP TABLE __temp__invoice');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('DROP INDEX IDX_70E4FA789E6B1585');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, organisation_id, has_been_invited, invitation_hash, deleted_at, name, description, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, has_been_invited BOOLEAN DEFAULT NULL, invitation_hash CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO member (id, organisation_id, has_been_invited, invitation_hash, deleted_at, name, description, street, street_nr, address_line, postal_code, city, country, phone, email, webpage) SELECT id, organisation_id, has_been_invited, invitation_hash, deleted_at, name, description, street, street_nr, address_line, postal_code, city, country, phone, email, webpage FROM __temp__member');
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
        $this->addSql('DROP INDEX UNIQ_FCF0CF9B9E6B1585');
        $this->addSql('DROP INDEX IDX_FCF0CF9BFDFCFDB5');
        $this->addSql('CREATE TEMPORARY TABLE __temp__organisation_setting AS SELECT id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, trade_event_days, last_confirm_event_email_send, deleted_at FROM organisation_setting');
        $this->addSql('DROP TABLE organisation_setting');
        $this->addSql('CREATE TABLE organisation_setting (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invite_email_subject CLOB DEFAULT NULL, invite_email_message CLOB DEFAULT NULL, must_confirm_event_before_days INTEGER NOT NULL, can_confirm_event_before_days INTEGER DEFAULT NULL, send_confirm_event_email_days INTEGER DEFAULT NULL, trade_event_days INTEGER DEFAULT NULL, last_confirm_event_email_send DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO organisation_setting (id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, trade_event_days, last_confirm_event_email_send, deleted_at) SELECT id, organisation_id, invite_email_subject, invite_email_message, must_confirm_event_before_days, can_confirm_event_before_days, send_confirm_event_email_days, trade_event_days, last_confirm_event_email_send, deleted_at FROM __temp__organisation_setting');
        $this->addSql('DROP TABLE __temp__organisation_setting');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCF0CF9B9E6B1585 ON organisation_setting (organisation_id)');
        $this->addSql('DROP INDEX IDX_673E6176217BBB47');
        $this->addSql('DROP INDEX IDX_673E61767597D3FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_members AS SELECT person_id, member_id FROM person_members');
        $this->addSql('DROP TABLE person_members');
        $this->addSql('CREATE TABLE person_members (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id))');
        $this->addSql('INSERT INTO person_members (person_id, member_id) SELECT person_id, member_id FROM __temp__person_members');
        $this->addSql('DROP TABLE __temp__person_members');
        $this->addSql('CREATE INDEX IDX_673E6176217BBB47 ON person_members (person_id)');
        $this->addSql('CREATE INDEX IDX_673E61767597D3FE ON person_members (member_id)');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content, deleted_at FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL, content CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content, deleted_at) SELECT id, frontend_user_id, "key", content, deleted_at FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }
}
