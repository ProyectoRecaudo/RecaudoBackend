<?php
namespace SeguridadBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\Handler;
use ModeloBundle\Entity\Tauditoria;
use ModeloBundle\Entity\Texepcion;
use ModeloBundle\Entity\Tusuario;
use SeguridadBundle\Services\JwtAuth;
use SeguridadBundle\Services\Auditoria;

class ExcepcionController extends Controller{
    /**
     * @Route("/excepcion")
     * 
     * Funcion para registrar las operaciones en las que se ha producido algun error
     * Recibe los datos a insertar en un json llamado jzon
     * fkidusuario, Usuario que realiza la operacion
     * nombreusuario, Usuario que realiza la operacion
     * modulo, modulo en donde ocurre el error
     * mensaje, mensaje de error
     * metodo, nombre del método o función donde se capturó la excepción
     * tipo, el tipo o la clase de excepcion
     * pila, la pila o el stack de la excepción,
     * origen, en donde se realiza la operacion sea web o movil
     * tambien recibe el token de autorizacion en una variagle authorization.
     */
    public function excepcionAction(Request $request)
    { 

        $token = $request->get('authorization',null);

        $jwt_auth = $this->get(JwtAuth::class);
        $check = $jwt_auth->checkToken($token);

        if($check){
            
            $json = $request->get('json',null);
            $params = json_decode($json);
    
            $user = $jwt_auth->checkToken($token,true);

            $idUsuario = (isset($params->fkidusuario)) ? $params->fkidusuario : null;
            $nombreUsuario = (isset($params->nombreusuario)) ? $params->nombreusuario : null;
            $modulo = (isset($params->modulo)) ? $params->modulo : null;
            $metodo = (isset($params->metodo)) ? $params->metodo : null;
            $mensaje = (isset($params->mensaje)) ? $params->mensaje : null;
            $tipoExepcion = (isset($params->tipoexepcion)) ? $params->tipoexepcion : null;
            $pila = (isset($params->pila)) ? $params->pila : null;
            $origen = (isset($params->origen)) ? $params->origen : null;
            $creacionExepcion = new \DateTime('now');
            
            $excepcion = new Texepcion();
            $excepcion->setFkidusuario($idUsuario);
            $excepcion->setNombreusuario($nombreUsuario);
            $excepcion->setModulo($modulo);
            $excepcion->setMetodo($metodo);
            $excepcion->setMensaje($mensaje);
            $excepcion->setTipoexcepcion($tipoExepcion);
            $excepcion->setPila($pila);
            $excepcion->setOrigen($origen);
            $excepcion->setCreacionexcepcion($creacionExepcion);

            $em = $this->getDoctrine()->getManager();
            $em->persist($excepcion);
            $em->flush();
           
            $respuesta = array(
                'status'=> 'sucess',
                'msg'  => 'Datos Insertados'
            );
            
        }else{
            $respuesta = array(
                'status'=> 'error',
                'msg'  => 'Autorizacion no valida'
            );
        }

        return new Response(json_encode($respuesta));
    }
}