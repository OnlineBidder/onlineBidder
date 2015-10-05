<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="testCpm")
*/
class TestCpm {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $ad_id;

    /** @ORM\Column(type="integer") */
    protected $insert_time;

    /** @ORM\Column(type="float", scale=2) */
    protected $cpm = 0;

    /** @ORM\Column(type="float", scale=4) */
    protected $cpc = 0;

    /** @ORM\Column(type="float", scale=4) */
    protected $ctr = 0;

    /**
     * @param mixed $ad_id
     */
    public function setAdId($ad_id)
    {
        $this->ad_id = $ad_id;
    }

    /**
     * @return mixed
     */
    public function getAdId()
    {
        return $this->ad_id;
    }

    /**
     * @param mixed $cpc
     */
    public function setCpc($cpc)
    {
        $this->cpc = $cpc;
    }

    /**
     * @return mixed
     */
    public function getCpc()
    {
        return $this->cpc;
    }

    /**
     * @param mixed $cpm
     */
    public function setCpm($cpm)
    {
        $this->cpm = $cpm;
    }

    /**
     * @return mixed
     */
    public function getCpm()
    {
        return $this->cpm;
    }

    /**
     * @param mixed $ctr
     */
    public function setCtr($ctr)
    {
        $this->ctr = $ctr;
    }

    /**
     * @return mixed
     */
    public function getCtr()
    {
        return $this->ctr;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $insert_time
     */
    public function setInsertTime($insert_time)
    {
        $this->insert_time = $insert_time;
    }

    /**
     * @return mixed
     */
    public function getInsertTime()
    {
        return $this->insert_time;
    }



}