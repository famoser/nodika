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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class NewBaseController extends AbstractController
{
    protected function displayWarning(string $message): void
    {
        $this->displayFlash('warning', $message);
    }

    protected function displayError(string $message): void
    {
        $this->displayFlash('danger', $message);
    }

    protected function displaySuccess(string $message): void
    {
        $this->displayFlash('success', $message);
    }

    protected function displayInfo(string $message): void
    {
        $this->displayFlash('info', $message);
    }

    private function displayFlash(string $type, string $message): void
    {
        $this->addFlash($type, $message);
    }

    protected function getUser(): UserInterface|Doctor|null
    {
        return parent::getUser();
    }
}
