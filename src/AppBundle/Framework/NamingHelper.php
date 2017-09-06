<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 16:32
 */

namespace AppBundle\Framework;


class NamingHelper
{
    /**
     * produces my_class_name from Famoser\Class\MyClassName
     * @param $classWithNamespace
     * @return string
     */
    public static function classToTranslationDomain($classWithNamespace)
    {
        $className = substr($classWithNamespace, strrpos($classWithNamespace, "\\") + 1);
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $className));
    }

    /**
     * produces my_constant from MY_CONSTANT
     *
     * @param $constant
     * @return string
     */
    public static function constantToTranslation($constant)
    {
        return strtolower($constant);
    }
}