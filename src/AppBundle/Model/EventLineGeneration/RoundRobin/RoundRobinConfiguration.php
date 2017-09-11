<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:06
 */

namespace AppBundle\Model\EventLineGeneration\RoundRobin;


use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;

class RoundRobinConfiguration extends BaseConfiguration
{
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        $this->randomOrderMade = false;
        if ($data != null) {
            $this->randomOrderMade = $data->randomOrderMade;
            foreach ($data->memberConfigurations as $key => $item) {
                $this->memberConfigurations[] = new MemberConfiguration($item);
            }
            $this->randomOrderMade = $data->randomOrderMade;
        }
        parent::__construct($data);
    }

    /* @var bool $randomOrderMade */
    public $randomOrderMade;

    /* @var MemberConfiguration[] $memberConfigurations */
    public $memberConfigurations;
}