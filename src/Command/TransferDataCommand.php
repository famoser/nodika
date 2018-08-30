<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Traits\IdTrait;
use App\Enum\EventTagColor;
use PDO;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/01/2018
 * Time: 19:47.
 */
class TransferDataCommand extends Command
{
    const CURRENT = 1;
    const OLD = 2;

    const DB_PATH = 'var/data_before_migration.sqlite';
    const DB_PATH2 = 'var/data_before_migration.sqlite2';

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * TransferDataCommand constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct();
        $this->doctrine = $registry;
    }

    protected function configure()
    {
        $this
            ->setName('app:transfer-data')
            ->setDescription('Transfers the data from an old version of the database.')
            ->setHelp('This will clear the new database, and then transfer the data from an old version of the db to the new one. The old database should be located at '.self::DB_PATH.
                "\n\nThis does not fully transfer all data, only the one used by the current installation. For example, event offers are not transferred, nor are old generations.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(self::DB_PATH)) {
            $output->writeln('old db not found at '.self::DB_PATH);

            return;
        }

        copy(self::DB_PATH, self::DB_PATH2);

        $output->writeln('cleaning old db');
        $this->cleanOldDb();

        $output->writeln('clearing new db');
        $this->clearNewDb();

        $output->writeln('importing emails');
        $this->importEmails();

        $output->writeln('importing doctors & clinics');
        $this->importClinics();

        $output->writeln('importing events & event past');
        $this->importEvents();

        $output->writeln('db migration done. Directly in the db, set the administrators & the ones who receive admin mail.');
    }

    /**
     * removes all entries from new database.
     */
    private function clearNewDb()
    {
        $tableNames = [
            //remove generations
            'event_generation_assign_event_tags',
            'event_generation_conflicting_event_tags',
            'event_generation_date_exception',
            'event_generation_target_clinic',
            'event_generation_target_doctor',
            'event_generation',

            //remove offers
            'event_offer',
            'event_offer_events',

            //remove tags
            'event_event_tags',
            'event_tag',

            //remove events
            'event_past',
            'event',

            //remove users
            'doctor_clinics',
            'clinic',
            'doctor',

            //remove basic infrastructure
            'email',
            'setting',
        ];

        foreach ($tableNames as $tableName) {
            $this->executeQuery(self::CURRENT, 'DELETE FROM '.$tableName);
        }
    }

    private function cleanOldDb()
    {
        $this->executeQuery(self::OLD, 'DELETE FROM event WHERE id IN (SELECT e.id FROM event e INNER JOIN event_line el ON e.event_line_id = el.id WHERE el.deleted_at IS NOT NULL)');
        $this->executeQuery(self::OLD, 'DELETE FROM event_past WHERE event_id IN (SELECT e.id FROM event e INNER JOIN event_line el ON e.event_line_id = el.id WHERE el.deleted_at IS NOT NULL)');
        $this->executeQuery(self::OLD, 'DELETE FROM event_line WHERE deleted_at IS NOT NULL');
    }

    private function importEmails()
    {
        $emails = $this->executeQuery(self::OLD, 'SELECT id, receiver, identifier, subject, body, action_text, carbon_copy, email_type, sent_date_time, visited_date_time FROM email');
        $this->insertFields(
            $emails,
            'email'
        );
    }

    private function importClinics()
    {
        $doctors = $this->executeQuery(
            self::OLD,
            'SELECT 
	p.id as id, 
	0 as is_administrator, 
	0 as receives_administrator_mail, 
	p.email as email, 
	password_hash as password_hash, 
	reset_hash as reset_hash,
	is_active as is_enabled,
	invitation_hash as invitation_identifier,
	job_title as job_title,
	given_name as given_name,
	family_name as family_name,
	street as street,
	street_nr as street_nr,
	address_line as address_line,
	postal_code as postal_code,
	city as city,
	country as country,
	phone as phone,
	p.deleted_at as deleted_at,
	registration_date as registration_date,
	NULL as last_login_date,
	invitation_date_time as last_invitation
FROM frontend_user f 
INNER JOIN person p ON f.person_id = p.id');

        $this->insertFields(
            $doctors,
            'doctor'
        );

        $clinics = $this->executeQuery(
            self::OLD,
            'SELECT id, name, description, street, street_nr, address_line, postal_code, city, country, phone, email, deleted_at, invitation_date_time as last_invitation, invitation_hash as invitation_identifier FROM member');

        $this->insertFields(
            $clinics,
            'clinic'
        );

        $clinics = $this->executeQuery(
            self::OLD,
            'SELECT person_id as doctor_id, member_id as clinic_id FROM person_members');

        $this->insertFields(
            $clinics,
            'doctor_clinics'
        );
    }

    private function importEvents()
    {
        $eventLines = $this->executeQuery(
            self::OLD,
            'SELECT id, name, description FROM event_line');

        //correct different confirmed by treatment
        $colors = [EventTagColor::RED, EventTagColor::BLUE, EventTagColor::YELLOW, EventTagColor::GREEN];
        $counter = 0;
        foreach ($eventLines as &$eventLine) {
            $eventLine['color'] = $colors[$counter++ % \count($colors)];
        }

        $this->insertFields(
            $eventLines,
            'event_tag'
        );

        $events = $this->executeQuery(
            self::OLD,
            'SELECT id, event_line_id, member_id as clinic_id, person_id as doctor_id, is_confirmed_date_time as confirm_date_time, NULL as confirmed_by_doctor_id, last_remainder_email_sent, 0 as event_type, start_date_time, end_date_time, deleted_at FROM event');

        $eventTags = [];
        //correct different confirmed by treatment
        foreach ($events as &$event) {
            if ($event['confirm_date_time'] && null !== $event['doctor_id']) {
                $event['confirmed_by_doctor_id'] = $event['doctor_id'];
            }
            $eventTags[] = ['event_id' => $event['id'], 'event_tag_id' => $event['event_line_id']];
            unset($event['event_line_id']);
        }

        $this->insertFields(
            $events,
            'event'
        );

        $this->insertFields(
            $eventTags,
            'event_event_tags'
        );

        $eventPasts = $this->executeQuery(
            self::OLD,
            'SELECT id, changed_by_person_id as created_by_id, changed_at_date_time as created_at, event_change_type, after_event_json as payload FROM event_past'
        );

        foreach ($eventPasts as &$eventPast) {
            $payload = $eventPast['payload'];
            unset($eventPast['payload']);
            unset($eventPast['deleted_at']);

            $oldEvent = json_decode($payload);
            $eventPast['start_date_time'] = $oldEvent->startDateTime->date;
            $eventPast['end_date_time'] = $oldEvent->endDateTime->date;
            $eventPast['clinic_id'] = $oldEvent->memberId;
            $eventPast['doctor_id'] = $oldEvent->personId;
            $eventPast['event_type'] = 0;
            if (property_exists($oldEvent, 'isConfirmedDateTime')) {
                $eventPast['confirm_date_time'] = $oldEvent->isConfirmedDateTime;
                $eventPast['confirmed_by_doctor_id'] = $eventPast['doctor_id'];
            } else {
                $eventPast['confirm_date_time'] = null;
                $eventPast['confirmed_by_doctor_id'] = null;
            }
            if (property_exists($oldEvent, 'lastRemainderEmailSent')) {
                $eventPast['last_remainder_email_sent'] = $eventPast->lastRemainderEmailSent;
            } else {
                $eventPast['last_remainder_email_sent'] = null;
            }
        }

        $this->insertFields(
            $eventPasts,
            'event_past'
        );
    }

    /**
     * @param IdTrait[] $objects
     */
    private function persistAll(array $objects)
    {
        $manager = $this->doctrine->getManager();
        foreach ($objects as $object) {
            $manager->persist($object);
        }
        $manager->flush();
    }

    /**
     * @param array  $content
     * @param array  $fieldSpezification
     * @param string $table
     */
    private function insertFields(array $content, string $table)
    {
        if (!$this->normalizeFieldSpezification($content, null, $fieldNames, $methodNames, $conversions)) {
            return;
        }

        //create insert sql
        $entries = [];
        $parameters = [];
        for ($i = 0; $i < \count($content); ++$i) {
            $entry = [];
            foreach ($fieldNames as $fieldName) {
                $currentKey = ':'.$fieldName.'_'.$i;
                $entry[] = $currentKey;
                $parameters[$currentKey] = $content[$i][$fieldName];
            }
            $entries[] = implode(', ', $entry);
        }

        //abort if none
        if (0 === \count($entries)) {
            return;
        }

        //preapre & execute sql
        $sql = 'INSERT INTO '.$table.' ('.implode(',', $fieldNames).') VALUES ';
        $sql .= '('.implode('),(', $entries).')';
        $this->executeQuery(self::CURRENT, $sql, $parameters);
    }

    /**
     * @param array $fieldSpezification
     * @param $fieldNames
     * @param $methodNames
     * @param $conversions
     *
     * @return bool
     */
    private function normalizeFieldSpezification($content, $fieldSpezification, &$fieldNames, &$methodNames, &$conversions)
    {
        if (0 === \count($content)) {
            return false;
        }

        //get fields from data set
        if (null === $fieldSpezification) {
            $fieldSpezification = [];
            foreach ($content[0] as $key => $value) {
                if (!is_numeric($key)) {
                    $fieldSpezification[] = $key;
                }
            }
        }

        //normalize mapping
        $fieldNames = [];
        $methodNames = [];
        $conversions = [];
        foreach ($fieldSpezification as $key => $value) {
            if (\is_string($key)) {
                $field = $key;
                $conversion = $value;
            } else {
                $field = $value;
                $conversion = 0;
            }
            $fieldNames[] = $field;
            $methodNames[] = 'set'.ucwords(str_replace('_', ' ', $field));
            $conversions[] = $conversion;
        }

        return true;
    }

    /**
     * @param array    $content
     * @param array    $fieldSpezification
     * @param callable $newObject
     *
     * @return array
     */
    private function writeFields(array $content, array $fieldSpezification, callable $newObject)
    {
        if (!$this->normalizeFieldSpezification($content, $fieldSpezification, $fieldNames, $methodNames, $conversions)) {
            return [];
        }

        //create objects
        $res = [];
        foreach ($content as $entry) {
            $object = $newObject();
            for ($i = 0; $i < \count($fieldNames); ++$i) {
                //get & optionally convert value
                $value = $entry[$fieldNames[$i]];
                $conversion = $conversions[$i];
                if (1 === $conversion) {
                    $value = (int) $value;
                } elseif (2 === $conversion && null !== $value) {
                    $value = new \DateTime($value);
                }

                //set to object
                $methodName = $methodNames[$i];
                $object->$methodName($value);
            }
            $res[] = $object;
        }

        return $res;
    }

    /**
     * @param $target
     * @param $sql
     * @param array $parameters
     *
     * @return array
     */
    private function executeQuery($target, $sql, $parameters = [])
    {
        if (self::CURRENT === $target) {
            $pdo = $this->doctrine->getConnection();
        } else {
            $pdo = new PDO('sqlite:'.realpath(self::DB_PATH2));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        /** @var \PDO $pdo */
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }
}