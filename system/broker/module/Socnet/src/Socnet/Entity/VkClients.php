<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkClients")
*/
class VkClients {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="integer") */
    protected $day_limit  = 0;

    /** @ORM\Column(type="integer") */
    protected $all_limit = 0;

    /** @ORM\Column(type="integer") */
    protected $account_id;

    /** @ORM\Column(type="integer") */
    protected $bidder_account_id = 1;


    public function set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * @param mixed $bidder_account_id
     */
    public function setBidderAccountId($bidder_account_id)
    {
        $this->bidder_account_id = $bidder_account_id;
    }

    /**
     * @return mixed
     */
    public function getBidderAccountId()
    {
        return $this->bidder_account_id;
    }


    /**
     * @param mixed $account_id
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->account_id;
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
     * @param mixed $day_limit
     */
    public function setDayLimit($day_limit)
    {
        $this->day_limit = $day_limit;
    }

    /**
     * @return mixed
     */
    public function getDayLimit()
    {
        return $this->day_limit;
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
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }




}