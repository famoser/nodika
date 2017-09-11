<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 16:24
 */

namespace AppBundle\Framework;


use AppBundle\Helper\NamingHelper;

abstract class TranslatableObject
{
    /**
     * get the prefix of the translation domain of this object
     *
     * @return mixed
     */
    protected abstract function getTranslationDomainPrefix();

    /**
     * get the translation domain of the current object
     *
     * @return string
     */
    public function getTranslationDomain()
    {
        $class = get_class($this);
        return $this->getTranslationDomainPrefix() . "_" . NamingHelper::classToTranslationDomain($class);
    }

    /**
     * the array for the builder including the translation_domain member
     *
     * @return string[]
     */
    protected function getTranslationDomainForBuilder()
    {
        return ["translation_domain" => static::getTranslationDomain()];
    }

    /**
     * the array for the builder including the translation_domain member
     *
     * @return string[]
     */
    public static function getTranslationDomainForBuilderStatic()
    {
        $instance = new static();
        return $instance->getTranslationDomainForBuilder();
    }

    /**
     * the array for the builder including the translation_domain member
     *
     * @return string
     */
    public static function getTranslationDomainStatic()
    {
        $instance = new static();
        return $instance->getTranslationDomain();
    }
}