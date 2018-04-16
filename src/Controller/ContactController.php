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
use App\Entity\Settings;
use App\Form\Model\ContactRequest\ContactRequestType;
use App\Model\ContactRequest;
use App\Service\EmailService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/contact")
 */
class ContactController extends BaseFormController
{
    /**
     * @Route("/", name="contact_index")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailService $emailService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator, EmailService $emailService)
    {
        $contactRequest = new ContactRequest();
        $form = $this->handleForm(
            $this->createForm(ContactRequestType::class)
                ->add("form.send", SubmitType::class),
            $request,
            function () use ($request, $contactRequest, $translator, $emailService) {
                $setting = $this->getDoctrine()->getRepository(Settings::class)->findSingle();
                /* @var FormInterface $form */
                $emailService->sendTextEmail(
                    $setting->getSupportMail(),
                    $translator->trans("contact_email.subject", [], "contact"),
                    $translator->trans(
                        "contact_email.description",
                        [
                            "%url%" => $request->getHost(),
                            "%email%" => $contactRequest->getEmail(),
                            "%name%" => $contactRequest->getName(),
                            "%message%" => $contactRequest->getMessage()
                        ],
                        "contact"
                    )
                );

                $this->displaySuccess($translator->trans('index.success.email_sent', [], 'contact'));

                return $this->createForm(ContactRequestType::class);
            }
        );

        $arr["form"] = $form->createView();
        return $this->render('contact/index.html.twig', $arr);
    }
}
