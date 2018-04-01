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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class BaseFormController extends BaseDoctrineController
{
    /**
     * inject the translator service
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class];
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->get("translator");
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param callable $onValidCallable with $form ass an argument
     *
     * @return FormInterface
     */
    protected function handleForm(FormInterface $form, Request $request, $onValidCallable)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                return $onValidCallable($form);
            }

            $this->displayError(
                $this->getTranslator()->trans('error.form_validation_failed', [], 'common_form')
            );
        }

        return $form;
    }

    /**
     * creates a "create" form
     *
     * @param Request $request
     * @param BaseEntity $defaultEntity
     * @param callable|null $beforeCreateCallable
     * @return FormInterface
     */
    protected function handleCreateForm(Request $request, BaseEntity $defaultEntity, $beforeCreateCallable = null)
    {
        $translator = $this->getTranslator();

        if (!is_callable($beforeCreateCallable)) {
            $beforeCreateCallable = function () {
                return true;
            };
        }

        return $this->handlePersistFormInternal(
            $request,
            $defaultEntity,
            $this->classToFormType(get_class($defaultEntity)),
            $translator->trans("submit.create", [], "common_form"),
            $translator->trans('successful.create', [], 'common_form'),
            $beforeCreateCallable
        );
    }

    /**
     * creates a "create" form
     *
     * @param Request $request
     * @param BaseEntity $entity
     * @param callable|null $beforeUpdateCallable
     * @return FormInterface
     */
    protected function handleUpdateForm(Request $request, BaseEntity $entity, $beforeUpdateCallable = null)
    {
        $translator = $this->getTranslator();

        if (!is_callable($beforeUpdateCallable)) {
            $beforeUpdateCallable = function () {
                return true;
            };
        }

        return $this->handlePersistFormInternal(
            $request,
            $entity,
            $this->classToFormType(get_class($entity)),
            $translator->trans("submit.update", [], "common_form"),
            $translator->trans('successful.update', [], 'common_form'),
            $beforeUpdateCallable
        );
    }

    /**
     * creates a "create" form
     *
     * @param Request $request
     * @param BaseEntity $entity
     * @param callable $beforeRemoveCallable called after successful submit, before entity is removed. return true to continue removal
     * @return FormInterface
     */
    protected function handleRemoveForm(Request $request, BaseEntity $entity, $beforeRemoveCallable = null)
    {
        $translator = $this->getTranslator();

        return $this->handleRemoveFormInternal(
            $request,
            $entity,
            $this->classToFormType(get_class($entity), "Delete"),
            $translator->trans("submit.delete", [], "common_form"),
            $translator->trans('successful.delete', [], 'common_form'),
            $beforeRemoveCallable ??
            function () {
                return true;
            }
        );
    }


    /**
     * persist the entity to the database if submitted successfully
     * @param Request $request
     * @param BaseEntity $entity
     * @param string $formType namespace of form type to use
     * @param string $buttonLabel label of button
     * @param string $successText content of text displayed if successful
     * @param callable $beforePersistCallable
     * @return FormInterface the constructed form
     */
    private function handlePersistFormInternal(Request $request, BaseEntity $entity, $formType, $buttonLabel, $successText, $beforePersistCallable)
    {
        $myOnSuccessCallable = function ($form) use ($entity, $successText, $beforePersistCallable) {
            $manager = $this->getDoctrine()->getManager();

            if ($beforePersistCallable($manager) !== false) {
                $manager->persist($entity);
                $manager->flush();
                $this->displaySuccess($successText);
            }

            return $form;
        };

        $myForm = $this->handleForm(
            $this->createForm($formType, $entity)
                ->add("submit", SubmitType::class, ["label" => $buttonLabel, "translation_domain" => false]),
            $request,
            $myOnSuccessCallable
        );
        return $myForm;
    }

    /**
     * persist the entity to the database if submitted successfully
     * @param Request $request
     * @param BaseEntity $entity
     * @param string $formType namespace of form type to use
     * @param string $buttonLabel label of button
     * @param string $successText content of text displayed if successful
     * @param callable $beforeRemoveCallable called after successful submit, before entity is removed. return true to continue removal
     * @return FormInterface the constructed form
     */
    private function handleRemoveFormInternal(Request $request, BaseEntity $entity, $formType, $buttonLabel, $successText, $beforeRemoveCallable)
    {
        $myOnSuccessCallable = function ($form) use ($entity, $successText, $beforeRemoveCallable) {
            $manager = $this->getDoctrine()->getManager();

            if ($beforeRemoveCallable($entity, $manager) !== false) {
                $manager->remove($entity);
                $manager->flush();
                $this->displaySuccess($successText);
            }

            return $form;
        };

        $myForm = $this->handleForm(
            $this->createForm($formType, $entity)
                ->add("submit", SubmitType::class, ["label" => $buttonLabel, "translation_domain" => false]),
            $request,
            $myOnSuccessCallable
        );
        return $myForm;
    }

    /**
     * produces App\Form\MyClassName\MyClassNameType from Famoser\Class\MyClassName
     * if $isRemoveType is true then the remove form is returned.
     *
     * @param string $classWithNamespace
     *
     * @param string $prepend is prepended to class name
     * @return string
     */
    private function classToFormType($classWithNamespace, $prepend = '')
    {
        $className = mb_substr($classWithNamespace, mb_strrpos($classWithNamespace, '\\') + 1);

        return 'App\\Form\\' . $className . '\\' . $prepend . $className . 'Type';
    }
}
