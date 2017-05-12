<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/05/2017
 * Time: 15:58
 */

namespace AppBundle\Service\Interfaces;


use Symfony\Component\Form\Form;

interface ExchangeServiceInterface
{
    /**
     * @param Form $createForm
     * @return \string[]
     */
    public function getCsvHeader(Form $createForm);

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success
     *
     * @param Form $createForm
     * @param Form $importForm
     * @return boolean
     */
    public function importCsv(Form $createForm, Form $importForm);
}