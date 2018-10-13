<?php

namespace ModeloBundle\Entity;

/**
 * Tauditoria
 */
class Tauditoria
{
    /**
     * @var integer
     */
    private $pkidauditoria;

    /**
     * @var integer
     */
    private $fkidusuario;

    /**
     * @var string
     */
    private $nombreusuario;

    /**
     * @var string
     */
    private $identificacionusuario;

    /**
     * @var string
     */
    private $tabla;

    /**
     * @var string
     */
    private $valoresrelevantes;

    /**
     * @var string
     */
    private $accion;

    /**
     * @var \DateTime
     */
    private $creacionauditoria = 'now()';

    /**
     * @var integer
     */
    private $pkidelemento;

    /**
     * @var string
     */
    private $origenauditoria;


    /**
     * Get pkidauditoria
     *
     * @return integer
     */
    public function getPkidauditoria()
    {
        return $this->pkidauditoria;
    }

    /**
     * Set fkidusuario
     *
     * @param integer $fkidusuario
     *
     * @return Tauditoria
     */
    public function setFkidusuario($fkidusuario)
    {
        $this->fkidusuario = $fkidusuario;

        return $this;
    }

    /**
     * Get fkidusuario
     *
     * @return integer
     */
    public function getFkidusuario()
    {
        return $this->fkidusuario;
    }

    /**
     * Set nombreusuario
     *
     * @param string $nombreusuario
     *
     * @return Tauditoria
     */
    public function setNombreusuario($nombreusuario)
    {
        $this->nombreusuario = $nombreusuario;

        return $this;
    }

    /**
     * Get nombreusuario
     *
     * @return string
     */
    public function getNombreusuario()
    {
        return $this->nombreusuario;
    }

    /**
     * Set identificacionusuario
     *
     * @param string $identificacionusuario
     *
     * @return Tauditoria
     */
    public function setIdentificacionusuario($identificacionusuario)
    {
        $this->identificacionusuario = $identificacionusuario;

        return $this;
    }

    /**
     * Get identificacionusuario
     *
     * @return string
     */
    public function getIdentificacionusuario()
    {
        return $this->identificacionusuario;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     *
     * @return Tauditoria
     */
    public function setTabla($tabla)
    {
        $this->tabla = $tabla;

        return $this;
    }

    /**
     * Get tabla
     *
     * @return string
     */
    public function getTabla()
    {
        return $this->tabla;
    }

    /**
     * Set valoresrelevantes
     *
     * @param string $valoresrelevantes
     *
     * @return Tauditoria
     */
    public function setValoresrelevantes($valoresrelevantes)
    {
        $this->valoresrelevantes = $valoresrelevantes;

        return $this;
    }

    /**
     * Get valoresrelevantes
     *
     * @return string
     */
    public function getValoresrelevantes()
    {
        return $this->valoresrelevantes;
    }

    /**
     * Set accion
     *
     * @param string $accion
     *
     * @return Tauditoria
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return string
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set creacionauditoria
     *
     * @param \DateTime $creacionauditoria
     *
     * @return Tauditoria
     */
    public function setCreacionauditoria($creacionauditoria)
    {
        $this->creacionauditoria = $creacionauditoria;

        return $this;
    }

    /**
     * Get creacionauditoria
     *
     * @return \DateTime
     */
    public function getCreacionauditoria()
    {
        return $this->creacionauditoria;
    }

    /**
     * Set pkidelemento
     *
     * @param integer $pkidelemento
     *
     * @return Tauditoria
     */
    public function setPkidelemento($pkidelemento)
    {
        $this->pkidelemento = $pkidelemento;

        return $this;
    }

    /**
     * Get pkidelemento
     *
     * @return integer
     */
    public function getPkidelemento()
    {
        return $this->pkidelemento;
    }

    /**
     * Set origenauditoria
     *
     * @param string $origenauditoria
     *
     * @return Tauditoria
     */
    public function setOrigenauditoria($origenauditoria)
    {
        $this->origenauditoria = $origenauditoria;

        return $this;
    }

    /**
     * Get origenauditoria
     *
     * @return string
     */
    public function getOrigenauditoria()
    {
        return $this->origenauditoria;
    }
}
