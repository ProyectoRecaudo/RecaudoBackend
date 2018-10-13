<?php

namespace ModeloBundle\Entity;

/**
 * Tplazatiporecaudo
 */
class Tplazatiporecaudo
{
    /**
     * @var integer
     */
    private $pkidplazatiporecaudo;

    /**
     * @var \DateTime
     */
    private $creacionplazatiporecaudo = 'now()';

    /**
     * @var \ModeloBundle\Entity\Tplaza
     */
    private $fkidplaza;

    /**
     * @var \ModeloBundle\Entity\Ttiporecaudo
     */
    private $fkidtiporecaudo;


    /**
     * Get pkidplazatiporecaudo
     *
     * @return integer
     */
    public function getPkidplazatiporecaudo()
    {
        return $this->pkidplazatiporecaudo;
    }

    /**
     * Set creacionplazatiporecaudo
     *
     * @param \DateTime $creacionplazatiporecaudo
     *
     * @return Tplazatiporecaudo
     */
    public function setCreacionplazatiporecaudo($creacionplazatiporecaudo)
    {
        $this->creacionplazatiporecaudo = $creacionplazatiporecaudo;

        return $this;
    }

    /**
     * Get creacionplazatiporecaudo
     *
     * @return \DateTime
     */
    public function getCreacionplazatiporecaudo()
    {
        return $this->creacionplazatiporecaudo;
    }

    /**
     * Set fkidplaza
     *
     * @param \ModeloBundle\Entity\Tplaza $fkidplaza
     *
     * @return Tplazatiporecaudo
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

    /**
     * Set fkidtiporecaudo
     *
     * @param \ModeloBundle\Entity\Ttiporecaudo $fkidtiporecaudo
     *
     * @return Tplazatiporecaudo
     */
    public function setFkidtiporecaudo(\ModeloBundle\Entity\Ttiporecaudo $fkidtiporecaudo = null)
    {
        $this->fkidtiporecaudo = $fkidtiporecaudo;

        return $this;
    }

    /**
     * Get fkidtiporecaudo
     *
     * @return \ModeloBundle\Entity\Ttiporecaudo
     */
    public function getFkidtiporecaudo()
    {
        return $this->fkidtiporecaudo;
    }
}
