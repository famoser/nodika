<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Form;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportFileModel
{
    /* @var string $uploadFolderPath */
    private $uploadFolderPath;

    /**
     * ImportFileModel constructor.
     *
     * @param string $uploadFolderPath
     */
    public function __construct($uploadFolderPath)
    {
        $this->uploadFolderPath = $uploadFolderPath;
    }

    /* @var UploadedFile $file */
    private $file;

    /* @var boolean $isCorrectFormat */
    private $isCorrectFormat;

    /* @var string $fileSrc */
    private $fileSrc;

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile|null $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function getIsCorrectFormat()
    {
        return $this->isCorrectFormat;
    }

    /**
     * @param bool $isCorrectFormat
     */
    public function setIsCorrectFormat($isCorrectFormat)
    {
        $this->isCorrectFormat = $isCorrectFormat;
    }

    /**
     * Manages the copying of the file to the relevant place on the server.
     */
    public function uploadFile()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return false;
        }

        $path = $_SERVER['DOCUMENT_ROOT'].$this->uploadFolderPath;
        // move takes the target directory and target filename as params
        $newFileName = uniqid().'.'.$this->getFile()->guessExtension();
        $this->getFile()->move(
            $path,
            $newFileName
        );

        // set the path property to the filename where you've saved the file
        $this->fileSrc = $newFileName;

        // clean up the file property as you won't need it anymore
        $this->setFile(null);

        return true;
    }

    /**
     * @return string
     */
    public function getFullFilePath()
    {
        return $_SERVER['DOCUMENT_ROOT'].$this->uploadFolderPath.'/'.$this->fileSrc;
    }
}
