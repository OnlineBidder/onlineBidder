<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="vkAdsProcessing", uniqueConstraints={@ORM\UniqueConstraint(name="ads_idx", columns={"ad_id", "status"})})
 */
class VkAdsProcessing{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ad_id;

    /** @ORM\Column(type="integer") */
    protected $account_id;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $status;

    /** @ORM\Column(type="float", scale=2) */
    protected $cpm = 0;

    /** @ORM\Column(type="float", scale=3) */
    protected $ctr = 0;

    /** @ORM\Column(type="string") */
    protected $access_token;

    /** @ORM\Column(type="integer") */
    protected $all_limit = 0;

    /** @ORM\Column(type="string", nullable=true) */
    protected $callback;

    /**
     * @param mixed $account_id
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }

    /**
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $all_limit
     */
    public function setAllLimit($all_limit)
    {
        $this->all_limit = $all_limit;
    }

    /**
     * @return mixed
     */
    public function getAllLimit()
    {
        return $this->all_limit;
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
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->account_id;
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
     * @param mixed $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }


    public function set($key, $value)
    {
        $this->$key = $value;
    }

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
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }



}