<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Setting;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/settings")
 */
class SettingController extends BaseController
{
    /**
     * @Route("/edit", name="administration_setting_edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $setting = $this->getDoctrine()->getRepository(Setting::class)->findSingle();
        $myForm = $this->handleUpdateForm(
            $request,
            $setting
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/setting/edit.html.twig', $arr);
    }
}
