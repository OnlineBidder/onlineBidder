<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkAdsTargeting", uniqueConstraints={@ORM\UniqueConstraint(name="targ_uniq", columns={"hash"})})
*/
class VkAdsTargeting{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="text") */
    protected $data = '';

    /** @ORM\Column(type="string") */
    protected $hash = '';

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

    /**
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }


}