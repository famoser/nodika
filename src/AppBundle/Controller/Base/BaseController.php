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
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Person;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Helper\CsvFileHelper;
use AppBundle\Helper\NamingHelper;
use AppBundle\Helper\StaticMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaseController extends Controller
{
    /**
     * @param $type
     * @param $submitButtonType
     * @param null $data
     * @param array $options
     * @return \Symfony\Component\Form\Form
     */
    public function createCrudForm($type, $submitButtonType, $data = null, array $options = array())
    {
        return $this->createForm($type, $data, [StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION => $submitButtonType] + $options);
    }


    /**
     * @param Request $request
     * @param BaseEntity $data
     * @param int $submitButtonType
     * @param $onSuccessCallable
     * @param array $formOptions
     * @return FormInterface
     */
    public function handleCrudForm(Request $request, BaseEntity $data, $submitButtonType, $onSuccessCallable = null, $formOptions = [])
    {
        $formType = NamingHelper::classToCrudFormType(get_class($data), $submitButtonType == SubmitButtonType::REMOVE);
        $myOnSuccessCallable = function ($form, $entity) use ($onSuccessCallable, $submitButtonType) {
            $translator = $this->get("translator");
            if ($submitButtonType == SubmitButtonType::CREATE) {
                $this->displaySuccess($translator->trans("successful.add", [], "common_form"));
            } elseif ($submitButtonType == SubmitButtonType::EDIT) {
                $this->displaySuccess($translator->trans("successful.save", [], "common_form"));
            } elseif ($submitButtonType == SubmitButtonType::REMOVE) {
                $this->displaySuccess($translator->trans("successful.remove", [], "common_form"));
            }

            if (is_callable($onSuccessCallable)) {
                return $onSuccessCallable($form, $entity);
            }
            return $form;
        };

        $myForm = $this->createForm($formType, $data, [StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION => $submitButtonType] + $formOptions);
        if ($submitButtonType == SubmitButtonType::REMOVE) {
            return $this->handleFormDoctrineRemove(
                $myForm,
                $request,
                $data,
                $myOnSuccessCallable
            );
        } else {
            return $this->handleFormDoctrinePersist(
                $myForm,
                $request,
                $data,
                $myOnSuccessCallable
            );

        }
    }

    /**
     * @param $type
     * @param $message
     * @param null $link
     */
    private function displayFlash($type, $message, $link = null)
    {
        if ($link != null) {
            $message = '<a href="' . $link . '">' . $message . '</a>';
        }
        $this->get('session')->getFlashBag()->set($type, $message);
    }

    /**
     * @param string $message the translation message to display
     * @param null $link
     */
    protected function displayError($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_ERROR, $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param null $link
     */
    protected function displayDanger($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_DANGER, $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param null $link
     */
    protected function displaySuccess($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_SUCCESS, $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param null $link
     */
    protected function displayInfo($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_INFO, $message, $link);
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
        if ($user instanceof FrontendUser) {
            return $user->getPerson();
        }
        return null;
    }

    /**
     * @return FrontendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param $filename
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
     * @param BaseEntity $entity
     * @param callable $onSuccessCallable with $form & $entity arguments
     * @return FormInterface
     */
    protected function handleFormDoctrinePersist(FormInterface $form, Request $request, BaseEntity $entity, $onSuccessCallable = null)
    {
        if (is_callable($onSuccessCallable)) {
            $myCallable = function ($form, $entity) use ($onSuccessCallable) {
                /* @var FormInterface $form */
                /* @var BaseEntity $entity */
                $this->fastSave($entity);
                return $onSuccessCallable ($form, $entity);
            };
        } else {
            $myCallable = function ($form, $entity) use ($onSuccessCallable) {
                /* @var FormInterface $form */
                /* @var BaseEntity $entity */
                $this->fastSave($entity);
                return $form;
            };
        }
        return $this->handleForm($form, $request, $entity, $myCallable);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param BaseEntity $entity
     * @param callable $onRemoveCallable with $form & $entity arguments
     * @param callable $beforeRemoveCallable with $form & $entity arguments
     * @return FormInterface
     */
    protected function handleFormDoctrineRemove(FormInterface $form, Request $request, BaseEntity $entity, $onRemoveCallable, $beforeRemoveCallable = null)
    {
        return $this->handleForm($form, $request, $entity, function ($form, $entity) use ($onRemoveCallable, $beforeRemoveCallable) {
            /* @var FormInterface $form */
            /* @var BaseEntity $entity */
            $em = $this->getDoctrine()->getManager();
            if (is_callable($beforeRemoveCallable)) {
                $beforeRemoveCallable($form, $entity, $em);
            }
            $em->remove($entity);
            $em->flush();
            return $onRemoveCallable ($form, $entity);
        });
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param $entity
     * @param callable $callable with $form & $entity arguments
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
        $mgr->flush();
    }

    /**
     * removes entity to database
     *
     * @param BaseEntity $entity
     * @param BaseEntity|null $entity2
     * @param BaseEntity|null $entity3
     */
    protected function fastRemove($entity, $entity2 = null, $entity3 = null)
    {
        $mgr = $this->getDoctrine()->getManager();
        $mgr->remove($entity);
        if ($entity2 != null)
            $mgr->remove($entity2);
        if ($entity3 != null)
            $mgr->remove($entity3);
        $mgr->flush();
    }

    /**
     * @param Organisation $organisation
     * @param int $applicationEventType
     * @return bool
     */
    protected function getHasEventOccurred(Organisation $organisation, $applicationEventType)
    {
        return $this->getDoctrine()->getRepository("AppBundle:ApplicationEvent")->hasEventOccurred($organisation, $applicationEventType);
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param string $backUrl
     * @param Response $response A response instance
     * @return Response A Response instance
     */
    protected function render($view, array $parameters, $backUrl, Response $response = null)
    {
        $parameters["back_url"] = $backUrl;
        return parent::render($view, $parameters, $response);
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param string $justification why no backbutton
     * @param Response $response A response instance
     * @return Response A Response instance
     */
    protected function renderNoBackUrl($view, array $parameters, $justification, Response $response = null)
    {
        return parent::render($view, $parameters, $response);
    }
}