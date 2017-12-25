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

use App\Entity\Member;
use App\Enum\SettingKey;
use App\Helper\SessionHelper;
use Symfony\Component\HttpFoundation\Response;

class BaseFrontendController extends BaseController
{
    private $memberCache = null;
    private $memberCacheHot = false;

    /**
     * returns the member of the logged in user or null.
     *
     * @return Member
     */
    protected function getMember()
    {
        if (null !== $this->memberCacheHot) {
            return $this->memberCache;
        }
        $person = $this->getPerson();
        if (null !== $person) {
            $session = $this->get('session');
            if ($session->has(SessionHelper::ACTIVE_MEMBER_ID)) {
                $activeMemberId = $session->get(SessionHelper::ACTIVE_MEMBER_ID);
            } else {
                $setting = $this->getDoctrine()->getRepository('App:Setting')->getByUser($this->getUser(), SettingKey::ACTIVE_MEMBER_ID);
                $activeMemberId = $setting->getContent();
            }
            foreach ($person->getMembers() as $member) {
                if ($member->getId() === $activeMemberId) {
                    $this->memberCache = $member;
                    $this->memberCacheHot = true;

                    return $member;
                }
            }
            if ($person->getMembers()->count() > 0) {
                $member = $person->getMembers()->first();
                $this->setMember($member);
                $this->memberCache = $member;
                $this->memberCacheHot = true;

                return $member;
            }
        }

        return null;
    }

    /**
     * @return \App\Entity\Organisation|null
     */
    protected function getOrganisation()
    {
        $member = $this->getMember();
        if ($member instanceof Member) {
            return $member->getOrganisation();
        }

        return null;
    }

    /**
     * sets the active member.
     *
     * @param Member $member
     */
    protected function setMember(Member $member)
    {
        $session = $this->get('session');
        $session->set(SessionHelper::ACTIVE_MEMBER_ID, $member->getId());
        $setting = $this->getDoctrine()->getRepository('App:Setting')->getByUser($this->getUser(), SettingKey::ACTIVE_MEMBER_ID);
        $setting->setContent($member->getId());
        $this->fastSave($setting);
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param string   $backUrl
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    protected function renderWithBackUrl($view, array $parameters, $backUrl, Response $response = null)
    {
        $this->addMenuEntries($parameters);

        return parent::renderWithBackUrl($view, $parameters, $backUrl, $response);
    }

    /**
     * Renders a view.
     *
     * @param string   $view          The view name
     * @param array    $parameters    An array of parameters to pass to the view
     * @param string   $justification why no backbutton
     * @param Response $response      A response instance
     *
     * @return Response A Response instance
     */
    protected function renderNoBackUrl($view, array $parameters, $justification, Response $response = null)
    {
        $this->addMenuEntries($parameters);

        return parent::renderNoBackUrl($view, $parameters, $justification, $response);
    }

    /**
     * add the entries needed to contruct the menu.
     *
     * @param $parameters
     */
    protected function addMenuEntries(&$parameters)
    {
        $parameters['menu_person'] = $this->getPerson();
        $member = $this->getMember();
        $parameters['menu_member'] = $member;
        $parameters['menu_unassigned_events_count'] = (null !== $member) ? $this->getDoctrine()->getRepository('App:Member')->countUnassignedEvents($member) : 0;
        $parameters['menu_unconfirmed_events_count'] = (null !== $member) ? $this->getDoctrine()->getRepository('App:Member')->countUnconfirmedEvents($member, $this->getPerson()) : 0;
        $parameters['menu_organisation'] = $this->getOrganisation();
    }
}
