<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180414144317 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE event (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, frontend_user_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, trade_tag INTEGER NOT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77597D3FE ON event (member_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77887A021 ON event (frontend_user_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C82CDCED ON event (event_line_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA71BDD81B ON event (generated_by_id)');
        $this->addSql('CREATE TABLE event_line (id INTEGER NOT NULL, display_order INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, is_administrator BOOLEAN NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, invitation_identifier CLOB DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('CREATE TABLE person_members (frontend_user_id INTEGER NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY(frontend_user_id, member_id))');
        $this->addSql('CREATE INDEX IDX_673E61767887A021 ON person_members (frontend_user_id)');
        $this->addSql('CREATE INDEX IDX_673E61767597D3FE ON person_members (member_id)');
        $this->addSql('CREATE TABLE event_generation (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, minimal_gap_between_events NUMERIC(10, 0) NOT NULL, start_cron_expression CLOB NOT NULL, end_cron_expression CLOB NOT NULL, differentiate_by_event_type BOOLEAN NOT NULL, weekday_weight NUMERIC(10, 0) NOT NULL, saturday_weight NUMERIC(10, 0) NOT NULL, sunday_weight NUMERIC(10, 0) NOT NULL, holiday_weight NUMERIC(10, 0) NOT NULL, mind_previous_events BOOLEAN NOT NULL, status INTEGER NOT NULL, step INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8BC8B514C82CDCED ON event_generation (event_line_id)');
        $this->addSql('CREATE INDEX IDX_8BC8B514B03A8386 ON event_generation (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8BC8B514EE85B337 ON event_generation (last_changed_by_id)');
        $this->addSql('CREATE TABLE member (id INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, invitation_identifier CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_offer (id INTEGER NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, message CLOB DEFAULT NULL, status INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68CD3612B03A8386 ON event_offer (created_by_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612EE85B337 ON event_offer (last_changed_by_id)');
        $this->addSql('CREATE TABLE event_offer_entry (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, event_id INTEGER DEFAULT NULL, target_frontend_user_id INTEGER DEFAULT NULL, target_member_id INTEGER DEFAULT NULL, event_offer_authorization_id INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3853319152A9D3E ON event_offer_entry (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_385331971F7E88B ON event_offer_entry (event_id)');
        $this->addSql('CREATE INDEX IDX_3853319AC7E9E21 ON event_offer_entry (target_frontend_user_id)');
        $this->addSql('CREATE INDEX IDX_38533194123A1D2 ON event_offer_entry (target_member_id)');
        $this->addSql('CREATE INDEX IDX_38533192058366E ON event_offer_entry (event_offer_authorization_id)');
        $this->addSql('CREATE TABLE event_generation_date_exception (id INTEGER NOT NULL, event_generation_id INTEGER DEFAULT NULL, event_type INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A86F737E7163DE68 ON event_generation_date_exception (event_generation_id)');
        $this->addSql('CREATE TABLE event_offer_authorization (id INTEGER NOT NULL, event_offer_id INTEGER DEFAULT NULL, signed_by_id INTEGER DEFAULT NULL, signature_status INTEGER NOT NULL, decision_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D161AF71152A9D3E ON event_offer_authorization (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_D161AF71D2EDD3FB ON event_offer_authorization (signed_by_id)');
        $this->addSql('CREATE TABLE email (id INTEGER NOT NULL, receiver CLOB NOT NULL, identifier CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, carbon_copy CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME NOT NULL, visited_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_generation_member (id INTEGER NOT NULL, member_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B37CB5C07597D3FE ON event_generation_member (member_id)');
        $this->addSql('CREATE INDEX IDX_B37CB5C07163DE68 ON event_generation_member (event_generation_id)');
        $this->addSql('CREATE TABLE event_generation_conflict_avoid (id INTEGER NOT NULL, event_line_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4EBB93D8C82CDCED ON event_generation_conflict_avoid (event_line_id)');
        $this->addSql('CREATE INDEX IDX_4EBB93D87163DE68 ON event_generation_conflict_avoid (event_generation_id)');
        $this->addSql('CREATE TABLE event_past (id INTEGER NOT NULL, event_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, member_id INTEGER DEFAULT NULL, frontend_user_id INTEGER DEFAULT NULL, event_line_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, event_change_type INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, trade_tag INTEGER NOT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CB03A8386 ON event_past (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CEE85B337 ON event_past (last_changed_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C7597D3FE ON event_past (member_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C7887A021 ON event_past (frontend_user_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CC82CDCED ON event_past (event_line_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C1BDD81B ON event_past (generated_by_id)');
        $this->addSql('CREATE TABLE settings (id INTEGER NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, support_mail CLOB NOT NULL, organisation_name CLOB NOT NULL, member_name CLOB NOT NULL, frontend_user_name CLOB NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E545A0C5B03A8386 ON settings (created_by_id)');
        $this->addSql('CREATE INDEX IDX_E545A0C5EE85B337 ON settings (last_changed_by_id)');
        $this->addSql('CREATE TABLE admin_user (id INTEGER NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD8A54A9E7927C74 ON admin_user (email)');
        $this->addSql('CREATE TABLE event_generation_frontend_user (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_663789AD7887A021 ON event_generation_frontend_user (frontend_user_id)');
        $this->addSql('CREATE INDEX IDX_663789AD7163DE68 ON event_generation_frontend_user (event_generation_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_line');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('DROP TABLE person_members');
        $this->addSql('DROP TABLE event_generation');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('DROP TABLE event_offer_entry');
        $this->addSql('DROP TABLE event_generation_date_exception');
        $this->addSql('DROP TABLE event_offer_authorization');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE event_generation_member');
        $this->addSql('DROP TABLE event_generation_conflict_avoid');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE event_generation_frontend_user');
    }
}
