<?php

namespace ModeloBundle\Entity;

/**
 * Tasignaciondependiente
 */
class Tasignaciondependiente
{
    /**
     * @var \DateTime
     */
    private $creacionasignaciondependiente = 'now()';

    /**
     * @var \DateTime
     */
    private $modficacionasignaciondependiente = 'now()';

    /**
     * @var string
     */
    private $numeroresolucionasignaciondependiente;

    /**
     * @var string
     */
    private $resolucionasignaciondependiente;

    /**
     * @var integer
     */
    private $pkidasignaciondependiente;

    /**
     * @var \ModeloBundle\Entity\Tasignacionpuesto
     */
    private $fkidasignacionpuesto;

    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkidtercero;


    /**
     * Set creacionasignaciondependiente
     *
     * @param \DateTime $creacionasignaciondependiente
     *
     * @return Tasignaciondependiente
     */
    public function setCreacionasignaciondependiente($creacionasignaciondependiente)
    {
        $this->creacionasignaciondependiente = $creacionasignaciondependiente;

        return $this;
    }

    /**
     * Get creacionasignaciondependiente
     *
     * @return \DateTime
     */
    public function getCreacionasignaciondependiente()
    {
        return $this->creacionasignaciondependiente;
    }

    /**
     * Set modficacionasignaciondependiente
     *
     * @param \DateTime $modficacionasignaciondependiente
     *
     * @return Tasignaciondependiente
     */
    public function setModficacionasignaciondependiente($modficacionasignaciondependiente)
    {
        $this->modficacionasignaciondependiente = $modficacionasignaciondependiente;

        return $this;
    }

    /**
     * Get modficacionasignaciondependiente
     *
     * @return \DateTime
     */
    public function getModficacionasignaciondependiente()
    {
        return $this->modficacionasignaciondependiente;
    }

    /**
     * Set numeroresolucionasignaciondependiente
     *
     * @param string $numeroresolucionasignaciondependiente
     *
     * @return Tasignaciondependiente
     */
    public function setNumeroresolucionasignaciondependiente($numeroresolucionasignaciondependiente)
    {
        $this->numeroresolucionasignaciondependiente = $numeroresolucionasignaciondependiente;

        return $this;
    }

    /**
     * Get numeroresolucionasignaciondependiente
     *
     * @return string
     */
    public function getNumeroresolucionasignaciondependiente()
    {
        return $this->numeroresolucionasignaciondependiente;
    }

    /**
     * Set resolucionasignaciondependiente
     *
     * @param string $resolucionasignaciondependiente
     *
     * @return Tasignaciondependiente
     */
    public function setResolucionasignaciondependiente($resolucionasignaciondependiente)
    {
        $this->resolucionasignaciondependiente = $resolucionasignaciondependiente;

        return $this;
    }

    /**
     * Get resolucionasignaciondependiente
     *
     * @return string
     */
    public function getResolucionasignaciondependiente()
    {
        return $this->resolucionasignaciondependiente;
    }

    /**
     * Get pkidasignaciondependiente
     *
     * @return integer
     */
    public function getPkidasignaciondependiente()
    {
        return $this->pkidasignaciondependiente;
    }

    /**
     * Set fkidasignacionpuesto
     *
     * @param \ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto
     *
     * @return Tasignaciondependiente
     */
    public function setFkidasignacionpuesto(\ModeloBundle\Entity\Tasignacionpuesto $fkidasignacionpuesto = null)
    {
        $this->fkidasignacionpuesto = $fkidasignacionpuesto;

        return $this;
    }

    /**
     * Get fkidasignacionpuesto
     *
     * @return \ModeloBundle\Entity\Tasignacionpuesto
     */
    public function getFkidasignacionpuesto()
    {
        return $this->fkidasignacionpuesto;
    }

    /**
     * Set fkidtercero
     *
     * @param \ModeloBundle\Entity\Ttercero $fkidtercero
     *
     * @return Tasignaciondependiente
     */
    public function setFkidtercero(\ModeloBundle\Entity\Ttercero $fkidtercero = null)
    {
        $this->fkidtercero = $fkidtercero;

        return $this;
    }

    /**
     * Get fkidtercero
     *
     * @return \ModeloBundle\Entity\Ttercero
     */
    public function getFkidtercero()
    {
        return $this->fkidtercero;
    }
}
