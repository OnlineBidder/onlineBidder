<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="vkDataUpdateQueue", uniqueConstraints={@ORM\UniqueConstraint(name="unique_sign", columns={"unique_sign"})})
 */
class VkDataUpdateQueue{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $method;

    /** @ORM\Column(type="string") */
    protected $access_token;

    /**
     * @ORM\Column(type="text")
     */
    protected $data;

    /**
     * @ORM\Column(type="string", length=32, options={"fixed" = true})
     */
    protected $unique_sign;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additional_info;

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $additional_info
     */
    public function setAdditionalInfo($additional_info)
    {
        $this->additional_info = $additional_info;
    }

    /**
     * @return mixed
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }

    /**
     * @param mixed $unique_sign
     */
    public function setUniqueSign($unique_sign)
    {
        $this->unique_sign = $unique_sign;
    }

    /**
     * @return mixed
     */
    public function getUniqueSign()
    {
        return $this->unique_sign;
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

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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


}