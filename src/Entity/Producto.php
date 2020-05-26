<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductoRepository::class)
 */
class Producto
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
    private $codigo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $detalle;

    /**
     * @ORM\Column(type="float")
     */
    private $precio_unit;

    /**
     * @ORM\OneToMany(targetEntity=DetalleFactura::class, mappedBy="producto")
     */
    private $detalleFacturas;

    public function __construct()
    {
        $this->detalleFacturas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getDetalle(): ?string
    {
        return $this->detalle;
    }

    public function setDetalle(string $detalle): self
    {
        $this->detalle = $detalle;

        return $this;
    }

    public function getPrecioUnit(): ?float
    {
        return $this->precio_unit;
    }

    public function setPrecioUnit(float $precio_unit): self
    {
        $this->precio_unit = $precio_unit;

        return $this;
    }

    /**
     * @return Collection|DetalleFactura[]
     */
    public function getDetalleFacturas(): Collection
    {
        return $this->detalleFacturas;
    }

    public function addDetalleFactura(DetalleFactura $detalleFactura): self
    {
        if (!$this->detalleFacturas->contains($detalleFactura)) {
            $this->detalleFacturas[] = $detalleFactura;
            $detalleFactura->setProducto($this);
        }

        return $this;
    }

    public function removeDetalleFactura(DetalleFactura $detalleFactura): self
    {
        if ($this->detalleFacturas->contains($detalleFactura)) {
            $this->detalleFacturas->removeElement($detalleFactura);
            // set the owning side to null (unless already changed)
            if ($detalleFactura->getProducto() === $this) {
                $detalleFactura->setProducto(null);
            }
        }

        return $this;
    }
}
