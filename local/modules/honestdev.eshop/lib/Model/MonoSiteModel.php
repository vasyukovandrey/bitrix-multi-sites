<?php
namespace Honestdev\Eshop\Model;

class MonoSiteModel
{
    public ?int $id  = null;

    public ?string $siteId = null;

    public ?string $code = null;

    public ?string $name = null;

    public ?string $domain = null;

    public ?string $type = null;

    public ?string $entityId = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSiteId(): ?string
    {
        return $this->siteId;
    }

    /**
     * @param string|null $siteId
     * @return $this
     */
    public function setSiteId(?string $siteId): self
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     * @return $this
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * @param string|null $entityId
     * @return $this
     */
    public function setEntityId(?string $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }
}