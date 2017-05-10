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
     * @return mixed
     */
    public function getStepOneDone()
    {
        return $this->stepOneDone;
    }

    /**
     * @param mixed $stepOneDone
     */
    public function setStepOneDone($stepOneDone)
    {
        $this->stepOneDone = $stepOneDone;
    }

    /**
     * @return mixed
     */
    public function getStepTwoDone()
    {
        return $this->stepTwoDone;
    }

    /**
     * @param mixed $stepTwoDone
     */
    public function setStepTwoDone($stepTwoDone)
    {
        $this->stepTwoDone = $stepTwoDone;
    }

    /**
     * @return mixed
     */
    public function getStepThreeDone()
    {
        return $this->stepThreeDone;
    }

    /**
     * @param mixed $stepThreeDone
     */
    public function setStepThreeDone($stepThreeDone)
    {
        $this->stepThreeDone = $stepThreeDone;
    }

    /**
     * @return mixed
     */
    public function getStepFourDone()
    {
        return $this->stepFourDone;
    }

    /**
     * @param mixed $stepFourDone
     */
    public function setStepFourDone($stepFourDone)
    {
        $this->stepFourDone = $stepFourDone;
    }
}