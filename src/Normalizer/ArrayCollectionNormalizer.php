<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ArrayCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param array  $object  Object to normalize
     * @param string $format  Format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @throws InvalidArgumentException   Occurs when the object given is not an attempted type for the normalizer
     * @throws CircularReferenceException Occurs when the normalizer detects a circular reference when no circular
     *                                    reference handler can fix it
     * @throws LogicException             Occurs when the normalizer is not called in an expected context
     *
     * @return array|string|int|float|bool
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalized = [];

        foreach ($object as $key => $val) {
            $normalized[] = $this->normalizer->normalize($val, $format, $context);
        }

        return $normalized;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data   Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ArrayCollection;
    }

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * Sets the owning Normalizer object.
     *
     * @param NormalizerInterface $normalizer
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
