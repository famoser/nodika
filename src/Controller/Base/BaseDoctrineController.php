<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Base;

use App\Entity\Base\BaseEntity;

class BaseDoctrineController extends BaseController
{
    /**
     * saves entity to database.
     *
     * @param BaseEntity[] $entities
     */
    protected function fastSave(...$entities)
    {
        $mgr = $this->getDoctrine()->getManager();
        foreach ($entities as $item) {
            $mgr->persist($item);
        }
        $mgr->flush();
    }

    /**
     * removes entity to database.
     *
     * @param BaseEntity[] $entities
     */
    protected function fastRemove(...$entities)
    {
        $mgr = $this->getDoctrine()->getManager();
        foreach ($entities as $item) {
            $mgr->remove($item);
        }
        $mgr->flush();
    }
}
