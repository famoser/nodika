<?php

declare(strict_types=1);

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181021114128 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE clinic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, invitation_identifier CLOB DEFAULT NULL, last_invitation DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_administrator BOOLEAN NOT NULL, receives_administrator_mail BOOLEAN NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME DEFAULT NULL, last_login_date DATETIME DEFAULT NULL, invitation_identifier CLOB DEFAULT NULL, last_invitation DATETIME DEFAULT NULL, job_title CLOB DEFAULT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AE7927C74 ON doctor (email)');
        $this->addSql('CREATE TABLE doctor_clinics (doctor_id INTEGER NOT NULL, clinic_id INTEGER NOT NULL, PRIMARY KEY(doctor_id, clinic_id))');
        $this->addSql('CREATE INDEX IDX_44A858CB87F4FB17 ON doctor_clinics (doctor_id)');
        $this->addSql('CREATE INDEX IDX_44A858CBCC22AD4 ON doctor_clinics (clinic_id)');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, confirmed_by_doctor_id INTEGER DEFAULT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA718D94A2 ON event (confirmed_by_doctor_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CC22AD4 ON event (clinic_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA787F4FB17 ON event (doctor_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA71BDD81B ON event (generated_by_id)');
        $this->addSql('CREATE TABLE event_event_tags (event_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_id, event_tag_id))');
        $this->addSql('CREATE INDEX IDX_289901A271F7E88B ON event_event_tags (event_id)');
        $this->addSql('CREATE INDEX IDX_289901A2884B1443 ON event_event_tags (event_tag_id)');
        $this->addSql('CREATE TABLE event_tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, color INTEGER NOT NULL, tag_type INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE event_generation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, start_cron_expression CLOB NOT NULL, end_cron_expression CLOB NOT NULL, differentiate_by_event_type BOOLEAN NOT NULL, weekday_weight NUMERIC(10, 0) NOT NULL, saturday_weight NUMERIC(10, 0) NOT NULL, sunday_weight NUMERIC(10, 0) NOT NULL, holiday_weight NUMERIC(10, 0) NOT NULL, mind_previous_events BOOLEAN NOT NULL, applied BOOLEAN NOT NULL, step INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_8BC8B514B03A8386 ON event_generation (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8BC8B514EE85B337 ON event_generation (last_changed_by_id)');
        $this->addSql('CREATE TABLE event_generation_conflicting_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id))');
        $this->addSql('CREATE INDEX IDX_8D07A4AE7163DE68 ON event_generation_conflicting_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_8D07A4AE884B1443 ON event_generation_conflicting_event_tags (event_tag_id)');
        $this->addSql('CREATE TABLE event_generation_assign_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id))');
        $this->addSql('CREATE INDEX IDX_3B6D33B77163DE68 ON event_generation_assign_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_3B6D33B7884B1443 ON event_generation_assign_event_tags (event_tag_id)');
        $this->addSql('CREATE TABLE event_offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, receiver_id INTEGER DEFAULT NULL, receiver_clinic_id INTEGER DEFAULT NULL, sender_id INTEGER DEFAULT NULL, sender_clinic_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, message CLOB DEFAULT NULL, is_resolved BOOLEAN NOT NULL, receiver_authorization_status INTEGER NOT NULL, sender_authorization_status INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_68CD3612CD53EDB6 ON event_offer (receiver_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612C12D89BC ON event_offer (receiver_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612F624B39D ON event_offer (sender_id)');
        $this->addSql('CREATE INDEX IDX_68CD361256CBCE22 ON event_offer (sender_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612B03A8386 ON event_offer (created_by_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612EE85B337 ON event_offer (last_changed_by_id)');
        $this->addSql('CREATE TABLE event_offer_events (event_offer_id INTEGER NOT NULL, event_id INTEGER NOT NULL, PRIMARY KEY(event_offer_id, event_id))');
        $this->addSql('CREATE INDEX IDX_67C12A18152A9D3E ON event_offer_events (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_67C12A1871F7E88B ON event_offer_events (event_id)');
        $this->addSql('CREATE TABLE event_generation_target_doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, doctor_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_7ED0809D87F4FB17 ON event_generation_target_doctor (doctor_id)');
        $this->addSql('CREATE INDEX IDX_7ED0809D7163DE68 ON event_generation_target_doctor (event_generation_id)');
        $this->addSql('CREATE TABLE setting (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, doctors_can_edit_self BOOLEAN NOT NULL, doctors_can_edit_clinics BOOLEAN NOT NULL, can_confirm_days_advance INTEGER NOT NULL, must_confirm_days_advance INTEGER NOT NULL, send_remainder_days_interval INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_9F74B898B03A8386 ON setting (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9F74B898EE85B337 ON setting (last_changed_by_id)');
        $this->addSql('CREATE TABLE event_generation_date_exception (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_generation_id INTEGER DEFAULT NULL, event_type INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_A86F737E7163DE68 ON event_generation_date_exception (event_generation_id)');
        $this->addSql('CREATE TABLE event_generation_target_clinic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, clinic_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_66938B43CC22AD4 ON event_generation_target_clinic (clinic_id)');
        $this->addSql('CREATE INDEX IDX_66938B437163DE68 ON event_generation_target_clinic (event_generation_id)');
        $this->addSql('CREATE TABLE email (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, receiver CLOB NOT NULL, identifier CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, carbon_copy CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME NOT NULL, visited_date_time DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE event_past (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, confirmed_by_doctor_id INTEGER DEFAULT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, event_change_type INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CB03A8386 ON event_past (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C18D94A2 ON event_past (confirmed_by_doctor_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CCC22AD4 ON event_past (clinic_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C87F4FB17 ON event_past (doctor_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C1BDD81B ON event_past (generated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE clinic');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('DROP TABLE doctor_clinics');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_event_tags');
        $this->addSql('DROP TABLE event_tag');
        $this->addSql('DROP TABLE event_generation');
        $this->addSql('DROP TABLE event_generation_conflicting_event_tags');
        $this->addSql('DROP TABLE event_generation_assign_event_tags');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('DROP TABLE event_offer_events');
        $this->addSql('DROP TABLE event_generation_target_doctor');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE event_generation_date_exception');
        $this->addSql('DROP TABLE event_generation_target_clinic');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE event_past');
    }
}
