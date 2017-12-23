<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/09/2017
 * Time: 12:52
 */

namespace App\Helper;

class HashHelper
{
    /**
     * creates a hash fit to be used as reset hash
     *
     * @return string
     */
    public static function createNewResetHash()
    {
        $newHash = '';
        //0-9, A-Z, a-z
        $allowedRanges = [[48, 57], [65, 90], [97, 122]];
        for ($i = 0; $i < 20; $i++) {
            $rand = mt_rand(20, 160);
            $allowed = false;
            for ($j = 0; $j < count($allowedRanges); $j++) {
                if ($allowedRanges[$j][0] <= $rand && $allowedRanges[$j][1] >= $rand) {
                    $allowed = true;
                }
            }
            if ($allowed) {
                $newHash .= chr($rand);
            } else {
                $i--;
            }
        }
        return $newHash;
    }
}
