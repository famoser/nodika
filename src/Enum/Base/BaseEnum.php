<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum\Base;

use ReflectionClass;
use Symfony\Component\Translation\TranslatorInterface;

abstract class BaseEnum
{
    /**
     * returns an array fit to be used by the ChoiceType.
     *
     * @return array
     */
    public static function getBuilderArguments()
    {
        $elem = new static();
        return $elem->getChoicesForBuilderInternal();
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @param TranslatorInterface $translator
     * @return string
     */
    public static function getTranslation($enumValue, TranslatorInterface $translator)
    {
        $elem = new static();
        return $elem->getTranslationInternal($enumValue, $translator);
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @param TranslatorInterface $translator
     * @return string
     */
    public static function getText($enumValue)
    {
        $elem = new static();
        return $elem->getTextInternal($enumValue);
    }

    /**
     * makes from camelCase => camel_case.
     *
     * @param $camelCase
     *
     * @return string
     */
    private static function camelCaseToTranslation($camelCase)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $camelCase));
    }

    /**
     * generates an array to be used in form fields.
     *
     * @return array
     */
    private function getChoicesForBuilderInternal()
    {
        try {
            $res = [];
            $reflection = new ReflectionClass(get_class($this));
            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                $res[strtolower($name)] = $value;
            }
            $transDomain = "enum_" . $this->camelCaseToTranslation($reflection->getShortName());
            return ['translation_domain' => $transDomain, 'label' => 'enum.name', 'choices' => $res, 'choice_translation_domain' => $transDomain];
        } catch (\ReflectionException $e) {
        }

        return [];
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @param TranslatorInterface $translator
     * @return bool|string
     */
    private function getTranslationInternal($enumValue, TranslatorInterface $translator)
    {
        try {
            $reflection = new ReflectionClass(get_class($this));
            return $translator->trans($this->getTextInternal($enumValue, $reflection), [], "enum_" . $this->camelCaseToTranslation($reflection->getShortName()));
        } catch (\ReflectionException $e) {
        }
        return "";
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @param ReflectionClass $reflection
     * @return bool|string
     */
    private function getTextInternal($enumValue, $reflection = null)
    {
        try {
            if ($reflection == null) {
                $reflection = new ReflectionClass(get_class($this));
            }

            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                if ($value === $enumValue) {
                    return strtolower($name);
                }
            }
        } catch (\ReflectionException $e) {
        }

        return "";
    }
}
