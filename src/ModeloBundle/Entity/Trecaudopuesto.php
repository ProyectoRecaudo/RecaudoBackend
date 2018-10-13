<?php

namespace ModeloBundle\Entity;

/**
 * Trecaudopuesto
 */
class Trecaudopuesto
{
    /**
     * @var boolean
     */
    private $recaudopuestopagado = '';

    /**
     * @var \DateTime
     */
    private $creacionrecaudo = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionrecaudo = 'now()';

    /**
     * @var integer
     */
    private $pkidrecaudopuesto;

    /**
     * @var \ModeloBundle\Entity\Tfactura
     */
    private $fkidfactura;


    /**
     * Set recaudopuestopagado
     *
     * @param boolean $recaudopuestopagado
     *
     * @return Trecaudopuesto
     */
    public function setRecaudopuestopagado($recaudopuestopagado)
    {
        $this->recaudopuestopagado = $recaudopuestopagado;

        return $this;
    }

    /**
     * Get recaudopuestopagado
     *
     * @return boolean
     */
    public function getRecaudopuestopagado()
    {
        return $this->recaudopuestopagado;
    }

    /**
     * Set creacionrecaudo
     *
     * @param \DateTime $creacionrecaudo
     *
     * @return Trecaudopuesto
     */
    public function setCreacionrecaudo($creacionrecaudo)
    {
        $this->creacionrecaudo = $creacionrecaudo;

        return $this;
    }

    /**
     * Get creacionrecaudo
     *
     * @return \DateTime
     */
    public function getCreacionrecaudo()
    {
        return $this->creacionrecaudo;
    }

    /**
     * Set modificacionrecaudo
     *
     * @param \DateTime $modificacionrecaudo
     *
     * @return Trecaudopuesto
     */
    public function setModificacionrecaudo($modificacionrecaudo)
    {
        $this->modificacionrecaudo = $modificacionrecaudo;

        return $this;
    }

    /**
     * Get modificacionrecaudo
     *
     * @return \DateTime
     */
    public function getModificacionrecaudo()
    {
        return $this->modificacionrecaudo;
    }

    /**
     * Get pkidrecaudopuesto
     *
     * @return integer
     */
    public function getPkidrecaudopuesto()
    {
        return $this->pkidrecaudopuesto;
    }

    /**
     * Set fkidfactura
     *
     * @param \ModeloBundle\Entity\Tfactura $fkidfactura
     *
     * @return Trecaudopuesto
     */
    public function setFkidfactura(\ModeloBundle\Entity\Tfactura $fkidfactura = null)
    {
        $this->fkidfactura = $fkidfactura;

        return $this;
    }

    /**
     * Get fkidfactura
     *
     * @return \ModeloBundle\Entity\Tfactura
     */
    public function getFkidfactura()
    {
        return $this->fkidfactura;
    }
}
