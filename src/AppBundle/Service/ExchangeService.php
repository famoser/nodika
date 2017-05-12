<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/05/2017
 * Time: 15:57
 */

namespace AppBundle\Service;


use AppBundle\Service\Interfaces\ExchangeServiceInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Translation\TranslatorInterface;

class ExchangeService implements ExchangeServiceInterface
{
    /* @var TranslatorInterface $translator */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
     * @param Form $createForm
     * @param Form $importForm
     * @return boolean
     */
    public function importCsv(Form $createForm, Form $importForm)
    {
        $header = [];
        foreach ($createForm as $group => $item) {
            if ($item instanceof Form) {
                foreach ($item->getIterator() as $name => $element) {
                    $header[] = $name;
                }
            }
        }


        return true;
    }
}