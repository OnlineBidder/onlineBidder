<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkAccounts")
*/
class VkAccounts {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $client_id;

    /** @ORM\Column(type="integer") */
    protected $vk_user_id;

    /** @ORM\Column(type="string") */
    protected $access_key;

    /** @ORM\Column(type="integer") */
    protected $expires_in;

    /** @ORM\Column(type="string") */
    protected $name = '';

    /** @ORM\Column(type="string") */
    protected $last_name = '';

    /** @ORM\Column(type="string") */
    protected $description = '';

    public function set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $access_key
     */
    public function setAccessKey($access_key)
    {
        $this->access_key = $access_key;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @param mixed $client_id
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param mixed $expires_in
     */
    public function setExpiresIn($expires_in)
    {
        $this->expires_in = $expires_in;
    }

    /**
     * @return mixed
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
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

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }


}