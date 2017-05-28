<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/05/2017
 * Time: 15:57
 */

namespace AppBundle\Service;


use AppBundle\Helper\CsvFileHelper;
use AppBundle\Helper\FlashMessageHelper;
use AppBundle\Model\Form\ImportFileModel;
use AppBundle\Service\Interfaces\ExchangeServiceInterface;
use Closure;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @param TranslatorInterface $translator
     * @param FlashBagInterface $flashBag
     * @param ValidatorInterface $validator
     * @param RegistryInterface $registry
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
     * @return \string[]
     */
    public function getCsvHeader(FormInterface $createForm)
    {
        $myArr = [];
        foreach ($createForm as $group => $item) {
            if ($item instanceof Form) {
                foreach ($item->getIterator() as $name => $element) {
                    $myArr[] = $this->translator->trans($name, [], $element->getConfig()->getOption("translation_domain"));
                }
            }
        }
        return $myArr;
    }

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success
     *
     * @param FormInterface $createForm
     * @param Closure $createNewEntityClosure
     * @param ImportFileModel $importFileModel
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
            $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_upload_failed", [], "import"));
            return false;
        }

        $row = 1;
        if (($handle = fopen($importFileModel->getFullFilePath(), "r")) !== false) {
            $accessorNames = [];
            $em = $this->registry->getEntityManager();
            while (($data = fgetcsv($handle, null, CsvFileHelper::DELIMITER)) !== FALSE) {
                if ($row++ == 1) {
                    //validate header (poorly, but should be enough for the normal user)
                    if (count($data) != count($header)) {
                        $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_open_failed", [], "import"));
                        return false;
                    }
                    for ($i = 0; $i < count($header); $i++) {
                        $accessorNames[$i] = "set" . strtoupper(substr($header[$i], 0, 1)) . substr($header[$i], 1);
                    }
                    continue;
                }
                $newEntry = $createNewEntityClosure();
                //transfer array to key-value
                for ($i = 0; $i < count($data) && $i < count($header); $i++) {
                    $propName = $accessorNames[$i];
                    $newEntry->$propName($data[$i]);
                }
                $errors = $this->validator->validate($newEntry);
                if (count($errors) == 0) {
                    $em->persist($newEntry);
                } else {
                    $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.failure_occurred_at", ["%number%" => $row - 1, "%error%" => $errors[0]], "import"));
                    fclose($handle);
                    return false;
                }
            }
            $em->flush();
            fclose($handle);
            return true;
        } else {
            $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_open_failed", [], "import"));
        }

        return false;
    }

    /**
     * imports the content of the csv file from the import form into the database and sets a flash message if an error occurred
     * returns true on success
     *
     * @param Closure $entitySetClosure
     * @param Closure $validateHeaderClosure
     * @param ImportFileModel $importFileModel
     * @return bool
     */
    public function importCsvAdvanced($entitySetClosure, $validateHeaderClosure, ImportFileModel $importFileModel)
    {
        if (!$importFileModel->uploadFile()) {
            $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_upload_failed", [], "import"));
            return false;
        }

        $row = 1;
        if (($handle = fopen($importFileModel->getFullFilePath(), "r")) !== false) {
            $em = $this->registry->getEntityManager();
            while (($data = fgetcsv($handle, null, CsvFileHelper::DELIMITER)) !== FALSE) {
                if ($row++ == 1) {
                    //validate header skipped
                    if ($validateHeaderClosure($data)) {
                        continue;
                    }
                }
                $newEntry = $entitySetClosure($data);
                $errors = $this->validator->validate($newEntry);
                if (count($errors) == 0 && $newEntry != null) {
                    $em->persist($newEntry);
                } else {
                    $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.failure_occurred_at", ["%number%" => $row - 1, "%error%" => $errors[0]], "import"));
                    fclose($handle);
                    return false;
                }
            }
            $em->flush();
            fclose($handle);
            return true;
        } else {
            $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_open_failed", [], "import"));
        }

        return false;
    }
}