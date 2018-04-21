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

use App\Controller\Base\BaseFormController;
use App\Entity\EventGeneration;
use App\Form\EventGeneration\BasicDataType;
use App\Form\EventGeneration\ChooseRecipientsType;
use App\Form\EventGeneration\SaveType;
use App\Form\EventGenerationConflictAvoid\EventGenerationConflictAvoidType;
use App\Helper\DateTimeFormatter;
use App\Model\Breadcrumb;
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
     * @param Request $request
     * @param EventGeneration $generation
     * @param $formClass
     * @param $onAnswerSubmitted
     * @return array|Response
     */
    private function handleStepForm(Request $request, EventGeneration $generation, $formClass, $onAnswerSubmitted)
    {
        $saved = false;
        $form = $this->handleForm(
            $this->createForm($formClass, $generation),
            $request,
            function ($form) use (&$saved, $generation) {
                $this->fastSave($generation);
                $saved = true;
                return $form;
            }
        );

        if ($saved) {
            return $onAnswerSubmitted;
        }


        $arr["form"] = $form;

        return $arr;
    }

    /**
     * @Route("/{generation}/basic", name="administration_generate_basic")
     *
     * @param Request $request
     * @param EventGeneration $generation
     * @return Response
     */
    public function basicAction(Request $request, EventGeneration $generation)
    {
        $arr = $this->handleStepForm(
            $request,
            $generation,
            BasicDataType::class,
            function () use ($generation) {
                return $this->redirectToRoute("administration_generate_recipients", ["generation" => $generation->getId()]);
            }
        );

        if ($arr instanceof Response) {
            return $arr;
        }

        return $this->render('administration/generate/basic.html.twig', $arr);
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
        $arr = $this->handleStepForm(
            $request,
            $generation,
            ChooseRecipientsType::class,
            function () use ($generation) {
                return $this->redirectToRoute("administration_generate_conflicts", ["generation" => $generation->getId()]);
            }
        );

        return $this->render('administration/generate/recipients.html.twig', $arr);
    }

    /**
     * @Route("/{generation}/conflicts", name="administration_generate_conflicts")
     *
     * @param Request $request
     * @param EventGeneration $generation
     * @return Response
     */
    public function conflictsAction(Request $request, EventGeneration $generation)
    {
        $arr = $this->handleStepForm(
            $request,
            $generation,
            EventGenerationConflictAvoidType::class,
            function () use ($generation) {
                return $this->redirectToRoute("administration_generate_weights", ["generation" => $generation->getId()]);
            }
        );

        return $this->render('administration/generate/conflicts.html.twig', $arr);
    }

    /**
     * @Route("/{generation}/weights", name="administration_generate_weights")
     *
     * @param EventGeneration $generation
     * @return Response
     */
    public function weightsAction(Request $request, EventGeneration $generation)
    {
        $arr = $this->handleStepForm(
            $request,
            $generation,
            EventGenerationConflictAvoidType::class,
            function () use ($generation) {
                return $this->redirectToRoute("administration_generate_save", ["generation" => $generation->getId()]);
            }
        );

        return $this->render('administration/generate/weights.html.twig', $arr);
    }

    /**
     * @Route("/{generation}/save", name="administration_generate_save")
     *
     * @param EventGeneration $generation
     * @return Response
     */
    public function saveAction(Request $request, EventGeneration $generation)
    {
        $arr = $this->handleStepForm(
            $request,
            $generation,
            SaveType::class,
            function () use ($generation) {
                return $this->redirectToRoute("administration_generate_index", ["generation" => $generation->getId()]);
            }
        );

        return $this->render('administration/generate/save.html.twig', $arr);
    }

    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl("administration_index"),
                $this->getTranslator()->trans("index.title", [], "administration")
            ),
            new Breadcrumb(
                $this->generateUrl("administration_generations"),
                $this->getTranslator()->trans("frontend_users.title", [], "administration")
            )
        ];
    }
}
