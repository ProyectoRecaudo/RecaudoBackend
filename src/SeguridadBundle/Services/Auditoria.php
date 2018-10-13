<?php
namespace SeguridadBundle\Services;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\Intl\Intl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\Handler;
use ModeloBundle\Entity\Tauditoria;
use ModeloBundle\Entity\Texepcion;
use ModeloBundle\Entity\Tusuario;
use SeguridadBundle\Services\JwtAuth;

class Auditoria{
    public $manager;

    public function __construct($manager){
        $this->manager=$manager;
    }

    /*
     * Funcion para registrar las operaciones que se han realizado correctamente en insertar, eliminar o modificar cualquier tabla
     * recibe los datos en un json que contenga:
     * idusuario, Usuario que realiza la operacion
     * nombreusuario, Usuario que realiza la operacion
     * identificacion, Usuario que realiza la operacion
     * accion, accion realizada insertar, eliminar, modificar
     *      en caso de que la accion sea "insertar" se verifica si el dato fue insertado con el id del elemento
     *      si este id es igual a 0 se genera un error.     
     * tabla, tabla en donde se inserta, elimina o mofica
     * valoresrelevantes, valores importantes en el proceso
     * idelemento, id del elmento que se ha insertado, eliminado o modificado
     * origen, en donde se realiza la operacion sea web o movil
     */
    public function auditoria($auditoria)
    { 
        try{
            $respuesta = array(
                'status'=> 'sucess',
                'msg'  => 'Datos Insertados'
            );
    
            //$auditoria = $request->get('auditoria',null);
            $data = json_decode($auditoria);
    
            if($data == null){
                $respuesta = array(
                    'status'=> 'error',
                    'msg'  => 'auditoria vacio'
                );
            }else{

                $idUsuario = $data->idusuario;  
                $nombreUsuario = $data->nombreusuario;
                $identificacionUsuario = $data->identificacionusuario;   
                $tabla = $data->tabla;
                $valoresRelevantes = $data->valoresrelevantes;
                $accion = $data->accion;
                $creacionAuditoria = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                $idElemento = $data->idelemento;
                $origenAuditoria = $data->origen;
                
                
                /*
                *Cuando se realiza una insercion en cualquier tabla en la base de datos se verifica si esta fue ingresada correctamente
                *en caso de que la insercion no se haya realizado se genera una excepcion 
                */
                if($idElemento <= 0 && $accion = "insertar"){
                    throw new \LogicException('La inserciòn de datos no fue realizada'); //Genera el mensaje de error
                }else{
                    $auditoria = new Tauditoria();
                    $auditoria->setFkidusuario($idUsuario);
                    $auditoria->setNombreusuario($nombreUsuario);
                    $auditoria->setIdentificacionusuario($identificacionUsuario);
                    $auditoria->setTabla($tabla);
                    $auditoria->setValoresrelevantes($valoresRelevantes);
                    $auditoria->setAccion($accion);
                    $auditoria->setCreacionauditoria($creacionAuditoria);
                    $auditoria->setPkidelemento($idElemento);
                    $auditoria->setOrigenauditoria($origenAuditoria);
                    
                    $em = $this->manager;
                    $em->persist($auditoria);
                    $em->flush();
                }
            }             
            return new Response(json_encode($respuesta));

        }catch(\Exception $e){
            $datosError = json_decode($auditoria);
            $trace = $e->getTrace();
            $creacionExepcion = new \DateTime('now',new \DateTimeZone('America/Bogota'));

            try{
                $exepcion = new Texepcion();
                $exepcion->setFkidusuario($datosError->idusuario);
                $exepcion->setNombreusuario($datosError->nombreusuario);
                $exepcion->setModulo($datosError->tabla);
                $exepcion->setMetodo($trace[0]['function']);
                $exepcion->setMensaje($e->getMessage());
                $exepcion->setTipoexcepcion($trace[0]['class']);
                $exepcion->setPila($e->getTraceAsString());
                $exepcion->setOrigen($datosError->origen);
                $exepcion->setCreacionexcepcion($creacionExepcion);
    
                $em = $this->manager;
                $em->persist($exepcion);
                $em->flush();

            }catch (\Exception $a){

            }

            throw $e;
        }
    }

    /*
     * Funcion para registrar las operaciones en las que se ha producido algun error
     * Recibe los datos a insertar en un json
     * idusuario, Usuario que realiza la operacion
     * nombreusuario, Usuario que realiza la operacion
     * modulo, modulo en donde ocurre el error
     * metodo, nombre del método o función donde se capturó la excepción
     * tipo, el tipo o la clase de excepcion
     * pila, la pila o el stack de la excepción,
     * origen, en donde se realiza la operacion sea web o movil
     */
    public function exepcion($exepcion)
    {
        try{
            $respuesta = array(
                'status'=> 'sucess',
                'msg'  => 'Datos Insertados'
            );
    
            //$exepcion = $request->get('exepcion',null);
            $data = json_decode($exepcion);
    
            if($data == null){
                $respuesta = array(
                    'status'=> 'error',
                    'msg'  => 'exepcion vacio'
                );
            }else{
                $idUsuario = $data->idusuario;
                $nombreUsuario = $data->nombreusuario;
                $modulo = $data->modulo;
                $metodo = $data->metodo;
                $mensaje = $data->mensaje;
                $tipoExepcion = $data->tipoExepcion;
                $pila = $data->pila;
                $origen = $data->origen;
                $creacionExepcion = new \DateTime('now',new \DateTimeZone('America/Bogota'));
        
                $exepcion = new Texepcion();
                $exepcion->setFkidusuario($idUsuario);
                $exepcion->setNombreusuario($nombreUsuario);
                $exepcion->setModulo($modulo);
                $exepcion->setMetodo($metodo);
                $exepcion->setMensaje($mensaje);
                $exepcion->setTipoexcepcion($tipoExepcion);
                $exepcion->setPila($pila);
                $exepcion->setOrigen($origen);
                $exepcion->setCreacionexcepcion($creacionExepcion);
    
                $em = $this->manager;
                $em->persist($exepcion);
                $em->flush();
            }
    
            return new Response(json_encode($respuesta));

        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * @Route("/today")
     * Funcion que retorna la fecha actual con zona horaria colombiana. 
     */
    public function today()
    {
        $date =new \DateTime('now');
        $fmt = new IntlDateFormatter(
            'en',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'America/Bogota',
            IntlDateFormatter::GREGORIAN,
            'MM/dd/yyyy H:mm:ss'
        );
       
        $today = $fmt->format($date);
        $fecha = array("today" => $today);
        return new Response(json_encode($fecha));
    }
}