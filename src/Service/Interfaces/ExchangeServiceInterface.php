<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Model\Form\ImportFileModel;
use Closure;
use Symfony\Component\Form\FormInterface;

interface ExchangeServiceInterface
{
    /**
     * @param FormInterface $createForm
     *
     * @return \string[]
     */
    public function getCsvHeader(FormInterface $createForm);

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success.
     *
     * @param FormInterface $createForm
     * @param Closure $createNewEntityClosure
     * @param ImportFileModel $importFileModel
     *
     * @return bool
     */
    public function importCsv(FormInterface $createForm, $createNewEntityClosure, ImportFileModel $importFileModel);

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success.
     *
     * @param Closure $entitySetClosure
     * @param Closure $validateHeaderClosure
     * @param ImportFileModel $importFileModel
     *
     * @return bool
     */
    public function importCsvAdvanced($entitySetClosure, $validateHeaderClosure, ImportFileModel $importFileModel);
}
