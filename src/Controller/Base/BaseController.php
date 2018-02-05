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

use App\Entity\Base\BaseEntity;
use App\Entity\FrontendUser;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Enum\SubmitButtonType;
use App\Helper\CsvFileHelper;
use App\Helper\NamingHelper;
use App\Helper\StaticMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BaseController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['kernel' => KernelInterface::class];
    }

    /**
     * @param $type
     * @param $submitButtonType
     * @param array $data
     * @param array $options
     *
     * @return FormInterface
     */
    public function createCrudForm($type, $submitButtonType, $data = null, array $options = [])
    {
        return $this->createForm($type, $data, [StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION => $submitButtonType] + $options);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param BaseEntity $data
     * @param int $submitButtonType
     * @param $onSuccessCallable
     * @param array $formOptions
     *
     * @return FormInterface
     */
    public function handleCrudForm(Request $request, TranslatorInterface $translator, BaseEntity $data, $submitButtonType, $onSuccessCallable = null, $formOptions = [])
    {
        $formType = NamingHelper::classToCrudFormType(get_class($data), SubmitButtonType::REMOVE === $submitButtonType);
        $myOnSuccessCallable = function ($form, $entity) use ($onSuccessCallable, $submitButtonType, $translator) {
            if (SubmitButtonType::CREATE === $submitButtonType) {
                $this->displaySuccess($translator->trans('successful.add', [], 'common_form'));
            } elseif (SubmitButtonType::EDIT === $submitButtonType) {
                $this->displaySuccess($translator->trans('successful.save', [], 'common_form'));
            } elseif (SubmitButtonType::REMOVE === $submitButtonType) {
                $this->displaySuccess($translator->trans('successful.remove', [], 'common_form'));
            }

            if (is_callable($onSuccessCallable)) {
                return $onSuccessCallable($form, $entity);
            }

            return $form;
        };

        $myForm = $this->createForm($formType, $data, [StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION => $submitButtonType] + $formOptions);
        if (SubmitButtonType::REMOVE === $submitButtonType) {
            return $this->handleFormDoctrineRemove(
                $myForm,
                $request,
                $translator,
                $data,
                $myOnSuccessCallable
            );
        }

        return $this->handleFormDoctrinePersist(
            $myForm,
            $request,
            $translator,
            $data,
            $myOnSuccessCallable
        );
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displaySuccess($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_SUCCESS, $message, $link);
    }

    /**
     * @param $type
     * @param $message
     * @param string $link
     */
    private function displayFlash($type, $message, $link = null)
    {
        if (null !== $link) {
            $message = '<a href="' . $link . '">' . $message . '</a>';
        }
        $this->get('session')->getFlashBag()->set($type, $message);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param BaseEntity $entity
     * @param callable $onRemoveCallable with $form & $entity arguments
     * @param callable $beforeRemoveCallable with $form & $entity arguments
     *
     * @return FormInterface
     */
    protected function handleFormDoctrineRemove(FormInterface $form, Request $request, TranslatorInterface $translator, BaseEntity $entity, $onRemoveCallable, $beforeRemoveCallable = null)
    {
        return $this->handleForm($form, $request, $translator, $entity, function ($form, $entity) use ($onRemoveCallable, $beforeRemoveCallable, $translator) {
            /* @var FormInterface $form */
            /* @var BaseEntity $entity */
            $em = $this->getDoctrine()->getManager();
            if (is_callable($beforeRemoveCallable)) {
                $beforeRemoveCallable($form, $entity, $em);
            }
            $em->remove($entity);
            $em->flush();

            return $onRemoveCallable($form, $entity);
        });
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param $entity
     * @param callable $callable with $form & $entity arguments
     *
     * @return FormInterface
     */
    protected function handleForm(FormInterface $form, Request $request, TranslatorInterface $translator, $entity, $callable)
    {
        $form->setData($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                return $callable($form, $entity);
            }
            $this->displayFormValidationError($translator);
        }

        return $form;
    }

    /**
     * displays the default form error.
     *
     * @param TranslatorInterface $translator
     */
    protected function displayFormValidationError(TranslatorInterface $translator)
    {
        $this->displayError($translator->trans('error.form_validation_failed', [], 'common_form'));
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayError($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_ERROR, $message, $link);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param BaseEntity $entity
     * @param callable $onSuccessCallable with $form & $entity arguments
     *
     * @return FormInterface
     */
    protected function handleFormDoctrinePersist(FormInterface $form, Request $request, TranslatorInterface $translator, BaseEntity $entity, $onSuccessCallable = null)
    {
        if (is_callable($onSuccessCallable)) {
            $myCallable = function ($form, $entity) use ($onSuccessCallable) {
                /* @var FormInterface $form */
                /* @var BaseEntity $entity */
                $this->fastSave($entity);

                return $onSuccessCallable($form, $entity);
            };
        } else {
            $myCallable = function ($form, $entity) use ($onSuccessCallable) {
                /* @var FormInterface $form */
                /* @var BaseEntity $entity */
                $this->fastSave($entity);

                return $form;
            };
        }

        return $this->handleForm($form, $request, $translator, $entity, $myCallable);
    }

    /**
     * saves entity to database.
     *
     * @param BaseEntity[] $entities
     */
    protected function fastSave(...$entities)
    {
        $mgr = $this->getDoctrine()->getManager();
        foreach ($entities as $item) {
            $mgr->persist($item);
        }
        $mgr->flush();
    }

    /**
     * get the parameter.
     *
     * remove this method as soon as possible
     * here because of missing getParameter call in AbstractController, should be back in release 4.1
     * clean up involves:
     *  remove this method
     *  remove getSubscribedServices override
     *  remove file config/packages/parameters.yml
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getParameter(string $name)
    {
        return $this->get('kernel')->getContainer()->getParameter($name);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayDanger($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_DANGER, $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayInfo($message, $link = null)
    {
        return $this->displayFlash(StaticMessageHelper::FLASH_INFO, $message, $link);
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
     *
     * @return StreamedResponse
     */
    protected function renderCsv($filename, $data, $header = null)
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($header, $data) {
            $handle = fopen('php://output', 'w+');

            //UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=,\n");

            if (is_array($header)) {
                // Add the header of the CSV file
                fputcsv($handle, $header, CsvFileHelper::DELIMITER);
            }

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
     * removes entity to database.
     *
     * @param BaseEntity[] $entities
     */
    protected function fastRemove(...$entities)
    {
        $mgr = $this->getDoctrine()->getManager();
        foreach ($entities as $item) {
            $mgr->remove($item);
        }
        $mgr->flush();
    }

    /**
     * @param Organisation $organisation
     * @param int $applicationEventType
     *
     * @return bool
     */
    protected function getHasEventOccurred(Organisation $organisation, $applicationEventType)
    {
        return $this->getDoctrine()->getRepository('App:ApplicationEvent')->hasEventOccurred($organisation, $applicationEventType);
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param string $backUrl
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    protected function renderWithBackUrl($view, array $parameters, $backUrl, Response $response = null)
    {
        $parameters['show_dashboard'] = $this->getShowDashboard($backUrl);
        $parameters['back_url'] = $backUrl;

        return parent::render($view, $parameters, $response);
    }

    /**
     * @param string|null $backUrl
     *
     * @return bool
     */
    private function getShowDashboard($backUrl = null)
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        return $this->getUser() instanceof FrontendUser && 'dashboard_index' !== $request->get('_route') && '/dashboard/' !== $backUrl;
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param string $justification why no backbutton
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    protected function renderNoBackUrl($view, array $parameters, $justification, Response $response = null)
    {
        $parameters['show_dashboard'] = $this->getShowDashboard();

        return parent::render($view, $parameters, $response);
    }
}
