<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkCampaign")
*/
class VkCampaigns{

    const STATUS_STOP = 0;
    const STATUS_START = 1;
    const STATUS_DELETED = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="integer") */
    protected $vk_client_id = 0;

    /** @ORM\Column(type="integer") */
    protected $status; //статус кампании (0 — кампания остановлена, 1 — кампания запущена, 2 — кампания удалена)

    /** @ORM\Column(type="string") */
    protected $day_limit;

    /** @ORM\Column(type="string") */
    protected $all_limit;

    /** @ORM\Column(type="string") */
    protected $start_time;

    /** @ORM\Column(type="string") */
    protected $stop_time;

    /** @ORM\Column(type="string") */
    protected $create_time;

    /** @ORM\Column(type="string") */
    protected $update_time;

    /** @ORM\Column(type="integer") */
    protected $bidder_control = 0;

      /** @ORM\Column(type="integer") */
    protected $cabinet_id;

    /** @ORM\Column(type="integer") */
    protected $vk_account_id = 0;

    /** @ORM\Column(type="integer") */
    protected $stop_from = 1;

    /** @ORM\Column(type="integer") */
    protected $stop_to = 9;

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
     * @param mixed $stop_from
     */
    public function setStopFrom($stop_from)
    {
        $this->stop_from = $stop_from;
    }

    /**
     * @return mixed
     */
    public function getStopFrom()
    {
        return $this->stop_from;
    }

    /**
     * @param mixed $stop_to
     */
    public function setStopTo($stop_to)
    {
        $this->stop_to = $stop_to;
    }

    /**
     * @return mixed
     */
    public function getStopTo()
    {
        return $this->stop_to;
    }

    /**
     * @param mixed $vk_client_id
     */
    public function setVkClientId($vk_client_id)
    {
        $this->vk_client_id = $vk_client_id;
    }

    /**
     * @return mixed
     */
    public function getVkClientId()
    {
        return $this->vk_client_id;
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
     * @param mixed $bidder_control
     */
    public function setBidderControl($bidder_control)
    {
        $this->bidder_control = $bidder_control;
    }

    /**
     * @return mixed
     */
    public function getBidderControl()
    {
        return $this->bidder_control;
    }

    /**
     * @param mixed $cabinet_id
     */
    public function setCabinetId($cabinet_id)
    {
        $this->cabinet_id = $cabinet_id;
    }

    /**
     * @return mixed
     */
    public function getCabinetId()
    {
        return $this->cabinet_id;
    }

    /**
     * @param mixed $create_time
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->create_time;
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

    /**
     * @param mixed $start_time
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->start_time;
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

    /**
     * @param mixed $stop_time
     */
    public function setStopTime($stop_time)
    {
        $this->stop_time = $stop_time;
    }

    /**
     * @return mixed
     */
    public function getStopTime()
    {
        return $this->stop_time;
    }

    /**
     * @param mixed $update_time
     */
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;
    }

    /**
     * @return mixed
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

}