<?php

namespace ModeloBundle\Entity;

/**
 * Trecibopuestoeventual
 */
class Trecibopuestoeventual
{
    /**
     * @var string
     */
    private $numerorecibopuestoeventual;

    /**
     * @var string
     */
    private $nombrebeneficiario;

    /**
     * @var float
     */
    private $valorecibopuestoeventual;

    /**
     * @var \DateTime
     */
    private $creacionrecibopuestoeventual = 'now()';

    /**
     * @var \DateTime
     */
    private $modificacionrecibopuestoeventual;

    /**
     * @var string
     */
    private $identificacionbeneficiario;

    /**
     * @var integer
     */
    private $pkidrecibopuestoeventual;

    /**
     * @var \ModeloBundle\Entity\Ttarifapuestoeventual
     */
    private $fkidtarifapuestoeventual;


    /**
     * Set numerorecibopuestoeventual
     *
     * @param string $numerorecibopuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setNumerorecibopuestoeventual($numerorecibopuestoeventual)
    {
        $this->numerorecibopuestoeventual = $numerorecibopuestoeventual;

        return $this;
    }

    /**
     * Get numerorecibopuestoeventual
     *
     * @return string
     */
    public function getNumerorecibopuestoeventual()
    {
        return $this->numerorecibopuestoeventual;
    }

    /**
     * Set nombrebeneficiario
     *
     * @param string $nombrebeneficiario
     *
     * @return Trecibopuestoeventual
     */
    public function setNombrebeneficiario($nombrebeneficiario)
    {
        $this->nombrebeneficiario = $nombrebeneficiario;

        return $this;
    }

    /**
     * Get nombrebeneficiario
     *
     * @return string
     */
    public function getNombrebeneficiario()
    {
        return $this->nombrebeneficiario;
    }

    /**
     * Set valorecibopuestoeventual
     *
     * @param float $valorecibopuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setValorecibopuestoeventual($valorecibopuestoeventual)
    {
        $this->valorecibopuestoeventual = $valorecibopuestoeventual;

        return $this;
    }

    /**
     * Get valorecibopuestoeventual
     *
     * @return float
     */
    public function getValorecibopuestoeventual()
    {
        return $this->valorecibopuestoeventual;
    }

    /**
     * Set creacionrecibopuestoeventual
     *
     * @param \DateTime $creacionrecibopuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setCreacionrecibopuestoeventual($creacionrecibopuestoeventual)
    {
        $this->creacionrecibopuestoeventual = $creacionrecibopuestoeventual;

        return $this;
    }

    /**
     * Get creacionrecibopuestoeventual
     *
     * @return \DateTime
     */
    public function getCreacionrecibopuestoeventual()
    {
        return $this->creacionrecibopuestoeventual;
    }

    /**
     * Set modificacionrecibopuestoeventual
     *
     * @param \DateTime $modificacionrecibopuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setModificacionrecibopuestoeventual($modificacionrecibopuestoeventual)
    {
        $this->modificacionrecibopuestoeventual = $modificacionrecibopuestoeventual;

        return $this;
    }

    /**
     * Get modificacionrecibopuestoeventual
     *
     * @return \DateTime
     */
    public function getModificacionrecibopuestoeventual()
    {
        return $this->modificacionrecibopuestoeventual;
    }

    /**
     * Set identificacionbeneficiario
     *
     * @param string $identificacionbeneficiario
     *
     * @return Trecibopuestoeventual
     */
    public function setIdentificacionbeneficiario($identificacionbeneficiario)
    {
        $this->identificacionbeneficiario = $identificacionbeneficiario;

        return $this;
    }

    /**
     * Get identificacionbeneficiario
     *
     * @return string
     */
    public function getIdentificacionbeneficiario()
    {
        return $this->identificacionbeneficiario;
    }

    /**
     * Get pkidrecibopuestoeventual
     *
     * @return integer
     */
    public function getPkidrecibopuestoeventual()
    {
        return $this->pkidrecibopuestoeventual;
    }

