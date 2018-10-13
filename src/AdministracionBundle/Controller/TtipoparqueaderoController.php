<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttipoparqueadero;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtipoparqueaderoController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los tipos de sector a la base de datos
    se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    /**
     * @Route("/query")
     */
    public function queryAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        try {
            if (!empty($request->get('authorization'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        
                        $query = "SELECT pkidtipoparqueadero, codigotipoparqueadero, nombretipoparqueadero, tipoparqueaderoactivo, creaciontipoparqueadero,
                        modificaciontipoparqueadero,descripciontipoparqueadero FROM ttipoparqueadero where tipoparqueaderoactivo=true  order by nombretipoparqueadero ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipoparqueadero = $stmt->fetchAll();

			                $data = array(
                            'status'    => 'Success',
                            'tipoparqueadero'     => $tipoparqueadero,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_PARQUEADEROS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidtipoparqueadero, codigotipoparqueadero, nombretipoparqueadero, tipoparqueaderoactivo, creaciontipoparqueadero,
                        modificaciontipoparqueadero,descripciontipoparqueadero FROM ttipoparqueadero  order by nombretipoparqueadero ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipoparqueadero = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidtipoparqueadero = array("nombrecampo"=>"pkidtipoparqueadero","nombreetiqueta"=>"Id Tipo Parqueadero","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipoparqueadero = array("nombrecampo"=>"nombretipoparqueadero","nombreetiqueta"=>"Nombre Tipo Parqueadero","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotipoparqueadero = array("nombrecampo"=>"codigotipoparqueadero","nombreetiqueta"=>"Codigo Tipo Parqueadero","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontipoparqueadero = array("nombrecampo"=>"descripciontipoparqueadero","nombreetiqueta"=>"Descripcion Tipo Parqueadero","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tipoparqueaderoactivo = array("nombrecampo"=>"tipoparqueaderoactivo","nombreetiqueta"=>"Tipo Parqueadero Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        array_push($cabeceras,$pkidtipoparqueadero);
                        array_push($cabeceras,$nombretipoparqueadero);
                        array_push($cabeceras,$codigotipoparqueadero);
                        array_push($cabeceras,$descripciontipoparqueadero);
                        array_push($cabeceras,$tipoparqueaderoactivo);

                        $title=array("Nuevo","Tipo Parqueadero");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'tipoparqueadero' => $tipoparqueadero,
                            'title' => $title
                        );

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'El usuario no tiene permisos !!',
                        );
                    }

                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Acceso no autorizado !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor !!',
                );
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Parqueadero",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }

    }

    /*Este funcion realiza la inserccion de un Tipo Parqueadero nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotipoparqueadero":"valor",
    "nombretipoparqueadero":"valor",
    "descripciontipoparqueadero":"valor",
    "tipoparqueaderoactivo":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/new")
     */
    public function newAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

//inicia codigo bien
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_TIPO_PARQUEADEROS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontipoparqueadero = new \Datetime("now");
                            $modificaciontipoparqueadero = new \Datetime("now");

                            $codigotipoparqueadero = (isset($params->codigotipoparqueadero)) ? $params->codigotipoparqueadero : null;
                            $nombretipoparqueadero = (isset($params->nombretipoparqueadero)) ? $params->nombretipoparqueadero : null;
                            $tipoparqueaderoactivo = (isset($params->tipoparqueaderoactivo)) ? $params->tipoparqueaderoactivo : true;
                            $descripciontipoparqueadero = (isset($params->descripciontipoparqueadero)) ? $params->descripciontipoparqueadero : null;

                            if ($nombretipoparqueadero != null) {

                                $tipoparqueadero = new Ttipoparqueadero();
                                if($codigotipoparqueadero!=null){
                                $tipoparqueadero->setCodigotipoparqueadero($codigotipoparqueadero);
                                }
                                if($descripciontipoparqueadero!=null){
                                    $tipoparqueadero->setDescripciontipoparqueadero($descripciontipoparqueadero);
                                }
                                //aqui quede
                                $tipoparqueadero->setNombretipoparqueadero($nombretipoparqueadero);
                                $tipoparqueadero->setTipoparqueaderoactivo($tipoparqueaderoactivo);
                                $tipoparqueadero->setCreaciontipoparqueadero($creaciontipoparqueadero);
                                $tipoparqueadero->setModificaciontipoparqueadero($modificaciontipoparqueadero);

                                $isset_tipoparqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->findOneBy(array(
                                    "nombretipoparqueadero" => $nombretipoparqueadero,
                                ));

                                if (!is_object($isset_tipoparqueadero)) {

                                    $em->persist($tipoparqueadero);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Tipo Parqueadero creado !!',
                                        'tipoparqueadero' => $tipoparqueadero,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "tipoparqueadero",
                                        "valoresrelevantes" => "idtipoparqueadero" . ":" . $tipoparqueadero->getPkidtipoparqueadero(),
                                        "idelemento" => $tipoparqueadero->getPkidtipoparqueadero(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tipo Parqueadero no creado, Duplicado !!',
                                    );
                                }
                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
                                );
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Parametro json es nulo!!',
                            );
                        }

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Parqueadero",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }
    }

    /*Esta funcion realiza la actualizacion de un tipo de sector,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidtipoparqueadero":"valor",
    "codigotipoparqueadero":"valor",
    "nombretipoparqueadero":"valor",
    "descripciontipoparqueadero":"valor",
    "tipoparqueaderoactivo":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/edit")
     */
    public function editAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

