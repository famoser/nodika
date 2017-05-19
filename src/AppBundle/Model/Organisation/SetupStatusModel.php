<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 22:21
 */

namespace AppBundle\Model\Organisation;


class SetupStatusModel
{
    private $stepOneDone;
    private $stepTwoDone;
    private $stepThreeDone;
    private $stepFourDone;

    /**
     * @return boolean
     */
    public function getStepOneDone()
    {
        return $this->stepOneDone;
    }

    /**
     * @param boolean $stepOneDone
     */
    public function setStepOneDone($stepOneDone)
    {
        $this->stepOneDone = $stepOneDone;
    }

    /**
     * @return boolean
     */
    public function getStepTwoDone()
    {
        return $this->stepTwoDone;
    }

    /**
     * @param boolean $stepTwoDone
     */
    public function setStepTwoDone($stepTwoDone)
    {
        $this->stepTwoDone = $stepTwoDone;
    }

    /**
     * @return boolean
     */
    public function getStepThreeDone()
    {
        return $this->stepThreeDone;
    }

    /**
     * @param boolean $stepThreeDone
     */
    public function setStepThreeDone($stepThreeDone)
    {
        $this->stepThreeDone = $stepThreeDone;
    }

    /**
     * @return boolean
     */
    public function getStepFourDone()
    {
        return $this->stepFourDone;
    }

    /**
     * @param boolean $stepFourDone
     */
    public function setStepFourDone($stepFourDone)
    {
        $this->stepFourDone = $stepFourDone;
    }

    /**
     * @return bool
     */
    public function getAllDone()
    {
        return $this->getStepOneDone() && $this->getStepTwoDone() && $this->getStepThreeDone() && $this->getStepFourDone();
    }
}