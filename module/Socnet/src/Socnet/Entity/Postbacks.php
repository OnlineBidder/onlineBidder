<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="postback", uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", columns={"ad_id", "lead_id"})})
*/
class Postbacks {
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

    /** @ORM\Column(type="float", scale=4) */
    protected $payment = 0;

    /** @ORM\Column(type="string", nullable=true) */
    protected $lead_id;


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
     * @param mixed $lead_id
     */
    public function setLeadId($lead_id)
    {
        $this->lead_id = $lead_id;
    }

    /**
     * @return mixed
     */
    public function getLeadId()
    {
        return $this->lead_id;
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

    /**
     * @param mixed $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return mixed
     */
    public function getPayment()
    {
        return $this->payment;
    }



}