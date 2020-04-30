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
     * @return string
     */
    public static function getText($enumValue)
    {
        $elem = new static();

        return $elem->getTextInternal($enumValue);
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @return array
     */
    public static function getPossibleValues()
    {
        $elem = new static();

        return $elem->getPossibleValuesInternal();
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
     * generates an array of all possible enum values.
     *
     * @return array
     */
    private function getPossibleValuesInternal()
    {
        try {
            $reflection = new ReflectionClass(static::class);

            return array_values($reflection->getConstants());
        } catch (\ReflectionException $e) {
        }

        return [];
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
            $reflection = new ReflectionClass(static::class);
            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                $res[mb_strtolower($name)] = $value;
            }
            $transDomain = 'enum_'.$this->camelCaseToTranslation($reflection->getShortName());

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
     * @return bool|string
     */
    private function getTranslationInternal($enumValue, TranslatorInterface $translator)
    {
        try {
            $reflection = new ReflectionClass(static::class);

            return $translator->trans($this->getTextInternal($enumValue, $reflection), [], 'enum_'.$this->camelCaseToTranslation($reflection->getShortName()));
        } catch (\ReflectionException $e) {
        }

        return '';
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     * @param ReflectionClass $reflection
     *
     * @return bool|string
     */
    private function getTextInternal($enumValue, $reflection = null)
    {
        try {
            if (null === $reflection) {
                $reflection = new ReflectionClass(static::class);
            }

            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                if ($value === $enumValue) {
                    return mb_strtolower($name);
                }
            }
        } catch (\ReflectionException $e) {
        }

        return '';
    }
}
