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
final class Version20180829131339 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_66938B43CC22AD4');
        $this->addSql('DROP INDEX IDX_66938B437163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_target_clinic AS SELECT id, clinic_id, event_generation_id, weight, generation_score, default_order FROM event_generation_target_clinic');
        $this->addSql('DROP TABLE event_generation_target_clinic');
        $this->addSql('CREATE TABLE event_generation_target_clinic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, clinic_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL, CONSTRAINT FK_66938B43CC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_66938B437163DE68 FOREIGN KEY (event_generation_id) REFERENCES event_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation_target_clinic (id, clinic_id, event_generation_id, weight, generation_score, default_order) SELECT id, clinic_id, event_generation_id, weight, generation_score, default_order FROM __temp__event_generation_target_clinic');
        $this->addSql('DROP TABLE __temp__event_generation_target_clinic');
        $this->addSql('CREATE INDEX IDX_66938B43CC22AD4 ON event_generation_target_clinic (clinic_id)');
        $this->addSql('CREATE INDEX IDX_66938B437163DE68 ON event_generation_target_clinic (event_generation_id)');
        $this->addSql('DROP INDEX IDX_68CD361256CBCE22');
        $this->addSql('DROP INDEX IDX_68CD3612F624B39D');
        $this->addSql('DROP INDEX IDX_68CD3612C12D89BC');
        $this->addSql('DROP INDEX IDX_68CD3612CD53EDB6');
        $this->addSql('DROP INDEX IDX_68CD3612B03A8386');
        $this->addSql('DROP INDEX IDX_68CD3612EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, created_by_id, last_changed_by_id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, message, created_at, last_changed_at, receiver_authorization_status, is_resolved, sender_authorization_status FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, receiver_id INTEGER DEFAULT NULL, receiver_clinic_id INTEGER DEFAULT NULL, sender_id INTEGER DEFAULT NULL, sender_clinic_id INTEGER DEFAULT NULL, message CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, receiver_authorization_status INTEGER NOT NULL, is_resolved BOOLEAN NOT NULL, sender_authorization_status INTEGER NOT NULL, CONSTRAINT FK_68CD3612CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612C12D89BC FOREIGN KEY (receiver_clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612F624B39D FOREIGN KEY (sender_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD361256CBCE22 FOREIGN KEY (sender_clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612B03A8386 FOREIGN KEY (created_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68CD3612EE85B337 FOREIGN KEY (last_changed_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer (id, created_by_id, last_changed_by_id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, message, created_at, last_changed_at, receiver_authorization_status, is_resolved, sender_authorization_status) SELECT id, created_by_id, last_changed_by_id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, message, created_at, last_changed_at, receiver_authorization_status, is_resolved, sender_authorization_status FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD361256CBCE22 ON event_offer (sender_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612F624B39D ON event_offer (sender_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612C12D89BC ON event_offer (receiver_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612CD53EDB6 ON event_offer (receiver_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612B03A8386 ON event_offer (created_by_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612EE85B337 ON event_offer (last_changed_by_id)');
        $this->addSql('DROP INDEX IDX_67C12A1871F7E88B');
        $this->addSql('DROP INDEX IDX_67C12A18152A9D3E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_events AS SELECT event_offer_id, event_id FROM event_offer_events');
        $this->addSql('DROP TABLE event_offer_events');
        $this->addSql('CREATE TABLE event_offer_events (event_offer_id INTEGER NOT NULL, event_id INTEGER NOT NULL, PRIMARY KEY(event_offer_id, event_id), CONSTRAINT FK_67C12A18152A9D3E FOREIGN KEY (event_offer_id) REFERENCES event_offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67C12A1871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_offer_events (event_offer_id, event_id) SELECT event_offer_id, event_id FROM __temp__event_offer_events');
        $this->addSql('DROP TABLE __temp__event_offer_events');
        $this->addSql('CREATE INDEX IDX_67C12A1871F7E88B ON event_offer_events (event_id)');
        $this->addSql('CREATE INDEX IDX_67C12A18152A9D3E ON event_offer_events (event_offer_id)');
        $this->addSql('DROP INDEX IDX_A86F737E7163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_date_exception AS SELECT id, event_generation_id, event_type, start_date_time, end_date_time FROM event_generation_date_exception');
        $this->addSql('DROP TABLE event_generation_date_exception');
        $this->addSql('CREATE TABLE event_generation_date_exception (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_generation_id INTEGER DEFAULT NULL, event_type INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, CONSTRAINT FK_A86F737E7163DE68 FOREIGN KEY (event_generation_id) REFERENCES event_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation_date_exception (id, event_generation_id, event_type, start_date_time, end_date_time) SELECT id, event_generation_id, event_type, start_date_time, end_date_time FROM __temp__event_generation_date_exception');
        $this->addSql('DROP TABLE __temp__event_generation_date_exception');
        $this->addSql('CREATE INDEX IDX_A86F737E7163DE68 ON event_generation_date_exception (event_generation_id)');
        $this->addSql('DROP INDEX IDX_8BC8B514B03A8386');
        $this->addSql('DROP INDEX IDX_8BC8B514EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation AS SELECT id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at FROM event_generation');
        $this->addSql('DROP TABLE event_generation');
        $this->addSql('CREATE TABLE event_generation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, minimal_gap_between_events NUMERIC(10, 0) NOT NULL, start_cron_expression CLOB NOT NULL COLLATE BINARY, end_cron_expression CLOB NOT NULL COLLATE BINARY, differentiate_by_event_type BOOLEAN NOT NULL, weekday_weight NUMERIC(10, 0) NOT NULL, saturday_weight NUMERIC(10, 0) NOT NULL, sunday_weight NUMERIC(10, 0) NOT NULL, holiday_weight NUMERIC(10, 0) NOT NULL, mind_previous_events BOOLEAN NOT NULL, step INTEGER NOT NULL, name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, CONSTRAINT FK_8BC8B514B03A8386 FOREIGN KEY (created_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8BC8B514EE85B337 FOREIGN KEY (last_changed_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation (id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at) SELECT id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at FROM __temp__event_generation');
        $this->addSql('DROP TABLE __temp__event_generation');
        $this->addSql('CREATE INDEX IDX_8BC8B514B03A8386 ON event_generation (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8BC8B514EE85B337 ON event_generation (last_changed_by_id)');
        $this->addSql('DROP INDEX IDX_8D07A4AE7163DE68');
        $this->addSql('DROP INDEX IDX_8D07A4AE884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_conflicting_event_tags AS SELECT event_generation_id, event_tag_id FROM event_generation_conflicting_event_tags');
        $this->addSql('DROP TABLE event_generation_conflicting_event_tags');
        $this->addSql('CREATE TABLE event_generation_conflicting_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id), CONSTRAINT FK_8D07A4AE7163DE68 FOREIGN KEY (event_generation_id) REFERENCES event_generation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8D07A4AE884B1443 FOREIGN KEY (event_tag_id) REFERENCES event_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation_conflicting_event_tags (event_generation_id, event_tag_id) SELECT event_generation_id, event_tag_id FROM __temp__event_generation_conflicting_event_tags');
        $this->addSql('DROP TABLE __temp__event_generation_conflicting_event_tags');
        $this->addSql('CREATE INDEX IDX_8D07A4AE7163DE68 ON event_generation_conflicting_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_8D07A4AE884B1443 ON event_generation_conflicting_event_tags (event_tag_id)');
        $this->addSql('DROP INDEX IDX_3B6D33B77163DE68');
        $this->addSql('DROP INDEX IDX_3B6D33B7884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_assign_event_tags AS SELECT event_generation_id, event_tag_id FROM event_generation_assign_event_tags');
        $this->addSql('DROP TABLE event_generation_assign_event_tags');
        $this->addSql('CREATE TABLE event_generation_assign_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id), CONSTRAINT FK_3B6D33B77163DE68 FOREIGN KEY (event_generation_id) REFERENCES event_generation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3B6D33B7884B1443 FOREIGN KEY (event_tag_id) REFERENCES event_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation_assign_event_tags (event_generation_id, event_tag_id) SELECT event_generation_id, event_tag_id FROM __temp__event_generation_assign_event_tags');
        $this->addSql('DROP TABLE __temp__event_generation_assign_event_tags');
        $this->addSql('CREATE INDEX IDX_3B6D33B77163DE68 ON event_generation_assign_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_3B6D33B7884B1443 ON event_generation_assign_event_tags (event_tag_id)');
        $this->addSql('DROP INDEX IDX_9F74B898B03A8386');
        $this->addSql('DROP INDEX IDX_9F74B898EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, support_mail CLOB NOT NULL COLLATE BINARY, organisation_name CLOB NOT NULL COLLATE BINARY, can_confirm_days_advance INTEGER NOT NULL, must_confirm_days_advance INTEGER NOT NULL, send_remainder_days_interval INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, CONSTRAINT FK_9F74B898B03A8386 FOREIGN KEY (created_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9F74B898EE85B337 FOREIGN KEY (last_changed_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setting (id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at) SELECT id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B898B03A8386 ON setting (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9F74B898EE85B337 ON setting (last_changed_by_id)');
        $this->addSql('DROP INDEX IDX_7ED0809D87F4FB17');
        $this->addSql('DROP INDEX IDX_7ED0809D7163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_target_doctor AS SELECT id, doctor_id, event_generation_id, weight, generation_score, default_order FROM event_generation_target_doctor');
        $this->addSql('DROP TABLE event_generation_target_doctor');
        $this->addSql('CREATE TABLE event_generation_target_doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, doctor_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL, CONSTRAINT FK_7ED0809D87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7ED0809D7163DE68 FOREIGN KEY (event_generation_id) REFERENCES event_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_generation_target_doctor (id, doctor_id, event_generation_id, weight, generation_score, default_order) SELECT id, doctor_id, event_generation_id, weight, generation_score, default_order FROM __temp__event_generation_target_doctor');
        $this->addSql('DROP TABLE __temp__event_generation_target_doctor');
        $this->addSql('CREATE INDEX IDX_7ED0809D87F4FB17 ON event_generation_target_doctor (doctor_id)');
        $this->addSql('CREATE INDEX IDX_7ED0809D7163DE68 ON event_generation_target_doctor (event_generation_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('DROP INDEX IDX_4FDF0D2CB03A8386');
        $this->addSql('DROP INDEX IDX_4FDF0D2CEE85B337');
        $this->addSql('DROP INDEX IDX_4FDF0D2C6F45385D');
        $this->addSql('DROP INDEX IDX_4FDF0D2CCC22AD4');
        $this->addSql('DROP INDEX IDX_4FDF0D2C87F4FB17');
        $this->addSql('DROP INDEX IDX_4FDF0D2C1BDD81B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, event_id, created_by_id, last_changed_by_id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, confirmed_by_doctor_id INTEGER DEFAULT NULL, event_change_type INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, CONSTRAINT FK_4FDF0D2C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2CB03A8386 FOREIGN KEY (created_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2CEE85B337 FOREIGN KEY (last_changed_by_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2C18D94A2 FOREIGN KEY (confirmed_by_doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2CCC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2C87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4FDF0D2C1BDD81B FOREIGN KEY (generated_by_id) REFERENCES event_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_past (id, event_id, created_by_id, last_changed_by_id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time) SELECT id, event_id, created_by_id, last_changed_by_id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CB03A8386 ON event_past (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CEE85B337 ON event_past (last_changed_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CCC22AD4 ON event_past (clinic_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C87F4FB17 ON event_past (doctor_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C1BDD81B ON event_past (generated_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C18D94A2 ON event_past (confirmed_by_doctor_id)');
        $this->addSql('DROP INDEX UNIQ_1FC0F36AE7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor AS SELECT id, is_administrator, email, password_hash, reset_hash, is_enabled, invitation_identifier, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at, registration_date, last_login_date, last_invitation FROM doctor');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, is_administrator BOOLEAN NOT NULL, email CLOB NOT NULL COLLATE BINARY, password_hash CLOB NOT NULL COLLATE BINARY, reset_hash CLOB NOT NULL COLLATE BINARY, is_enabled BOOLEAN NOT NULL, invitation_identifier CLOB DEFAULT NULL COLLATE BINARY, job_title CLOB DEFAULT NULL COLLATE BINARY, given_name CLOB NOT NULL COLLATE BINARY, family_name CLOB NOT NULL COLLATE BINARY, street CLOB DEFAULT NULL COLLATE BINARY, street_nr CLOB DEFAULT NULL COLLATE BINARY, address_line CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, phone CLOB DEFAULT NULL COLLATE BINARY, deleted_at DATETIME DEFAULT NULL, registration_date DATETIME DEFAULT NULL, last_login_date DATETIME DEFAULT NULL, last_invitation DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO doctor (id, is_administrator, email, password_hash, reset_hash, is_enabled, invitation_identifier, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at, registration_date, last_login_date, last_invitation) SELECT id, is_administrator, email, password_hash, reset_hash, is_enabled, invitation_identifier, job_title, given_name, family_name, street, street_nr, address_line, postal_code, city, country, phone, deleted_at, registration_date, last_login_date, last_invitation FROM __temp__doctor');
        $this->addSql('DROP TABLE __temp__doctor');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AE7927C74 ON doctor (email)');
        $this->addSql('DROP INDEX IDX_44A858CB87F4FB17');
        $this->addSql('DROP INDEX IDX_44A858CBCC22AD4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor_clinics AS SELECT doctor_id, clinic_id FROM doctor_clinics');
        $this->addSql('DROP TABLE doctor_clinics');
        $this->addSql('CREATE TABLE doctor_clinics (doctor_id INTEGER NOT NULL, clinic_id INTEGER NOT NULL, PRIMARY KEY(doctor_id, clinic_id), CONSTRAINT FK_44A858CB87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_44A858CBCC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO doctor_clinics (doctor_id, clinic_id) SELECT doctor_id, clinic_id FROM __temp__doctor_clinics');
        $this->addSql('DROP TABLE __temp__doctor_clinics');
        $this->addSql('CREATE INDEX IDX_44A858CB87F4FB17 ON doctor_clinics (doctor_id)');
        $this->addSql('CREATE INDEX IDX_44A858CBCC22AD4 ON doctor_clinics (clinic_id)');
        $this->addSql('DROP INDEX IDX_3BAE0AA76F45385D');
        $this->addSql('DROP INDEX IDX_3BAE0AA7CC22AD4');
        $this->addSql('DROP INDEX IDX_3BAE0AA787F4FB17');
        $this->addSql('DROP INDEX IDX_3BAE0AA71BDD81B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, confirmed_by_doctor_id INTEGER DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_3BAE0AA718D94A2 FOREIGN KEY (confirmed_by_doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7CC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA787F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA71BDD81B FOREIGN KEY (generated_by_id) REFERENCES event_generation (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event (id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at) SELECT id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CC22AD4 ON event (clinic_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA787F4FB17 ON event (doctor_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA71BDD81B ON event (generated_by_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA718D94A2 ON event (confirmed_by_doctor_id)');
        $this->addSql('DROP INDEX IDX_289901A271F7E88B');
        $this->addSql('DROP INDEX IDX_289901A2884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_event_tags AS SELECT event_id, event_tag_id FROM event_event_tags');
        $this->addSql('DROP TABLE event_event_tags');
        $this->addSql('CREATE TABLE event_event_tags (event_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_id, event_tag_id), CONSTRAINT FK_289901A271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_289901A2884B1443 FOREIGN KEY (event_tag_id) REFERENCES event_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event_event_tags (event_id, event_tag_id) SELECT event_id, event_tag_id FROM __temp__event_event_tags');
        $this->addSql('DROP TABLE __temp__event_event_tags');
        $this->addSql('CREATE INDEX IDX_289901A271F7E88B ON event_event_tags (event_id)');
        $this->addSql('CREATE INDEX IDX_289901A2884B1443 ON event_event_tags (event_tag_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('sqlite' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE doctor ADD COLUMN agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL');
        $this->addSql('DROP INDEX IDX_44A858CB87F4FB17');
        $this->addSql('DROP INDEX IDX_44A858CBCC22AD4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor_clinics AS SELECT doctor_id, clinic_id FROM doctor_clinics');
        $this->addSql('DROP TABLE doctor_clinics');
        $this->addSql('CREATE TABLE doctor_clinics (doctor_id INTEGER NOT NULL, clinic_id INTEGER NOT NULL, PRIMARY KEY(doctor_id, clinic_id))');
        $this->addSql('INSERT INTO doctor_clinics (doctor_id, clinic_id) SELECT doctor_id, clinic_id FROM __temp__doctor_clinics');
        $this->addSql('DROP TABLE __temp__doctor_clinics');
        $this->addSql('CREATE INDEX IDX_44A858CB87F4FB17 ON doctor_clinics (doctor_id)');
        $this->addSql('CREATE INDEX IDX_44A858CBCC22AD4 ON doctor_clinics (clinic_id)');
        $this->addSql('DROP INDEX IDX_3BAE0AA718D94A2');
        $this->addSql('DROP INDEX IDX_3BAE0AA7CC22AD4');
        $this->addSql('DROP INDEX IDX_3BAE0AA787F4FB17');
        $this->addSql('DROP INDEX IDX_3BAE0AA71BDD81B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, confirmed_by_id INTEGER DEFAULT NULL, trade_tag INTEGER NOT NULL)');
        $this->addSql('INSERT INTO event (id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at) SELECT id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time, deleted_at FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CC22AD4 ON event (clinic_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA787F4FB17 ON event (doctor_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA71BDD81B ON event (generated_by_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA76F45385D ON event (confirmed_by_id)');
        $this->addSql('DROP INDEX IDX_289901A271F7E88B');
        $this->addSql('DROP INDEX IDX_289901A2884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_event_tags AS SELECT event_id, event_tag_id FROM event_event_tags');
        $this->addSql('DROP TABLE event_event_tags');
        $this->addSql('CREATE TABLE event_event_tags (event_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_id, event_tag_id))');
        $this->addSql('INSERT INTO event_event_tags (event_id, event_tag_id) SELECT event_id, event_tag_id FROM __temp__event_event_tags');
        $this->addSql('DROP TABLE __temp__event_event_tags');
        $this->addSql('CREATE INDEX IDX_289901A271F7E88B ON event_event_tags (event_id)');
        $this->addSql('CREATE INDEX IDX_289901A2884B1443 ON event_event_tags (event_tag_id)');
        $this->addSql('DROP INDEX IDX_8BC8B514B03A8386');
        $this->addSql('DROP INDEX IDX_8BC8B514EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation AS SELECT id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at FROM event_generation');
        $this->addSql('DROP TABLE event_generation');
        $this->addSql('CREATE TABLE event_generation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, minimal_gap_between_events NUMERIC(10, 0) NOT NULL, start_cron_expression CLOB NOT NULL, end_cron_expression CLOB NOT NULL, differentiate_by_event_type BOOLEAN NOT NULL, weekday_weight NUMERIC(10, 0) NOT NULL, saturday_weight NUMERIC(10, 0) NOT NULL, sunday_weight NUMERIC(10, 0) NOT NULL, holiday_weight NUMERIC(10, 0) NOT NULL, mind_previous_events BOOLEAN NOT NULL, step INTEGER NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO event_generation (id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at) SELECT id, created_by_id, last_changed_by_id, minimal_gap_between_events, start_cron_expression, end_cron_expression, differentiate_by_event_type, weekday_weight, saturday_weight, sunday_weight, holiday_weight, mind_previous_events, step, name, description, start_date_time, end_date_time, created_at, last_changed_at FROM __temp__event_generation');
        $this->addSql('DROP TABLE __temp__event_generation');
        $this->addSql('CREATE INDEX IDX_8BC8B514B03A8386 ON event_generation (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8BC8B514EE85B337 ON event_generation (last_changed_by_id)');
        $this->addSql('DROP INDEX IDX_3B6D33B77163DE68');
        $this->addSql('DROP INDEX IDX_3B6D33B7884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_assign_event_tags AS SELECT event_generation_id, event_tag_id FROM event_generation_assign_event_tags');
        $this->addSql('DROP TABLE event_generation_assign_event_tags');
        $this->addSql('CREATE TABLE event_generation_assign_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id))');
        $this->addSql('INSERT INTO event_generation_assign_event_tags (event_generation_id, event_tag_id) SELECT event_generation_id, event_tag_id FROM __temp__event_generation_assign_event_tags');
        $this->addSql('DROP TABLE __temp__event_generation_assign_event_tags');
        $this->addSql('CREATE INDEX IDX_3B6D33B77163DE68 ON event_generation_assign_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_3B6D33B7884B1443 ON event_generation_assign_event_tags (event_tag_id)');
        $this->addSql('DROP INDEX IDX_8D07A4AE7163DE68');
        $this->addSql('DROP INDEX IDX_8D07A4AE884B1443');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_conflicting_event_tags AS SELECT event_generation_id, event_tag_id FROM event_generation_conflicting_event_tags');
        $this->addSql('DROP TABLE event_generation_conflicting_event_tags');
        $this->addSql('CREATE TABLE event_generation_conflicting_event_tags (event_generation_id INTEGER NOT NULL, event_tag_id INTEGER NOT NULL, PRIMARY KEY(event_generation_id, event_tag_id))');
        $this->addSql('INSERT INTO event_generation_conflicting_event_tags (event_generation_id, event_tag_id) SELECT event_generation_id, event_tag_id FROM __temp__event_generation_conflicting_event_tags');
        $this->addSql('DROP TABLE __temp__event_generation_conflicting_event_tags');
        $this->addSql('CREATE INDEX IDX_8D07A4AE7163DE68 ON event_generation_conflicting_event_tags (event_generation_id)');
        $this->addSql('CREATE INDEX IDX_8D07A4AE884B1443 ON event_generation_conflicting_event_tags (event_tag_id)');
        $this->addSql('DROP INDEX IDX_A86F737E7163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_date_exception AS SELECT id, event_generation_id, event_type, start_date_time, end_date_time FROM event_generation_date_exception');
        $this->addSql('DROP TABLE event_generation_date_exception');
        $this->addSql('CREATE TABLE event_generation_date_exception (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_generation_id INTEGER DEFAULT NULL, event_type INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL)');
        $this->addSql('INSERT INTO event_generation_date_exception (id, event_generation_id, event_type, start_date_time, end_date_time) SELECT id, event_generation_id, event_type, start_date_time, end_date_time FROM __temp__event_generation_date_exception');
        $this->addSql('DROP TABLE __temp__event_generation_date_exception');
        $this->addSql('CREATE INDEX IDX_A86F737E7163DE68 ON event_generation_date_exception (event_generation_id)');
        $this->addSql('DROP INDEX IDX_66938B43CC22AD4');
        $this->addSql('DROP INDEX IDX_66938B437163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_target_clinic AS SELECT id, clinic_id, event_generation_id, weight, generation_score, default_order FROM event_generation_target_clinic');
        $this->addSql('DROP TABLE event_generation_target_clinic');
        $this->addSql('CREATE TABLE event_generation_target_clinic (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, clinic_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL)');
        $this->addSql('INSERT INTO event_generation_target_clinic (id, clinic_id, event_generation_id, weight, generation_score, default_order) SELECT id, clinic_id, event_generation_id, weight, generation_score, default_order FROM __temp__event_generation_target_clinic');
        $this->addSql('DROP TABLE __temp__event_generation_target_clinic');
        $this->addSql('CREATE INDEX IDX_66938B43CC22AD4 ON event_generation_target_clinic (clinic_id)');
        $this->addSql('CREATE INDEX IDX_66938B437163DE68 ON event_generation_target_clinic (event_generation_id)');
        $this->addSql('DROP INDEX IDX_7ED0809D87F4FB17');
        $this->addSql('DROP INDEX IDX_7ED0809D7163DE68');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_generation_target_doctor AS SELECT id, doctor_id, event_generation_id, weight, generation_score, default_order FROM event_generation_target_doctor');
        $this->addSql('DROP TABLE event_generation_target_doctor');
        $this->addSql('CREATE TABLE event_generation_target_doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, doctor_id INTEGER DEFAULT NULL, event_generation_id INTEGER DEFAULT NULL, weight NUMERIC(10, 0) NOT NULL, generation_score NUMERIC(10, 0) DEFAULT NULL, default_order INTEGER NOT NULL)');
        $this->addSql('INSERT INTO event_generation_target_doctor (id, doctor_id, event_generation_id, weight, generation_score, default_order) SELECT id, doctor_id, event_generation_id, weight, generation_score, default_order FROM __temp__event_generation_target_doctor');
        $this->addSql('DROP TABLE __temp__event_generation_target_doctor');
        $this->addSql('CREATE INDEX IDX_7ED0809D87F4FB17 ON event_generation_target_doctor (doctor_id)');
        $this->addSql('CREATE INDEX IDX_7ED0809D7163DE68 ON event_generation_target_doctor (event_generation_id)');
        $this->addSql('DROP INDEX IDX_68CD3612CD53EDB6');
        $this->addSql('DROP INDEX IDX_68CD3612C12D89BC');
        $this->addSql('DROP INDEX IDX_68CD3612F624B39D');
        $this->addSql('DROP INDEX IDX_68CD361256CBCE22');
        $this->addSql('DROP INDEX IDX_68CD3612B03A8386');
        $this->addSql('DROP INDEX IDX_68CD3612EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer AS SELECT id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, created_by_id, last_changed_by_id, message, is_resolved, receiver_authorization_status, sender_authorization_status, created_at, last_changed_at FROM event_offer');
        $this->addSql('DROP TABLE event_offer');
        $this->addSql('CREATE TABLE event_offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, receiver_id INTEGER DEFAULT NULL, receiver_clinic_id INTEGER DEFAULT NULL, sender_id INTEGER DEFAULT NULL, sender_clinic_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, message CLOB DEFAULT NULL, is_resolved BOOLEAN NOT NULL, receiver_authorization_status INTEGER NOT NULL, sender_authorization_status INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO event_offer (id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, created_by_id, last_changed_by_id, message, is_resolved, receiver_authorization_status, sender_authorization_status, created_at, last_changed_at) SELECT id, receiver_id, receiver_clinic_id, sender_id, sender_clinic_id, created_by_id, last_changed_by_id, message, is_resolved, receiver_authorization_status, sender_authorization_status, created_at, last_changed_at FROM __temp__event_offer');
        $this->addSql('DROP TABLE __temp__event_offer');
        $this->addSql('CREATE INDEX IDX_68CD3612CD53EDB6 ON event_offer (receiver_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612C12D89BC ON event_offer (receiver_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612F624B39D ON event_offer (sender_id)');
        $this->addSql('CREATE INDEX IDX_68CD361256CBCE22 ON event_offer (sender_clinic_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612B03A8386 ON event_offer (created_by_id)');
        $this->addSql('CREATE INDEX IDX_68CD3612EE85B337 ON event_offer (last_changed_by_id)');
        $this->addSql('DROP INDEX IDX_67C12A18152A9D3E');
        $this->addSql('DROP INDEX IDX_67C12A1871F7E88B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_offer_events AS SELECT event_offer_id, event_id FROM event_offer_events');
        $this->addSql('DROP TABLE event_offer_events');
        $this->addSql('CREATE TABLE event_offer_events (event_offer_id INTEGER NOT NULL, event_id INTEGER NOT NULL, PRIMARY KEY(event_offer_id, event_id))');
        $this->addSql('INSERT INTO event_offer_events (event_offer_id, event_id) SELECT event_offer_id, event_id FROM __temp__event_offer_events');
        $this->addSql('DROP TABLE __temp__event_offer_events');
        $this->addSql('CREATE INDEX IDX_67C12A18152A9D3E ON event_offer_events (event_offer_id)');
        $this->addSql('CREATE INDEX IDX_67C12A1871F7E88B ON event_offer_events (event_id)');
        $this->addSql('DROP INDEX IDX_4FDF0D2C71F7E88B');
        $this->addSql('DROP INDEX IDX_4FDF0D2CB03A8386');
        $this->addSql('DROP INDEX IDX_4FDF0D2CEE85B337');
        $this->addSql('DROP INDEX IDX_4FDF0D2C18D94A2');
        $this->addSql('DROP INDEX IDX_4FDF0D2CCC22AD4');
        $this->addSql('DROP INDEX IDX_4FDF0D2C87F4FB17');
        $this->addSql('DROP INDEX IDX_4FDF0D2C1BDD81B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event_past AS SELECT id, event_id, created_by_id, last_changed_by_id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time FROM event_past');
        $this->addSql('DROP TABLE event_past');
        $this->addSql('CREATE TABLE event_past (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, clinic_id INTEGER DEFAULT NULL, doctor_id INTEGER DEFAULT NULL, generated_by_id INTEGER DEFAULT NULL, event_change_type INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, confirm_date_time DATETIME DEFAULT NULL, last_remainder_email_sent DATETIME DEFAULT NULL, event_type INTEGER NOT NULL, start_date_time DATETIME NOT NULL, end_date_time DATETIME NOT NULL, confirmed_by_id INTEGER DEFAULT NULL, trade_tag INTEGER NOT NULL)');
        $this->addSql('INSERT INTO event_past (id, event_id, created_by_id, last_changed_by_id, confirmed_by_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time) SELECT id, event_id, created_by_id, last_changed_by_id, confirmed_by_doctor_id, clinic_id, doctor_id, generated_by_id, event_change_type, created_at, last_changed_at, confirm_date_time, last_remainder_email_sent, event_type, start_date_time, end_date_time FROM __temp__event_past');
        $this->addSql('DROP TABLE __temp__event_past');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C71F7E88B ON event_past (event_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CB03A8386 ON event_past (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CEE85B337 ON event_past (last_changed_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2CCC22AD4 ON event_past (clinic_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C87F4FB17 ON event_past (doctor_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C1BDD81B ON event_past (generated_by_id)');
        $this->addSql('CREATE INDEX IDX_4FDF0D2C6F45385D ON event_past (confirmed_by_id)');
        $this->addSql('DROP INDEX IDX_9F74B898B03A8386');
        $this->addSql('DROP INDEX IDX_9F74B898EE85B337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_id INTEGER DEFAULT NULL, last_changed_by_id INTEGER DEFAULT NULL, support_mail CLOB NOT NULL, organisation_name CLOB NOT NULL, can_confirm_days_advance INTEGER NOT NULL, must_confirm_days_advance INTEGER NOT NULL, send_remainder_days_interval INTEGER NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO setting (id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at) SELECT id, created_by_id, last_changed_by_id, support_mail, organisation_name, can_confirm_days_advance, must_confirm_days_advance, send_remainder_days_interval, created_at, last_changed_at FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B898B03A8386 ON setting (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9F74B898EE85B337 ON setting (last_changed_by_id)');
    }
}
