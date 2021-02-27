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

use App\Entity\Doctor;
use App\Model\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BaseController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                'kernel' => KernelInterface::class,
                'security.token_storage' => TokenStorageInterface::class,
                'translator' => TranslatorInterface::class,
            ];
    }

    /**
     * @return KernelInterface
     */
    private function getKernel()
    {
        return $this->get('kernel');
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**BaseFormController
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
        return $this->getKernel()->getContainer()->getParameter($name);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayError($message, $link = null)
    {
        $this->displayFlash('danger', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displaySuccess($message, $link = null)
    {
        $this->displayFlash('success', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayDanger($message, $link = null)
    {
        $this->displayFlash('danger', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayInfo($message, $link = null)
    {
        $this->displayFlash('info', $message, $link);
    }

    /**
     * @param $type
     * @param $message
     * @param string $link
     */
    private function displayFlash($type, $message, $link = null)
    {
        if (null !== $link) {
            $message = '<a href="'.$link.'">'.$message.'</a>';
        }
        $this->get('session')->getFlashBag()->set($type, $message);
    }

    /**
     * @return Doctor|null
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return Breadcrumb[]|array
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl('index_index'),
                $this->getTranslator()->trans('index.title', [], 'index')
            ),
        ];
    }

    /**
     * Renders a view.
     *
     * @param Breadcrumb[] $breadcrumbs
     */
    protected function render(string $view, array $parameters = [], Response $response = null, array $breadcrumbs = []): Response
    {
        $parameters['breadcrumbs'] = array_merge($this->getIndexBreadcrumbs(), $breadcrumbs);

        return parent::render($view, $parameters);
    }
}
