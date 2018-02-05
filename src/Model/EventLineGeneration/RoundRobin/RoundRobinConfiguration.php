<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventLineGeneration\RoundRobin;

use App\Model\EventLineGeneration\Base\BaseConfiguration;

class RoundRobinConfiguration extends BaseConfiguration
{
    /* @var bool $randomOrderMade */
    public $randomOrderMade;
    /* @var MemberConfiguration[] $memberConfigurations */
    public $memberConfigurations;

    /**
     * RoundRobinConfiguration constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        if (null !== $data) {
            foreach ($data->memberConfigurations as $key => $item) {
                $this->memberConfigurations[] = new MemberConfiguration($item);
            }
            $this->randomOrderMade = $data->randomOrderMade;
        } else {
            $this->randomOrderMade = false;
        }
        parent::__construct($data);
    }
}
