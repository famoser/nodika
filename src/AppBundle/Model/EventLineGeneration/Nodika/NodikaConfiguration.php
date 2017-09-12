<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:06
 */

namespace AppBundle\Model\EventLineGeneration\Nodika;


use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;

class NodikaConfiguration extends BaseConfiguration
{
    /**
     * NodikaConfiguration constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->memberConfigurations = [];
        if ($data != null) {
            foreach ($data->memberConfigurations as $key => $item) {
                $this->memberConfigurations[] = new MemberConfiguration($item);
            }
        }
        parent::__construct($data);
    }

    /* @var MemberConfiguration[] $memberConfigurations */
    public $memberConfigurations;
}