<?php

namespace Socnet\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity
*   @ORM\Table(name="vkAdsLayout")
*/
class VkAdsLayout{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $campaign_id;

    /** @ORM\Column(type="integer") */
    protected $ad_format;

    /** @ORM\Column(type="integer") */
    protected $cost_type;

    /** @ORM\Column(type="integer") */
    protected $video = 0;

    /** @ORM\Column(type="string") */
    protected $title;

    /** @ORM\Column(type="string") */
    protected $description = '';

    /** @ORM\Column(type="string") */
    protected $link_url = '';

    /** @ORM\Column(type="string") */
    protected $link_domain = '';

    /** @ORM\Column(type="string") */
    protected $preview_link = '';

    /** @ORM\Column(type="string") */
    protected $image_src = '';


    public function set($key, $value)
    {
        $this->$key = $value;
    }

    public function get($key)
    {
        return isset($this->$key) ? $this->$key : false;
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
     * @param mixed $image_src
     */
    public function setImageSrc($image_src)
    {
        $this->image_src = $image_src;
    }

    /**
     * @return mixed
     */
    public function getImageSrc()
    {
        return $this->image_src;
    }

    /**
     * @param mixed $link_domain
     */
    public function setLinkDomain($link_domain)
    {
        $this->link_domain = $link_domain;
    }

    /**
     * @return mixed
     */
    public function getLinkDomain()
    {
        return $this->link_domain;
    }

    /**
     * @param mixed $link_url
     */
    public function setLinkUrl($link_url)
    {
        $this->link_url = $link_url;
    }

    /**
     * @return mixed
     */
    public function getLinkUrl()
    {
        return $this->link_url;
    }

    /**
     * @param mixed $preview_link
     */
    public function setPreviewLink($preview_link)
    {
        $this->preview_link = $preview_link;
    }

    /**
     * @return mixed
     */
    public function getPreviewLink()
    {
        return $this->preview_link;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return mixed
     */
    public function getVideo()
    {
        return $this->video;
    }

}