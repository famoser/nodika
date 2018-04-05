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

use App\Controller\Base\BaseController;
use App\Controller\Base\BaseFormController;
use App\Entity\EventGeneration;
use App\Entity\EventLine;
use App\Entity\Organisation;
use App\Helper\DateTimeFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/generate")
 * @Security("has_role('ROLE_USER')")
 */
class GenerateController extends BaseFormController
{
    /**
     * @Route("/", name="administration_generate_index")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        $now = new \DateTime();

        $generation = new EventGeneration();
        $generation->setName($translator->trans("entity.default_name", ["%date%" => $now->format(DateTimeFormatter::DATE_TIME_FORMAT)], "entity_event_generation"));
        $generation->registerChangeBy($this->getUser());

        $submitted = false;
        $form = $this->handleCreateForm(
            $request,
            $generation,
            function () use (&$submitted) {
                $submitted = true;
                return true;
            }
        );

        if ($submitted) {
            return $this->redirectToRoute("administration_generate_basic", ["generation" => $generation->getId()]);
        }

        $arr["form"] = $form;

        $generations = $this->getDoctrine()->getRepository(EventGeneration::class)->findAll();
        $arr["generations"] = $generations;

        return $this->render('administration/generate/index.html.twig', $arr);
    }

    /**
     * @Route("/{generation}/basic", name="administration_generate_basic")
     *
     * @param Request $request
     * @param EventGeneration $generation
     * @return Response
     */
    public function chooseAction(Request $request, EventGeneration $generation)
    {
        $saved = false;
        $form = $this->handleCreateForm(
            $request,
            $generation,
            function () use (&$saved) {
                $saved = true;
                return true;
            }
        );

        if ($saved) {
            return $this->redirectToRoute("administration_generate_basic", ["generation" => $generation->getId()]);
        }

        return $this->render(
            'administration/generate/basic.html.twig'
        );
    }

    /**
     * @Route("/{generation}/recipients", name="administration_generate_recipients")
     *
     * @param Request $request
     * @param EventGeneration $generation
     * @return Response
     */
    public function recipientAction(Request $request, EventGeneration $generation)
    {
        return $this->render(
            'administration/generate/recipients.html.twig'
        );
    }

    /**
     * @Route("/{generation}/conflicts", name="administration_generate_conflicts")
     *
     * @param EventGeneration $generation
     * @return Response
     */
    public function conflictsAction(Request $request, EventGeneration $generation)
    {
        return $this->render(
            'administration/generate/conflicts.html.twig'
        );
    }

    /**
     * @Route("/{generation}/weights", name="administration_generate_weights")
     *
     * @param EventGeneration $generation
     * @return Response
     */
    public function weightsAction(Request $request, EventGeneration $generation)
    {
        return $this->render(
            'administration/generate/weights.html.twig'
        );
    }

    /**
     * @Route("/{generation}/save", name="administration_generate_save")
     *
     * @param EventGeneration $generation
     * @return Response
     */
    public function saveAction(Request $request, EventGeneration $generation)
    {
        return $this->render(
            'administration/generate/save.html.twig'
        );
    }
}
