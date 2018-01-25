<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Helper\CsvFileHelper;
use App\Helper\NamingHelper;
use App\Helper\StaticMessageHelper;
use App\Model\Form\ImportFileModel;
use App\Service\Interfaces\ExchangeServiceInterface;
use Closure;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExchangeService implements ExchangeServiceInterface
{
    /* @var TranslatorInterface $translator */
    private $translator;
    /* @var FlashBagInterface $flashBag */
    private $flashBag;
    /* @var ValidatorInterface $validator */
    private $validator;
    /* @var RegistryInterface $registry */
    private $registry;

    /**
     * ExchangeService constructor.
     *
     * @param TranslatorInterface $translator
     * @param FlashBagInterface   $flashBag
     * @param ValidatorInterface  $validator
     * @param RegistryInterface   $registry
     */
    public function __construct(TranslatorInterface $translator, FlashBagInterface $flashBag, ValidatorInterface $validator, RegistryInterface $registry)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
        $this->validator = $validator;
        $this->registry = $registry;
    }

    /**
     * @param FormInterface $createForm
     *
     * @return \string[]
     */
    public function getCsvHeader(FormInterface $createForm)
    {
        $myArr = [];
        foreach ($createForm as $group => $item) {
            if ($item instanceof Form) {
                foreach ($item->getIterator() as $name => $element) {
                    $myName = NamingHelper::propertyToTranslation($name);
                    $myArr[] = $this->translator->trans($myName, [], $element->getConfig()->getOption('translation_domain'));
                }
            }
        }

        return $myArr;
    }

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success.
     *
     * @param FormInterface   $createForm
     * @param Closure         $createNewEntityClosure
     * @param ImportFileModel $importFileModel
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return bool
     */
    public function importCsv(FormInterface $createForm, $createNewEntityClosure, ImportFileModel $importFileModel)
    {
        $header = [];
        foreach ($createForm as $group => $item) {
            if ($item instanceof FormInterface) {
                foreach ($item->getIterator() as $name => $element) {
                    $header[] = $name;
                }
            }
        }

        if (!$importFileModel->uploadFile()) {
            $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_upload_failed', [], 'import'));

            return false;
        }

        $row = 1;
        if (false !== ($handle = fopen($importFileModel->getFullFilePath(), 'r'))) {
            $accessorNames = [];
            $em = $this->registry->getEntityManager();
            while (false !== ($data = fgetcsv($handle, null, CsvFileHelper::DELIMITER))) {
                if (1 === $row++) {
                    //validate header (poorly, but should be enough for the normal user)
                    if (count($data) !== count($header)) {
                        $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_wrong_format', [], 'import'));

                        return false;
                    }
                    for ($i = 0; $i < count($header); ++$i) {
                        $accessorNames[$i] = 'set'.mb_strtoupper(mb_substr($header[$i], 0, 1)).mb_substr($header[$i], 1);
                    }
                    continue;
                }
                $newEntry = $createNewEntityClosure();
                if (false === $newEntry) {
                    $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.creation_failure_occurred_at', ['%number%' => $row - 1], 'import'));

                    return false;
                }
                //transfer array to key-value
                for ($i = 0; $i < count($data) && $i < count($header); ++$i) {
                    $propName = $accessorNames[$i];
                    $newEntry->$propName($data[$i]);
                }
                $errors = $this->validator->validate($newEntry);
                if (0 === count($errors)) {
                    $em->persist($newEntry);
                } else {
                    $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.failure_occurred_at', ['%number%' => $row - 1, '%error%' => $errors[0]], 'import'));
                    fclose($handle);

                    return false;
                }
            }
            $em->flush();
            fclose($handle);
            if ($row - 2 <= 0) {
                $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_empty', [], 'import'));
            } else {
                $this->flashBag->set(StaticMessageHelper::FLASH_SUCCESS, $this->translator->trans('success.import_successful', ['%count%' => $row - 2], 'import'));
            }

            return true;
        }
        $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_open_failed', [], 'import'));

        return false;
    }

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success.
     *
     * @param Closure         $entitySetClosure
     * @param Closure         $validateHeaderClosure
     * @param ImportFileModel $importFileModel
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return bool
     */
    public function importCsvAdvanced($entitySetClosure, $validateHeaderClosure, ImportFileModel $importFileModel)
    {
        if (!$importFileModel->uploadFile()) {
            $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_upload_failed', [], 'import'));

            return false;
        }

        $row = 1;
        if (false !== ($handle = fopen($importFileModel->getFullFilePath(), 'r'))) {
            $em = $this->registry->getEntityManager();
            while (false !== ($data = fgetcsv($handle, null, CsvFileHelper::DELIMITER))) {
                if (1 === $row++) {
                    //validate header skipped
                    if (!$validateHeaderClosure($data)) {
                        $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_wrong_format', [], 'import'));

                        return false;
                    }
                    continue;
                }
                $newEntry = $entitySetClosure($data);
                if (false === $newEntry) {
                    $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.creation_failure_occurred_at', ['%number%' => $row - 1], 'import'));

                    return false;
                }
                $errors = $this->validator->validate($newEntry);
                if (0 === count($errors) && null !== $newEntry) {
                    $em->persist($newEntry);
                } else {
                    fclose($handle);
                    $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.failure_occurred_at', ['%number%' => $row - 1, '%error%' => $errors[0]], 'import'));

                    return false;
                }
            }
            $em->flush();
            fclose($handle);
            if ($row - 2 <= 0) {
                $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_empty', [], 'import'));
            } else {
                $this->flashBag->set(StaticMessageHelper::FLASH_SUCCESS, $this->translator->trans('success.import_successful', ['%count%' => $row - 2], 'import'));
            }

            return true;
        }
        $this->flashBag->set(StaticMessageHelper::FLASH_ERROR, $this->translator->trans('error.file_open_failed', [], 'import'));

        return false;
    }
}
