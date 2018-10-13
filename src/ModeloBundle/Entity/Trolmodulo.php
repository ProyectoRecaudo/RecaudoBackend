<?php

namespace ModeloBundle\Entity;

/**
 * Trolmodulo
 */
class Trolmodulo
{
    /**
     * @var integer
     */
    private $pkidrolmodulo;

    /**
     * @var \DateTime
     */
    private $creacionrolmodulo = 'now()';

    /**
     * @var \ModeloBundle\Entity\Tmodulo
     */
    private $fkidmodulo;

    /**
     * @var \ModeloBundle\Entity\Trol
     */
    private $fkidrol;


    /**
     * Get pkidrolmodulo
     *
     * @return integer
     */
    public function getPkidrolmodulo()
    {
        return $this->pkidrolmodulo;
    }

    /**
     * Set creacionrolmodulo
     *
     * @param \DateTime $creacionrolmodulo
     *
     * @return Trolmodulo
     */
    public function setCreacionrolmodulo($creacionrolmodulo)
    {
        $this->creacionrolmodulo = $creacionrolmodulo;

        return $this;
    }

    /**
     * Get creacionrolmodulo
     *
     * @return \DateTime
     */
    public function getCreacionrolmodulo()
    {
        return $this->creacionrolmodulo;
    }

    /**
     * Set fkidmodulo
     *
     * @param \ModeloBundle\Entity\Tmodulo $fkidmodulo
     *
     * @return Trolmodulo
     */
    public function setFkidmodulo(\ModeloBundle\Entity\Tmodulo $fkidmodulo = null)
    {
        $this->fkidmodulo = $fkidmodulo;

        return $this;
    }

    /**
     * Get fkidmodulo
     *
     * @return \ModeloBundle\Entity\Tmodulo
     */
    public function getFkidmodulo()
    {
        return $this->fkidmodulo;
    }

    /**
     * Set fkidrol
     *
     * @param \ModeloBundle\Entity\Trol $fkidrol
     *
     * @return Trolmodulo
     */
    public function setFkidrol(\ModeloBundle\Entity\Trol $fkidrol = null)
    {
        $this->fkidrol = $fkidrol;

        return $this;
    }

    /**
     * Get fkidrol
     *
     * @return \ModeloBundle\Entity\Trol
     */
    public function getFkidrol()
    {
        return $this->fkidrol;
    }
}
