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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ExchangeService implements ExchangeServiceInterface
{
    /* @var TranslatorInterface $translator */
    private $translator;
    /* @var FlashBagInterface $flashBag */
    private $flashBag;

    /**
     * ExchangeService constructor.
     * @param TranslatorInterface $translator
     * @param FlashBagInterface $flashBag
     */
    public function __construct(TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
    }

    /**
     * @param Form $createForm
     * @return \string[]
     */
    public function getCsvHeader(Form $createForm)
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
     * @param ImportFileModel $importFileModel
     * @return boolean
     */
    public function importCsv(FormInterface $createForm, ImportFileModel $importFileModel)
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
            while (($data = fgetcsv($handle, null, CsvFileHelper::DELIMITER)) !== FALSE) {
                if ($row++ == 1) {
                    //validate header (poorly, but should be enough for the normal user)
                    if (count($data) != count($header)) {
                        $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_open_failed", [], "import"));
                        return false;
                    }
                    continue;
                }
                $keyVal = [];
                //transfer array to key-value
                for ($i = 0; $i < count($data) && $i < count($header); $i++) {
                    $keyVal[$header[$i]] = $data[$i];
                }
                $createForm->setData($keyVal);
            }
            fclose($handle);
            return true;
        } else {
            $this->flashBag->set(FlashMessageHelper::ERROR_MESSAGE, $this->translator->trans("error.file_open_failed", [], "import"));
        }

        return false;
    }
}