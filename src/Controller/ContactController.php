<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Entity\Doctor;
use App\Form\Model\ContactRequest\ContactRequestType;
use App\Model\ContactRequest;
use App\Service\EmailService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/contact")
 */
class ContactController extends BaseFormController
{
    /**
     * @Route("/", name="contact_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        //prefill contact request with user data
        $createContactRequest = function () {
            $contactRequest = new ContactRequest();
            if ($this->getUser() instanceof Doctor) {
                $contactRequest->setName($this->getUser()->getFullName());
                $contactRequest->setEmail($this->getUser()->getEmail());
            }

            return $contactRequest;
        };
        $contactRequest = $createContactRequest();

        //reuse create form logic
        $createForm = function ($contactRequest) {
            return $this->createForm(ContactRequestType::class, $contactRequest)
                ->add('submit', SubmitType::class, ['translation_domain' => 'contact', 'label' => 'index.send_mail']);
        };

        //contact form
        $form = $this->handleForm(
            $createForm($contactRequest),
            $request,
            function ($form) use ($request, $contactRequest, $translator, $emailService, $createContactRequest, $createForm) {
                /** @var FormInterface $form */
                // "check" is a hidden field; if it is filled out then we should prevent the bot from sending emails
                if (ContactRequestType::CHECK_DATA === $form->get('check')->getData() &&
                    ContactRequestType::CHECK2_DATA === $form->get('check2')->getData() &&
                    false === mb_strpos($contactRequest->getMessage(), 'bit.ly')) {
                    $userRepo = $this->getDoctrine()->getRepository(Doctor::class);
                    $admins = $userRepo->findBy(['isAdministrator' => true, 'receivesAdministratorMail' => true]);
                    foreach ($admins as $admin) {
                        /* @var FormInterface $form */
                        $emailService->sendTextEmail(
                            $admin->getEmail(),
                            $translator->trans('contact_email.subject', [], 'contact'),
                            $translator->trans(
                                'contact_email.description',
                                [
                                    '%url%' => $request->getHost(),
                                    '%email%' => $contactRequest->getEmail(),
                                    '%name%' => $contactRequest->getName(),
                                    '%message%' => $contactRequest->getMessage(),
                                ],
                                'contact'
                            )
                        );
                    }
                }

                $this->displaySuccess($translator->trans('index.success.email_sent', [], 'contact'));

                return $createForm($createContactRequest());
            }
        );

        $arr['form'] = $form->createView();

        return $this->render('contact/index.html.twig', $arr);
    }
}
