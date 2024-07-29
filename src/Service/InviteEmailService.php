<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Doctor;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InviteEmailService
{
    /**
     * @var EmailServiceInterface
     */
    private $emailService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine, EmailServiceInterface $emailService, TranslatorInterface $translator, UrlGeneratorInterface $generator)
    {
        $this->doctrine = $doctrine;
        $this->emailService = $emailService;
        $this->translator = $translator;
        $this->urlGenerator = $generator;
    }

    /**
     * @throws \Exception
     */
    public function inviteDoctor(Doctor $doctor)
    {
        // map clinics to clinic name array
        $clinics = [];
        foreach ($doctor->getClinics() as $clinic) {
            $clinics[] = $clinic->getName();
        }

        // sent invite email
        $this->emailService->sendActionEmail(
            $doctor->getEmail(),
            $this->translator->trans('invite.email.subject', [], 'administration_doctor'),
            $this->translator->trans('invite.email.message', ['%email%' => $doctor->getEmail(), '%clinics%' => implode(', ', $clinics)], 'administration_doctor'),
            $this->translator->trans('invite.email.action_text', [], 'administration_doctor'),
            $this->urlGenerator->generate('invite_doctor', ['guid' => $doctor->invite()], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        // save changes
        $manager = $this->doctrine->getManager();
        $manager->persist($doctor);
        $manager->flush();
    }
}
