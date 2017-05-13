<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:50
 */

namespace AppBundle\Controller\Base;

use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use AppBundle\Helper\CsvFileHelper;
use AppBundle\Helper\FlashMessageHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
    protected function displaySuccess($message)
    {
        $this->get('session')->getFlashBag()->set(FlashMessageHelper::SUCCESS_MESSAGE, $message);
    }

    /**
     * displays the default form error
     */
    protected function displayFormValidationError()
    {
        $this->displayError($this->get("translator")->trans("error.form_validation_failed", [], "common"));
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
}