<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Base;

use App\Controller\Base\BaseFormController;
use App\Model\Breadcrumb;

class BaseController extends BaseFormController
{
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl('administration_index'),
                $this->getTranslator()->trans('index.title', [], 'administration')
            ),
        ];
    }
}
