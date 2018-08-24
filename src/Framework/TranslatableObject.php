<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Framework;

use App\Helper\NamingHelper;

abstract class TranslatableObject
{
    /**
     * the array for the builder including the translation_domain clinic.
     *
     * @return string[]
     */
    public static function getTranslationDomainForBuilderStatic()
    {
        $instance = new static();

        return $instance->getTranslationDomainForBuilder();
    }

    /**
     * the array for the builder including the translation_domain clinic.
     *
     * @return string[]
     */
    protected function getTranslationDomainForBuilder()
    {
        return ['translation_domain' => $this->getTranslationDomain()];
    }

    /**
     * get the translation domain of the current object.
     *
     * @return string
     */
    public function getTranslationDomain()
    {
        $class = get_class($this);

        return $this->getTranslationDomainPrefix().'_'.NamingHelper::classToTranslationDomain($class);
    }

    /**
     * get the prefix of the translation domain of this object.
     *
     * @return mixed
     */
    abstract protected function getTranslationDomainPrefix();

    /**
     * the array for the builder including the translation_domain clinic.
     *
     * @return string
     */
    public static function getTranslationDomainStatic()
    {
        $instance = new static();

        return $instance->getTranslationDomain();
    }
}
