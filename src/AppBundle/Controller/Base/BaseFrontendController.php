<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:50
 */

namespace AppBundle\Controller\Base;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Person;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Helper\CsvFileHelper;
use AppBundle\Helper\NamingHelper;
use AppBundle\Helper\SessionHelper;
use AppBundle\Helper\StaticMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaseFrontendController extends BaseController
{
    /**
     * returns the member of the logged in user or null
     *
     * @return Member
     */
    protected function getMember()
    {
        $person = $this->getPerson();
        if ($person != null) {
            $session = $this->get("session");
            if ($session->has(SessionHelper::ACTIVE_MEMBER_ID)) {
                $activeMemberId = $session->get(SessionHelper::ACTIVE_MEMBER_ID);
                foreach ($person->getMembers() as $member) {
                    if ($member->getId() == $activeMemberId) {
                        return $member;
                    }
                }
            } else {
                if ($person->getMembers()->count() > 0) {
                    $activeMember = $person->getMembers()->first();
                    $session->set(SessionHelper::ACTIVE_MEMBER_ID, $activeMember->getId());
                    return $activeMember;
                }
            }
        }
        return null;
    }

    /**
     * sets the active member
     *
     * @param Member $member
     */
    protected function setMember(Member $member)
    {
        $session = $this->get("session");
        $session->set(SessionHelper::ACTIVE_MEMBER_ID, $member->getId());
    }
}