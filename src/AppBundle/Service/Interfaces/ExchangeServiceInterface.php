<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/05/2017
 * Time: 15:58
 */

namespace AppBundle\Service\Interfaces;


use AppBundle\Model\Form\ImportFileModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

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
     * @param FormInterface $createForm
     * @param ImportFileModel $importFileModel
     * @return boolean
     */
    public function importCsv(FormInterface $createForm, ImportFileModel $importFileModel);
}