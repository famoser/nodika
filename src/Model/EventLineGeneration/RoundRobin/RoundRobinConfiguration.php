<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:06
 */

namespace App\Model\EventLineGeneration\RoundRobin;

use App\Model\EventLineGeneration\Base\BaseConfiguration;

class RoundRobinConfiguration extends BaseConfiguration
{
    /**
     * RoundRobinConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        if ($data != null) {
            foreach ($data->memberConfigurations as $key => $item) {
                $this->memberConfigurations[] = new MemberConfiguration($item);
            }
            $this->randomOrderMade = $data->randomOrderMade;
        } else {
            $this->randomOrderMade = false;
        }
        parent::__construct($data);
    }

    /* @var bool $randomOrderMade */
    public $randomOrderMade;

    /* @var MemberConfiguration[] $memberConfigurations */
    public $memberConfigurations;
}
