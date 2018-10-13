<?php

namespace ModeloBundle\Entity;

/**
 * Ttipoanimal
 */
class Ttipoanimal
{
    /**
     * @var string
     */
    private $codigotipoanimal;

    /**
     * @var string
     */
    private $nombretipoanimal;

    /**
     * @var string
     */
    private $descripciontipoanimal;

    /**
     * @var boolean
     */
    private $tipoanimalactivo = '1';

    /**
     * @var \DateTime
     */
    private $creaciontipoanimal = 'now()';

    /**
     * @var \DateTime
     */
    private $modificaciontipoanimal = 'now()';

    /**
     * @var integer
     */
    private $pktipoanimal;


    /**
     * Set codigotipoanimal
     *
     * @param string $codigotipoanimal
     *
     * @return Ttipoanimal
     */
    public function setCodigotipoanimal($codigotipoanimal)
    {
        $this->codigotipoanimal = $codigotipoanimal;

        return $this;
    }

    /**
     * Get codigotipoanimal
     *
     * @return string
     */
    public function getCodigotipoanimal()
    {
        return $this->codigotipoanimal;
    }

    /**
     * Set nombretipoanimal
     *
     * @param string $nombretipoanimal
     *
     * @return Ttipoanimal
     */
    public function setNombretipoanimal($nombretipoanimal)
    {
        $this->nombretipoanimal = $nombretipoanimal;

        return $this;
    }

    /**
     * Get nombretipoanimal
     *
     * @return string
     */
    public function getNombretipoanimal()
    {
        return $this->nombretipoanimal;
    }

    /**
     * Set descripciontipoanimal
     *
     * @param string $descripciontipoanimal
     *
     * @return Ttipoanimal
     */
    public function setDescripciontipoanimal($descripciontipoanimal)
    {
        $this->descripciontipoanimal = $descripciontipoanimal;

        return $this;
    }

    /**
     * Get descripciontipoanimal
     *
     * @return string
     */
    public function getDescripciontipoanimal()
    {
        return $this->descripciontipoanimal;
    }

    /**
     * Set tipoanimalactivo
     *
     * @param boolean $tipoanimalactivo
     *
     * @return Ttipoanimal
     */
    public function setTipoanimalactivo($tipoanimalactivo)
    {
        $this->tipoanimalactivo = $tipoanimalactivo;

        return $this;
    }

    /**
     * Get tipoanimalactivo
     *
     * @return boolean
     */
    public function getTipoanimalactivo()
    {
        return $this->tipoanimalactivo;
    }

    /**
     * Set creaciontipoanimal
     *
     * @param \DateTime $creaciontipoanimal
     *
     * @return Ttipoanimal
     */
    public function setCreaciontipoanimal($creaciontipoanimal)
    {
        $this->creaciontipoanimal = $creaciontipoanimal;

        return $this;
    }

    /**
     * Get creaciontipoanimal
     *
     * @return \DateTime
     */
    public function getCreaciontipoanimal()
    {
        return $this->creaciontipoanimal;
    }

    /**
     * Set modificaciontipoanimal
     *
     * @param \DateTime $modificaciontipoanimal
     *
     * @return Ttipoanimal
     */
    public function setModificaciontipoanimal($modificaciontipoanimal)
    {
        $this->modificaciontipoanimal = $modificaciontipoanimal;

        return $this;
    }

    /**
     * Get modificaciontipoanimal
     *
     * @return \DateTime
     */
    public function getModificaciontipoanimal()
    {
        return $this->modificaciontipoanimal;
    }

    /**
     * Get pktipoanimal
     *
     * @return integer
     */
    public function getPktipoanimal()
    {
        return $this->pktipoanimal;
    }
    /**
     * @var integer
     */
    private $pkidtipoanimal;


    /**
     * Get pkidtipoanimal
     *
     * @return integer
     */
    public function getPkidtipoanimal()
    {
        return $this->pkidtipoanimal;
    }
}
