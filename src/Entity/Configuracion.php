<?php

namespace App\Entity;

use App\Repository\ConfiguracionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfiguracionRepository::class)
 */
class Configuracion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $establecimiento;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $punto_emision;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sec_factura;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstablecimiento(): ?string
    {
        return $this->establecimiento;
    }

    public function setEstablecimiento(string $establecimiento): self
    {
        $this->establecimiento = $establecimiento;

        return $this;
    }

    public function getPuntoEmision(): ?string
    {
        return $this->punto_emision;
    }

    public function setPuntoEmision(string $punto_emision): self
    {
        $this->punto_emision = $punto_emision;

        return $this;
    }

    public function getSecFactura(): ?string
    {
        return $this->sec_factura;
    }

    public function setSecFactura(string $sec_factura): self
    {
        $this->sec_factura = $sec_factura;

        return $this;
    }
}
