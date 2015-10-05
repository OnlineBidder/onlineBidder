<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="photoStorage")
*/
class PhotoStorage{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $file_name;

    /** @ORM\Column(type="text") */
    protected $photo_data;

    /**
     * @param mixed $file_name
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->file_name;
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
     * @param mixed $photo_data
     */
    public function setPhotoData($photo_data)
    {
        $this->photo_data = $photo_data;
    }

    /**
     * @return mixed
     */
    public function getPhotoData()
    {
        return $this->photo_data;
    }

}