//inicia codigo bien
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_TIPO_PARQUEADEROS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creaciontipoparqueadero = new \Datetime("now");
                            $modificaciontipoparqueadero = new \Datetime("now");
                            $pkidtipoparqueadero = (isset($params->pkidtipoparqueadero)) ? $params->pkidtipoparqueadero : null;
                            $codigotipoparqueadero = (isset($params->codigotipoparqueadero)) ? $params->codigotipoparqueadero : null;
                            $nombretipoparqueadero = (isset($params->nombretipoparqueadero)) ? $params->nombretipoparqueadero : null;
                            $tipoparqueaderoactivo = (isset($params->tipoparqueaderoactivo)) ? $params->tipoparqueaderoactivo : true;
                            $descripciontipoparqueadero = (isset($params->descripciontipoparqueadero)) ? $params->descripciontipoparqueadero : null;

                            if ($nombretipoparqueadero != null ) {

                                $tipoparqueadero = $this->getDoctrine()->getRepository("ModeloBundle:Ttipoparqueadero")->findOneBy(array(
                                    "pkidtipoparqueadero" => $pkidtipoparqueadero,
                                ));

                                if(!is_object($tipoparqueadero)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo de parqueadero no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($codigotipoparqueadero!=null){
                                    $tipoparqueadero->setCodigotipoparqueadero($codigotipoparqueadero);
                                    }
                                    if($descripciontipoparqueadero!=null){
                                        $tipoparqueadero->setDescripciontipoparqueadero($descripciontipoparqueadero);
                                    }
                                if ($nombretipoparqueadero != null) {
                                    $nombretipoparqueadero_old = $tipoparqueadero->getNombretipoparqueadero();
                                    $tipoparqueadero_id = $tipoparqueadero->getPkidtipoparqueadero();

                                    $tipoparqueadero->setNombretipoparqueadero("p");
                                    $em->persist($tipoparqueadero);
                                    $em->flush();

                                    $isset_tipoparqueadero = $em->getRepository('ModeloBundle:Ttipoparqueadero')->findOneBy(array(
                                        "nombretipoparqueadero" => $nombretipoparqueadero,
                                    ));

                                    if (!is_object($isset_tipoparqueadero)) {
                                        $tipoparqueadero->setNombretipoparqueadero($nombretipoparqueadero);
                                    } else {
                                        $tipoparqueadero_old_id = $em->getRepository('ModeloBundle:Ttipoparqueadero')->findOneBy(array(
                                            "pkidtipoparqueadero" => $tipoparqueadero_id,
                                        ));

                                        $tipoparqueadero_old_id->setNombretipoparqueadero($nombretipoparqueadero_old);
                                        $em->persist($tipoparqueadero_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Tipo Parqueadero no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $tipoparqueadero->setTipoparqueaderoactivo($tipoparqueaderoactivo);
                                $tipoparqueadero->setModificaciontipoparqueadero($modificaciontipoparqueadero);

                                $em->persist($tipoparqueadero);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Tipo Parqueadero actualizado !!',
                                    'tipoparqueadero' => $tipoparqueadero,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Tipo Parqueadero",
                                    "valoresrelevantes" => "idtipoparqueadero" . ":" . $tipoparqueadero->getPkidtipoparqueadero(),
                                    "idelemento" => $tipoparqueadero->getPkidtipoparqueadero(),
                                    "origen" => "web",
                                );

                                $auditoria = $this->get(Auditoria::class);
                                $auditoria->auditoria(json_encode($datos));

                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
                                );
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Parametro json es nulo!!',
                            );
                        }

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Parqueadero",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }
    }

    /*Este funcion realiza la eliminacion de un tipo de sector
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidtipoparqueadero":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario.
     */
    /**
     * @Route("/remove")
     */
    public function removeAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_TIPO_PARQUEADEROS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidtipoparqueadero = (isset($params->pkidtipoparqueadero)) ? $params->pkidtipoparqueadero : null;

                            if ($pkidtipoparqueadero != null) {

                                $tipoparqueadero = $this->getDoctrine()->getRepository("ModeloBundle:Ttipoparqueadero")->findOneBy(array(
                                    "pkidtipoparqueadero" => $pkidtipoparqueadero,
                                ));

                                
                                $isset_tipoparqueadero_cualquiera = $this->getDoctrine()->getRepository("ModeloBundle:Tparqueadero")->findOneBy(array(
                                    "fkidtipoparqueadero" => $pkidtipoparqueadero,
                                ));

                                if (!is_object($isset_tipoparqueadero_cualquiera)) {
                                    
                                            if (is_object($tipoparqueadero)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Tipo Parqueadero",
                                                    "valoresrelevantes" => "idtipoparqueadero" . ":" . $tipoparqueadero->getPkidtipoparqueadero(),
                                                    "idelemento" => $tipoparqueadero->getPkidtipoparqueadero(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($tipoparqueadero);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Tipo Parqueadero se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Tipo Parqueadero a eliminar no existe !!',
                                                );
                                            }
                                            
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Tipo Parqueadero, pertenece a un parqueadero !!',
                                    );
                                }
                                

                            } else {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Algunos campos son nulos !!',
                                );
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'Parametro json es nulo!!',
                            );
                        }

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg' => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Token no valido !!',
                    );

                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg' => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tipo Parqueadero",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));
            } catch (\Exception $a) {

            }

            throw $e;

        }
    }

    //Fin clase
}
