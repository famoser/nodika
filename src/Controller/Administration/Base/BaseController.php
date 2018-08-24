<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 8/24/18
 * Time: 2:03 PM
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
                $this->generateUrl("administration_index"),
                $this->getTranslator()->trans("index.title", [], "administration")
            )
        ];
    }
}