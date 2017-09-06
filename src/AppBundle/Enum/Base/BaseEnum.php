<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 08:41
 */

namespace AppBundle\Enum\Base;

use AppBundle\Framework\NamingHelper;
use AppBundle\Framework\TranslatableObject;
use ReflectionClass;

abstract class BaseEnum extends TranslatableObject
{
    /**
     * generates an array to be used in form fields
     *
     * @return array
     */
    protected function getChoicesForBuilderInternal()
    {
        $reflection = new ReflectionClass(get_class($this));

        $choices = $reflection->getConstants();
        $toString = $reflection->getMethod('toString');

        $res = [];
        foreach ($choices as $choice) {
            $res[$toString->invoke($this, $choice)] = $choice;
        }
        return ["choices" => $res, 'choice_translation_domain' => $this->getTranslationDomain()];
    }

    /**
     * translate enum value
     *
     * @param $enumValue
     * @return array
     */
    protected function getTranslationForBuilderInternal($enumValue)
    {
        $reflection = new ReflectionClass(get_class($this));

        $choices = $reflection->getConstants();
        foreach ($choices as $name => $value) {
            if ($value == $enumValue) {
                return ["translation_domain" => $this->getTranslationDomain(), "label" => NamingHelper::constantToTranslation($value)];
            }
        }
        return ["translation_domain" => "common_error", "label" => "enum.invalid_constant"];
    }

    /**
     * @return string
     */
    protected function getTranslationDomainPrefix()
    {
        return "enum";
    }
}