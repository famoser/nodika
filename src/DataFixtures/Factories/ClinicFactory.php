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

namespace App\DataFixtures\Factories;

use App\Entity\Clinic;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Clinic>
 */
final class ClinicFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Clinic::class;
    }

    /**
     * @return array<string, bool|string>
     */
    public function defaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'description' => self::faker()->sentence(),
            'email' => self::faker()->unique()->safeEmail(),
            'phone' => self::faker()->phoneNumber(),
            'street' => self::faker()->streetName(),
            'streetNr' => self::faker()->buildingNumber(),
            'postalCode' => self::faker()->postcode(),
            'city' => self::faker()->city(),
        ];
    }
}
