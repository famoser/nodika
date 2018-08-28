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
    const NEW = 1;
    const OLD = 2;

    const DB_PATH = '../../var/data_before_migration.sqlite';

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
            ->setHelp('This will clear the new database, and then transfer the data from an old version of the db to the new one. The old database should be located at '.self::DB_PATH);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('clearing new db');
        $this->clearNewDb();
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

            //remove tags
            'event_event_tags',
            'event_tag',

            //remove offers
            'event_offer_authorization',
            'event_offer_entry',
            'event_offer',

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
            $this->executeQuery(self::NEW, 'DELETE * FROM '.$tableName);
        }
    }

    /**
     * @param $target
     * @param $sql
     * @param array $values
     */
    private function executeQuery($target, $sql, $values = [])
    {
        if (self::NEW === $target) {
            $pdo = $this->doctrine->getConnection();
        } else {
            $pdo = new PDO('sqlite:'.self::DB_PATH);
        }
        /** @var \PDO $pdo */
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
}