    /**
     * Set fkidtarifapuestoeventual
     *
     * @param \ModeloBundle\Entity\Ttarifapuestoeventual $fkidtarifapuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setFkidtarifapuestoeventual(\ModeloBundle\Entity\Ttarifapuestoeventual $fkidtarifapuestoeventual = null)
    {
        $this->fkidtarifapuestoeventual = $fkidtarifapuestoeventual;

        return $this;
    }

    /**
     * Get fkidtarifapuestoeventual
     *
     * @return \ModeloBundle\Entity\Ttarifapuestoeventual
     */
    public function getFkidtarifapuestoeventual()
    {
        return $this->fkidtarifapuestoeventual;
    }
    /**
     * @var \ModeloBundle\Entity\Ttercero
     */
    private $fkidtercero;


    /**
     * Set fkidtercero
     *
     * @param \ModeloBundle\Entity\Ttercero $fkidtercero
     *
     * @return Trecibopuestoeventual
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
    /**
     * @var string
     */
    private $nombreusuario;

    /**
     * @var string
     */
    private $identificacionusuario;

    /**
     * @var integer
     */
    private $fkidplaza;

    /**
     * @var float
     */
    private $valortarifa;

    /**
     * @var string
     */
    private $nombreplaza;


    /**
     * Set nombreusuario
     *
     * @param string $nombreusuario
     *
     * @return Trecibopuestoeventual
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
     * @return Trecibopuestoeventual
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
     * Set fkidplaza
     *
     * @param integer $fkidplaza
     *
     * @return Trecibopuestoeventual
     */
    public function setFkidplaza($fkidplaza)
    {
        $this->fkidplaza = $fkidplaza;

        return $this;
    }

    /**
     * Get fkidplaza
     *
     * @return integer
     */
    public function getFkidplaza()
    {
        return $this->fkidplaza;
    }

    /**
     * Set valortarifa
     *
     * @param float $valortarifa
     *
     * @return Trecibopuestoeventual
     */
    public function setValortarifa($valortarifa)
    {
        $this->valortarifa = $valortarifa;

        return $this;
    }

    /**
     * Get valortarifa
     *
     * @return float
     */
    public function getValortarifa()
    {
        return $this->valortarifa;
    }

    /**
     * Set nombreplaza
     *
     * @param string $nombreplaza
     *
     * @return Trecibopuestoeventual
     */
    public function setNombreplaza($nombreplaza)
    {
        $this->nombreplaza = $nombreplaza;

        return $this;
    }

    /**
     * Get nombreplaza
     *
     * @return string
     */
    public function getNombreplaza()
    {
        return $this->nombreplaza;
    }
    /**
     * @var boolean
     */
    private $recibopuestoeventualactivo;

    /**
     * @var string
     */
    private $nombretercero;

    /**
     * @var string
     */
    private $identificaciontercero;

    /**
     * @var string
     */
    private $nombresector;

    /**
     * @var \ModeloBundle\Entity\Tsector
     */
    private $fkidsector;


    /**
     * Set recibopuestoeventualactivo
     *
     * @param boolean $recibopuestoeventualactivo
     *
     * @return Trecibopuestoeventual
     */
    public function setRecibopuestoeventualactivo($recibopuestoeventualactivo)
    {
        $this->recibopuestoeventualactivo = $recibopuestoeventualactivo;

        return $this;
    }

    /**
     * Get recibopuestoeventualactivo
     *
     * @return boolean
     */
    public function getRecibopuestoeventualactivo()
    {
        return $this->recibopuestoeventualactivo;
    }

    /**
     * Set nombretercero
     *
     * @param string $nombretercero
     *
     * @return Trecibopuestoeventual
     */
    public function setNombretercero($nombretercero)
    {
        $this->nombretercero = $nombretercero;

        return $this;
    }

    /**
     * Get nombretercero
     *
     * @return string
     */
    public function getNombretercero()
    {
        return $this->nombretercero;
    }

    /**
     * Set identificaciontercero
     *
     * @param string $identificaciontercero
     *
     * @return Trecibopuestoeventual
     */
    public function setIdentificaciontercero($identificaciontercero)
    {
        $this->identificaciontercero = $identificaciontercero;

        return $this;
    }

    /**
     * Get identificaciontercero
     *
     * @return string
     */
    public function getIdentificaciontercero()
    {
        return $this->identificaciontercero;
    }

    /**
     * Set nombresector
     *
     * @param string $nombresector
     *
     * @return Trecibopuestoeventual
     */
    public function setNombresector($nombresector)
    {
        $this->nombresector = $nombresector;

        return $this;
    }

