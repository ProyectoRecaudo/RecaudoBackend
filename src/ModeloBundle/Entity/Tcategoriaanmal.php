<?php

namespace ModeloBundle\Entity;

/**
 * Tcategoriaanmal
 */
class Tcategoriaanmal
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
     * @return Tcategoriaanmal
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
     * @return Tcategoriaanmal
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
     * @return Tcategoriaanmal
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
     * @return Tcategoriaanmal
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
}
