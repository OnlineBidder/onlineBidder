<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkAds")
*/
class VkAds{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $campaign_id;

    /** @ORM\Column(type="integer") */
    protected $vk_client_id = 0;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="integer") */
    protected $status; //статус  (0 — остановлена, 1 — запущена, 2 — удалена)

    /** @ORM\Column(type="string") */
    protected $approved;

    /** @ORM\Column(type="string") */
    protected $all_limit;

    /** @ORM\Column(type="string") */
    protected $category1_id;

    /** @ORM\Column(type="string") */
    protected $category2_id;

    /** @ORM\Column(type="string") */
    protected $create_time;

    /** @ORM\Column(type="string") */
    protected $update_time;

    /** @ORM\Column(type="integer") */
    protected $cost_type;

    /** @ORM\Column(type="integer") */
    protected $ad_format;

    /** @ORM\Column(type="string") */
    protected $cpc = '0';

    /** @ORM\Column(type="string") */
    protected $cpm = '0';

    /** @ORM\Column(type="integer") */
    protected $ad_platform;

    /** @ORM\Column(type="integer") */
    protected $bidder_update_time = 0;

    /** @ORM\Column(type="integer") */
    protected $impressions = 0;

    /** @ORM\Column(type="integer") */
    protected $clicks = 0;

    /** @ORM\Column(type="float", scale=4) */
    protected $spent = 0;

    /** @ORM\Column(type="integer") */
    protected $bidder_control = 0;

    /** @ORM\Column(type="integer") */
    protected $impressions_step = 0;

    /** @ORM\Column(type="integer") */
    protected $clicks_step = 0;

    /** @ORM\Column(type="integer") */
    protected $algorithm = 1;

    /** @ORM\Column(type="integer") */
    protected $min_counter = 10000;

    /** @ORM\Column(type="float", scale=4) */
    protected $min_ctr = 0.04;

    /** @ORM\Column(type="float", scale=1) */
    protected $off_minutes = 2;

    /** @ORM\Column(type="float", scale=2) */
    protected $desire_cpc = 0;

    public function set($key, $value)
    {
        $this->$key = $value;
    }

    public function get($key)
    {
        return isset($this->$key) ? $this->$key : false;
    }

    /**
     * @param mixed $min_counter
     */
    public function setMinCounter($min_counter)
    {
        $this->min_counter = $min_counter;
    }

    /**
     * @param mixed $desire_cpc
     */
    public function setDesireCpc($desire_cpc)
    {
        $this->desire_cpc = $desire_cpc;
    }

    /**
     * @return mixed
     */
    public function getDesireCpc()
    {
        return $this->desire_cpc;
    }

    /**
     * @param mixed $spent
     */
    public function setSpent($spent)
    {
        $this->spent = $spent;
    }

    /**
     * @return mixed
     */
    public function getSpent()
    {
        return $this->spent;
    }

    /**
     * @return mixed
     */
    public function getMinCounter()
    {
        return $this->min_counter;
    }

    /**
     * @param mixed $min_ctr
     */
    public function setMinCtr($min_ctr)
    {
        $this->min_ctr = $min_ctr;
    }

    /**
     * @return mixed
     */
    public function getMinCtr()
    {
        return $this->min_ctr;
    }

    /**
     * @param mixed $off_minutes
     */
    public function setOffMinutes($off_minutes)
    {
        $this->off_minutes = $off_minutes;
    }

    /**
     * @return mixed
     */
    public function getOffMinutes()
    {
        return $this->off_minutes;
    }

    /**
     * @param mixed $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return mixed
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param mixed $clicks_step
     */
    public function setClicksStep($clicks_step)
    {
        $this->clicks_step = $clicks_step;
    }

    /**
     * @return mixed
     */
    public function getClicksStep()
    {
        return $this->clicks_step;
    }

    /**
     * @param mixed $impressions_step
     */
    public function setImpressionsStep($impressions_step)
    {
        $this->impressions_step = $impressions_step;
    }

    /**
     * @return mixed
     */
    public function getImpressionsStep()
    {
        return $this->impressions_step;
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
     * @param mixed $clicks
     */
    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
    }

    /**
     * @return mixed
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * @param mixed $impressions
     */
    public function setImpressions($impressions)
    {
        $this->impressions = $impressions;
    }

    /**
     * @return mixed
     */
    public function getImpressions()
    {
        return $this->impressions;
    }

    /**
     * @param mixed $bidder_update_time
     */
    public function setBidderUpdateTime($bidder_update_time)
    {
        $this->bidder_update_time = $bidder_update_time;
    }

    /**
     * @return mixed
     */
    public function getBidderUpdateTime()
    {
        return $this->bidder_update_time;
    }

    /**
     * @param mixed $ad_format
     */
    public function setAdFormat($ad_format)
    {
        $this->ad_format = $ad_format;
    }

    /**
     * @return mixed
     */
    public function getAdFormat()
    {
        return $this->ad_format;
    }

    /**
     * @param mixed $ad_platform
     */
    public function setAdPlatform($ad_platform)
    {
        $this->ad_platform = $ad_platform;
    }

    /**
     * @return mixed
     */
    public function getAdPlatform()
    {
        return $this->ad_platform;
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
     * @param mixed $approved
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * @return mixed
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * @param mixed $campaign_id
     */
    public function setCampaignId($campaign_id)
    {
        $this->campaign_id = $campaign_id;
    }

    /**
     * @return mixed
     */
    public function getCampaignId()
    {
        return $this->campaign_id;
    }

    /**
     * @param mixed $category1_id
     */
    public function setCategory1Id($category1_id)
    {
        $this->category1_id = $category1_id;
    }

    /**
     * @return mixed
     */
    public function getCategory1Id()
    {
        return $this->category1_id;
    }

    /**
     * @param mixed $category2_id
     */
    public function setCategory2Id($category2_id)
    {
        $this->category2_id = $category2_id;
    }

    /**
     * @return mixed
     */
    public function getCategory2Id()
    {
        return $this->category2_id;
    }

    /**
     * @param mixed $cost_type
     */
    public function setCostType($cost_type)
    {
        $this->cost_type = $cost_type;
    }

    /**
     * @return mixed
     */
    public function getCostType()
    {
        return $this->cost_type;
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


}