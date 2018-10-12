<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="address")
 * @ORM\Entity
 */
class Address
{
    use TimestampableTrait;

    private const BILLING_ADDRESS = 1; // ha 1, akkor SZÁMLÁZÁSI cím
    private const DELIVERY_ADDRESS = 2; // ha 2, akkor SZÁLLÍTÁSI cím

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    private $street='';

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city='';

    /**
     * @var int
     *
     * @ Assert\NotBlank()
     * @ Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="zip", type="integer")
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string", length=255, nullable=false)
     */
    private $province='';

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=false)
     */
    private $country='';

    /**
     * @var int
     *
     * @ORM\Column(name="address_type", type="integer", nullable=false)
     */
    private $addressType;



    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @var string $street
     */
    public function setStreet(string $street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @var string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * @return int
     */
    public function getZip(): ?int
    {
        return $this->zip;
    }

    /**
     * @var int $zip
     */
    public function setZip(int $zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @var string $province
     */
    public function setProvince(string $province)
    {
        $this->province = $province;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @var string $country
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * @return bool
     */
    public function isBillingAddress(): bool
    {
        if ($this->getAddressType() == BILLING_ADDRESS) {
            return true;
        } else {
            return false;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isDeliveryAddress(): bool
    {
        if ($this->getAddressType() == DELIVERY_ADDRESS) {
            return true;
        } else {
            return false;
        }
        return null;
    }

    /**
     *
     */
    public function setAddressTypeToBilling()
    {
        $this->addressType = BILLING_ADDRESS;
    }

    public function setAddressTypeToDelivery()
    {
        $this->addressType = DELIVERY_ADDRESS;
    }


    /**
     * @return int
     */
    public function getAddressType(): ?int
    {
        return $this->addressType;
    }

    /**
     * @var int $type
     */
    public function setAddressType(int $type)
    {
        $this->addressType = $type;
    }



}