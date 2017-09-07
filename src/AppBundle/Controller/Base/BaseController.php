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
use AppBundle\Entity\Person;
use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Helper\CsvFileHelper;
use AppBundle\Helper\FlashMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaseController extends Controller
{
    /**
     * @param string $message the translation message to display
     */
    protected function displayError($message)
    {
        $this->get('session')->getFlashBag()->set(FlashMessageHelper::ERROR_MESSAGE, $message);
    }

    /**
     * @param string $message the translation message to display
     */
    protected function displayDanger($message)
    {
        $this->get('session')->getFlashBag()->set(FlashMessageHelper::DANGER_MESSAGE, $message);
    }

    /**
     * @param string $message the translation message to display
     */
    protected function displaySuccess($message)
    {
        $this->get('session')->getFlashBag()->set(FlashMessageHelper::SUCCESS_MESSAGE, $message);
    }

    /**
     * displays the default form error
     */
    protected function displayFormValidationError()
    {
        $this->displayError($this->get("translator")->trans("error.form_validation_failed", [], "common_form"));
    }

    /**
     * @return Person
     */
    protected function getPerson()
    {
        $user = $this->getUser();
        return $user->getPerson();
    }

    /**
     * @return FrontendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param array $header
     * @param array $data
     * @return StreamedResponse
     */
    protected function renderCsv($filename, $header, $data)
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($header, $data) {
            $handle = fopen('php://output', 'w+');

            // Add the header of the CSV file
            fputcsv($handle, $header, CsvFileHelper::DELIMITER);

            //add the data
            foreach ($data as $row) {
                fputcsv(
                    $handle, // The file pointer
                    $row, // The fields
                    CsvFileHelper::DELIMITER // The delimiter
                );
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param $entity
     * @return FormInterface
     */
    protected function handleDoctrineForm(FormInterface $form, Request $request, BaseEntity $entity)
    {
        return $this->handleForm($form, $request, $entity, function ($form, $entity) {
            /* @var FormInterface $form */
            /* @var BaseEntity $entity */

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            return $form;
        });
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param BaseEntity $entity
     * @param $onSuccessCallable
     * @return FormInterface
     */
    protected function handleDoctrineFormWithCustomOnSuccess(FormInterface $form, Request $request, BaseEntity $entity, $onSuccessCallable)
    {
        return $this->handleForm($form, $request, $entity, function ($form, $entity) use ($onSuccessCallable) {
            /* @var FormInterface $form */
            /* @var BaseEntity $entity */

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            return $onSuccessCallable ($form, $entity);
        });
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param BaseEntity $entity
     * @param callable $onRemoveCallable
     * @param callable $beforeRemoveCallable
     * @return FormInterface
     */
    protected function handleDoctrineRemove(FormInterface $form, Request $request, BaseEntity $entity, $onRemoveCallable, $beforeRemoveCallable = null)
    {
        return $this->handleForm($form, $request, $entity, function ($form, $entity) use ($onRemoveCallable, $beforeRemoveCallable) {
            /* @var FormInterface $form */
            /* @var BaseEntity $entity */

            $em = $this->getDoctrine()->getManager();
            $beforeRemoveCallable($entity, $em);
            $em->remove($entity);
            $em->flush();
            return $onRemoveCallable ($form, $entity);
        });
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param $entity
     * @param callable $callable
     * @return FormInterface
     */
    protected function handleForm(FormInterface $form, Request $request, $entity, $callable)
    {
        $form->setData($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                return $callable($form, $entity);
            } else {
                $this->displayFormValidationError();
            }
        }
        return $form;
    }

    /**
     * saves entity to database
     *
     * @param BaseEntity $entity
     * @param BaseEntity|null $entity2
     * @param BaseEntity|null $entity3
     */
    protected function fastSave($entity, $entity2 = null, $entity3 = null)
    {
        $mgr = $this->getDoctrine()->getManager();
        $mgr->persist($entity);
        if ($entity2 != null)
            $mgr->persist($entity2);
        if ($entity3 != null)
            $mgr->persist($entity3);
        $mgr->persist($entity);
        $mgr->flush();
    }
}