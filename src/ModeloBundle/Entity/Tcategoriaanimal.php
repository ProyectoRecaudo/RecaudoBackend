<?php

namespace ModeloBundle\Entity;

/**
 * Tcategoriaanimal
 */
class Tcategoriaanimal
{
    /**
     * @var string
     */
    private $nombrecategoriaanimal;

    /**
     * @var string
     */
    private $descripcioncategoriaanimal;

    /**
     * @var \DateTime
     */
    private $creacioncategoriaanimal = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacioncategoriaanimal = 'now()';

    /**
     * @var integer
     */
    private $pkidcategoriaanimal;


    /**
     * Set nombrecategoriaanimal
     *
     * @param string $nombrecategoriaanimal
     *
     * @return Tcategoriaanimal
     */
    public function setNombrecategoriaanimal($nombrecategoriaanimal)
    {
        $this->nombrecategoriaanimal = $nombrecategoriaanimal;

        return $this;
    }

    /**
     * Get nombrecategoriaanimal
     *
     * @return string
     */
    public function getNombrecategoriaanimal()
    {
        return $this->nombrecategoriaanimal;
    }

    /**
     * Set descripcioncategoriaanimal
     *
     * @param string $descripcioncategoriaanimal
     *
     * @return Tcategoriaanimal
     */
    public function setDescripcioncategoriaanimal($descripcioncategoriaanimal)
    {
        $this->descripcioncategoriaanimal = $descripcioncategoriaanimal;

        return $this;
    }

    /**
     * Get descripcioncategoriaanimal
     *
     * @return string
     */
    public function getDescripcioncategoriaanimal()
    {
        return $this->descripcioncategoriaanimal;
    }

    /**
     * Set creacioncategoriaanimal
     *
     * @param \DateTime $creacioncategoriaanimal
     *
     * @return Tcategoriaanimal
     */
    public function setCreacioncategoriaanimal($creacioncategoriaanimal)
    {
        $this->creacioncategoriaanimal = $creacioncategoriaanimal;

        return $this;
    }

    /**
     * Get creacioncategoriaanimal
     *
     * @return \DateTime
     */
    public function getCreacioncategoriaanimal()
    {
        return $this->creacioncategoriaanimal;
    }

    /**
     * Set modificacioncategoriaanimal
     *
     * @param \DateTime $modificacioncategoriaanimal
     *
     * @return Tcategoriaanimal
     */
    public function setModificacioncategoriaanimal($modificacioncategoriaanimal)
    {
        $this->modificacioncategoriaanimal = $modificacioncategoriaanimal;

        return $this;
    }

    /**
     * Get modificacioncategoriaanimal
     *
     * @return \DateTime
     */
    public function getModificacioncategoriaanimal()
    {
        return $this->modificacioncategoriaanimal;
    }

    /**
     * Get pkidcategoriaanimal
     *
     * @return integer
     */
    public function getPkidcategoriaanimal()
    {
        return $this->pkidcategoriaanimal;
    }
    /**
     * @var boolean
     */
    private $categoriaanimalactivo;


    /**
     * Set categoriaanimalactivo
     *
     * @param boolean $categoriaanimalactivo
     *
     * @return Tcategoriaanimal
     */
    public function setCategoriaanimalactivo($categoriaanimalactivo)
    {
        $this->categoriaanimalactivo = $categoriaanimalactivo;

        return $this;
    }

    /**
     * Get categoriaanimalactivo
     *
     * @return boolean
     */
    public function getCategoriaanimalactivo()
    {
        return $this->categoriaanimalactivo;
    }
    /**
     * @var string
     */
    private $codigocategoriaanimal;


    /**
     * Set codigocategoriaanimal
     *
     * @param string $codigocategoriaanimal
     *
     * @return Tcategoriaanimal
     */
    public function setCodigocategoriaanimal($codigocategoriaanimal)
    {
        $this->codigocategoriaanimal = $codigocategoriaanimal;

        return $this;
    }

    /**
     * Get codigocategoriaanimal
     *
     * @return string
     */
    public function getCodigocategoriaanimal()
    {
        return $this->codigocategoriaanimal;
    }
}
