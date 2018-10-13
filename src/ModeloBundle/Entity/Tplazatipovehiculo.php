<?php

namespace ModeloBundle\Entity;

/**
 * Tplazatipovehiculo
 */
class Tplazatipovehiculo
{
    /**
     * @var \DateTime
     */
    private $creaciontipovehiculoplaza = 'now()';

    /**
     * @var integer
     */
    private $pktipovehiculoplaza;

    /**
     * @var \ModeloBundle\Entity\Ttipovehiculo
     */
    private $fkidtipovehiculo;

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;


    /**
     * Set creaciontipovehiculoplaza
     *
     * @param \DateTime $creaciontipovehiculoplaza
     *
     * @return Tplazatipovehiculo
     */
    public function setCreaciontipovehiculoplaza($creaciontipovehiculoplaza)
    {
        $this->creaciontipovehiculoplaza = $creaciontipovehiculoplaza;

        return $this;
    }

    /**
     * Get creaciontipovehiculoplaza
     *
     * @return \DateTime
     */
    public function getCreaciontipovehiculoplaza()
    {
        return $this->creaciontipovehiculoplaza;
    }

    /**
     * Get pktipovehiculoplaza
     *
     * @return integer
     */
    public function getPktipovehiculoplaza()
    {
        return $this->pktipovehiculoplaza;
    }

    /**
     * Set fkidtipovehiculo
     *
     * @param \ModeloBundle\Entity\Ttipovehiculo $fkidtipovehiculo
     *
     * @return Tplazatipovehiculo
     */
    public function setFkidtipovehiculo(\ModeloBundle\Entity\Ttipovehiculo $fkidtipovehiculo = null)
    {
        $this->fkidtipovehiculo = $fkidtipovehiculo;

        return $this;
    }

    /**
     * Get fkidtipovehiculo
     *
     * @return \ModeloBundle\Entity\Ttipovehiculo
     */
    public function getFkidtipovehiculo()
    {
        return $this->fkidtipovehiculo;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tplazatipovehiculo
     */
    public function setFkidplaza(\ModeloBundle\Entity\Tplaza $fkidplaza = null)
    {
        $this->fkidplaza = $fkidplaza;

        return $this;
    }

    /**
     * Get fkidplaza
     *
     * @return \ModeloBundle\Entity\Tplaza
     */
    public function getFkidplaza()
    {
        return $this->fkidplaza;
    }
}
