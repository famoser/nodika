<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 05/03/2017
 * Time: 08:41
 */

namespace AppBundle\Enum\Base;

use AppBundle\Helper\NamingHelper;
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

        $res = [];
        foreach ($choices as $name => $value) {
            $res[NamingHelper::constantToTranslation($name)] = $value;
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
                return ["translation_domain" => $this->getTranslationDomain(), "label" => NamingHelper::constantToTranslation($name)];
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


    /**
     * returns an array fit to be used by the ChoiceType
     *
     * @return array
     */
    public static function getChoicesForBuilder()
    {
        $elem = new static();
        return $elem->getChoicesForBuilderInternal();
    }

    /**
     * translate enum value
     *
     * @param $enumValue
     * @return array
     */
    public static function getTranslationForBuilder($enumValue)
    {
        $elem = new static();
        return $elem->getTranslationForBuilderInternal($enumValue);
    }
}