    /**
     * Get nombresector
     *
     * @return string
     */
    public function getNombresector()
    {
        return $this->nombresector;
    }

    /**
     * Set fkidsector
     *
     * @param \ModeloBundle\Entity\Tsector $fkidsector
     *
     * @return Trecibopuestoeventual
     */
    public function setFkidsector(\ModeloBundle\Entity\Tsector $fkidsector = null)
    {
        $this->fkidsector = $fkidsector;

        return $this;
    }

    /**
     * Get fkidsector
     *
     * @return \ModeloBundle\Entity\Tsector
     */
    public function getFkidsector()
    {
        return $this->fkidsector;
    }
    /**
     * @var string
     */
    private $identificacionrecaudador;

    /**
     * @var string
     */
    private $nombrerecaudador;

    /**
     * @var string
     */
    private $apellidorecaudador;

    /**
     * @var \ModeloBundle\Entity\Tusuario
     */
    private $fkidusuariorecaudador;


    /**
     * Set identificacionrecaudador
     *
     * @param string $identificacionrecaudador
     *
     * @return Trecibopuestoeventual
     */
    public function setIdentificacionrecaudador($identificacionrecaudador)
    {
        $this->identificacionrecaudador = $identificacionrecaudador;

        return $this;
    }

    /**
     * Get identificacionrecaudador
     *
     * @return string
     */
    public function getIdentificacionrecaudador()
    {
        return $this->identificacionrecaudador;
    }

    /**
     * Set nombrerecaudador
     *
     * @param string $nombrerecaudador
     *
     * @return Trecibopuestoeventual
     */
    public function setNombrerecaudador($nombrerecaudador)
    {
        $this->nombrerecaudador = $nombrerecaudador;

        return $this;
    }

    /**
     * Get nombrerecaudador
     *
     * @return string
     */
    public function getNombrerecaudador()
    {
        return $this->nombrerecaudador;
    }

    /**
     * Set apellidorecaudador
     *
     * @param string $apellidorecaudador
     *
     * @return Trecibopuestoeventual
     */
    public function setApellidorecaudador($apellidorecaudador)
    {
        $this->apellidorecaudador = $apellidorecaudador;

        return $this;
    }

    /**
     * Get apellidorecaudador
     *
     * @return string
     */
    public function getApellidorecaudador()
    {
        return $this->apellidorecaudador;
    }

    /**
     * Set fkidusuariorecaudador
     *
     * @param \ModeloBundle\Entity\Tusuario $fkidusuariorecaudador
     *
     * @return Trecibopuestoeventual
     */
    public function setFkidusuariorecaudador(\ModeloBundle\Entity\Tusuario $fkidusuariorecaudador = null)
    {
        $this->fkidusuariorecaudador = $fkidusuariorecaudador;

        return $this;
    }

    /**
     * Get fkidusuariorecaudador
     *
     * @return \ModeloBundle\Entity\Tusuario
     */
    public function getFkidusuariorecaudador()
    {
        return $this->fkidusuariorecaudador;
    }
    /**
     * @var string
     */
    private $identificacionterceropuestoeventual;

    /**
     * @var string
     */
    private $nombreterceropuestoeventual;


    /**
     * Set identificacionterceropuestoeventual
     *
     * @param string $identificacionterceropuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setIdentificacionterceropuestoeventual($identificacionterceropuestoeventual)
    {
        $this->identificacionterceropuestoeventual = $identificacionterceropuestoeventual;

        return $this;
    }

    /**
     * Get identificacionterceropuestoeventual
     *
     * @return string
     */
    public function getIdentificacionterceropuestoeventual()
    {
        return $this->identificacionterceropuestoeventual;
    }

    /**
     * Set nombreterceropuestoeventual
     *
     * @param string $nombreterceropuestoeventual
     *
     * @return Trecibopuestoeventual
     */
    public function setNombreterceropuestoeventual($nombreterceropuestoeventual)
    {
        $this->nombreterceropuestoeventual = $nombreterceropuestoeventual;

        return $this;
    }

    /**
     * Get nombreterceropuestoeventual
     *
     * @return string
     */
    public function getNombreterceropuestoeventual()
    {
        return $this->nombreterceropuestoeventual;
    }
}
