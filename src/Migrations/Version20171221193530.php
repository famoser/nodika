<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171221193530 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE organisation (id INTEGER NOT NULL, is_active BOOLEAN NOT NULL, active_end DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation_person (organisation_id INTEGER NOT NULL, person_id INTEGER NOT NULL, PRIMARY KEY(organisation_id, person_id))');
        $this->addSql('CREATE INDEX IDX_B6C70B6B9E6B1585 ON organisation_person (organisation_id)');
        $this->addSql('CREATE INDEX IDX_B6C70B6B217BBB47 ON organisation_person (person_id)');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, display_order INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEDFF85D9E6B1585 ON event_line (organisation_id)');
        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, person_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, is_confirmed BOOLEAN DEFAULT \'0\' NOT NULL, is_confirmed_date_time DATETIME DEFAULT NULL, end_date_time DATETIME NOT NULL, trade_tag INTEGER NOT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7217BBB47 ON event (person_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA71BDD81B ON event (generated_by_id)');
        $this->addSql('CREATE TABLE event_line_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, created_by_person_id INTEGER DEFAULT NULL, created_at_date_time DATETIME NOT NULL, applied BOOLEAN DEFAULT \'0\' NOT NULL, distribution_type INTEGER NOT NULL, distribution_configuration_json CLOB NOT NULL, distribution_output_json CLOB NOT NULL, generation_result_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A44E1BE0C82CDCED ON event_line_generation (event_line_id)');
        $this->addSql('CREATE INDEX IDX_A44E1BE07B7EBE2A ON event_line_generation (created_by_person_id)');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invitation_date_time DATETIME DEFAULT NULL, invitation_hash CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70E4FA789E6B1585 ON member (organisation_id)');
        $this->addSql('CREATE TABLE person (id INTEGER NOT NULL, invitation_date_time DATETIME DEFAULT NULL, invitation_hash CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person_members (person_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(person_id, member_id))');
        $this->addSql('CREATE INDEX IDX_673E6176217BBB47 ON person_members (person_id)');
        $this->addSql('CREATE INDEX IDX_673E61767597D3FE ON person_members (member_id)');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, offered_by_member_id INTEGER DEFAULT NULL, offered_by_person_id INTEGER DEFAULT NULL, offered_to_member_id INTEGER DEFAULT NULL, offered_to_person_id INTEGER DEFAULT NULL, description CLOB DEFAULT NULL, create_date_time DATETIME NOT NULL, open_date_time DATETIME DEFAULT NULL, close_date_time DATETIME DEFAULT NULL, status INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68CD3612CC914ED9 ON event_offer (offered_by_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612987D2660 ON event_offer (offered_by_person_id)');
        $this->addSql('CREATE INDEX IDX_68CD361269F7084D ON event_offer (offered_to_member_id)');
        $this->addSql('CREATE INDEX IDX_68CD36123D1B60F4 ON event_offer (offered_to_person_id)');
        $this->addSql('CREATE TABLE admin_user (id INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_active BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD8A54A9E7927C74 ON admin_user (email)');
        $this->addSql('CREATE TABLE application_event (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, application_event_type INTEGER NOT NULL, occurred_at_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D83AEDDD9E6B1585 ON application_event (organisation_id)');
        $this->addSql('CREATE TABLE email (id INTEGER NOT NULL, receiver CLOB NOT NULL, identifier CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, carbon_copy CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME NOT NULL, visited_date_time DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, changed_by_person_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, changed_at_date_time DATETIME NOT NULL, event_change_type INTEGER NOT NULL, before_event_json CLOB NOT NULL, after_event_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C17ECF0EB ON event_past (changed_by_person_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, person_id INTEGER DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_active BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEA217BBB47 ON frontend_user (person_id)');
        $this->addSql('CREATE TABLE invoice (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, invoice_date_time DATETIME NOT NULL, payment_date_time DATETIME NOT NULL, payment_status INTEGER NOT NULL, invoice_type INTEGER NOT NULL, invoice_data_json CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_906517449E6B1585 ON invoice (organisation_id)');
        $this->addSql('CREATE TABLE newsletter (id INTEGER NOT NULL, choice INTEGER NOT NULL, message CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation_setting (id INTEGER NOT NULL, organisation_id INTEGER DEFAULT NULL, receiver_of_remainders_id INTEGER DEFAULT NULL, member_invite_email_subject CLOB DEFAULT NULL, member_invite_email_message CLOB DEFAULT NULL, person_invite_email_subject CLOB DEFAULT NULL, person_invite_email_message CLOB DEFAULT NULL, must_confirm_event_before_days INTEGER NOT NULL, can_confirm_event_before_days INTEGER DEFAULT NULL, send_confirm_event_email_days INTEGER DEFAULT NULL, trade_event_days INTEGER DEFAULT NULL, last_confirm_event_email_send DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCF0CF9B9E6B1585 ON organisation_setting (organisation_id)');
        $this->addSql('CREATE INDEX IDX_FCF0CF9BFDFCFDB5 ON organisation_setting (receiver_of_remainders_id)');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL, content CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE organisation_person');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_line_generation');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_members');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE application_event');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE organisation_setting');
        $this->addSql('DROP TABLE setting');
    }
}
