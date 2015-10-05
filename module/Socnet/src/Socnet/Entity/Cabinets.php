<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="cabinets")
*/
class Cabinets {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $account_id;

    /** @ORM\Column(type="string") */
    protected $account_type;

    /** @ORM\Column(type="integer") */
    protected $account_status;

    /** @ORM\Column(type="string") */
    protected $access_role;

    /** @ORM\Column(type="integer") */
    protected $vk_user_id;

    /** @ORM\Column(type="integer") */
    protected $vk_account_id;


    public function set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * @param mixed $vk_account_id
     */
    public function setVkAccountId($vk_account_id)
    {
        $this->vk_account_id = $vk_account_id;
    }

    /**
     * @return mixed
     */
    public function getVkAccountId()
    {
        return $this->vk_account_id;
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
     * @param mixed $access_role
     */
    public function setAccessRole($access_role)
    {
        $this->access_role = $access_role;
    }

    /**
     * @return mixed
     */
    public function getAccessRole()
    {
        return $this->access_role;
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
     * @param mixed $account_status
     */
    public function setAccountStatus($account_status)
    {
        $this->account_status = $account_status;
    }

    /**
     * @return mixed
     */
    public function getAccountStatus()
    {
        return $this->account_status;
    }

    /**
     * @param mixed $account_type
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * @param mixed $vk_user_id
     */
    public function setVkUserId($vk_user_id)
    {
        $this->vk_user_id = $vk_user_id;
    }

    /**
     * @return mixed
     */
    public function getVkUserId()
    {
        return $this->vk_user_id;
    }

}