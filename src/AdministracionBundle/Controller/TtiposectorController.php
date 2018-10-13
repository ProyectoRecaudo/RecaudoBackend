<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttiposector;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtiposectorController extends Controller
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
                        
                        $query = "SELECT pkidtiposector, codigotiposector, nombretiposector, tiposectoractivo, creaciontiposector,
                        modificaciontiposector,descripciontiposector FROM ttiposector where tiposectoractivo=true  order by nombretiposector ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tiposector = $stmt->fetchAll();

			            $data = array(
                            'status'    => 'Success',
                            'tiposector'     => $tiposector,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_SECTORES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidtiposector, codigotiposector, nombretiposector, tiposectoractivo, creaciontiposector,
                        modificaciontiposector,descripciontiposector FROM ttiposector  order by nombretiposector ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tiposector = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidtiposector = array("nombrecampo"=>"pkidtiposector","nombreetiqueta"=>"Id Tipo Sector","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretiposector = array("nombrecampo"=>"nombretiposector","nombreetiqueta"=>"Nombre Tipo Sector","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotiposector = array("nombrecampo"=>"codigotiposector","nombreetiqueta"=>"Codigo Tipo Sector","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontiposector = array("nombrecampo"=>"descripciontiposector","nombreetiqueta"=>"Descripcion Tipo Sector","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tiposectoractivo = array("nombrecampo"=>"tiposectoractivo","nombreetiqueta"=>"Tipo Sector Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        array_push($cabeceras,$pkidtiposector);
                        array_push($cabeceras,$nombretiposector);
                        array_push($cabeceras,$codigotiposector);
                        array_push($cabeceras,$descripciontiposector);
                        array_push($cabeceras,$tiposectoractivo);

                        $title=array("Nuevo","Tipo Sector");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'tiposector' => $tiposector,
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
                'modulo' => "Tipo Sector",
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

    /*Este funcion realiza la inserccion de un tipo sector nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotiposector":"valor",
    "nombretiposector":"valor",
    "descripciontiposector":"valor",
    "tiposectoractivo":"valor"}
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

                    if (in_array("PERM_TIPO_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontiposector = new \Datetime("now");
                            $modificaciontiposector = new \Datetime("now");

                            $codigotiposector = (isset($params->codigotiposector)) ? $params->codigotiposector : null;
                            $nombretiposector = (isset($params->nombretiposector)) ? $params->nombretiposector : null;
                            $tiposectoractivo = (isset($params->tiposectoractivo)) ? $params->tiposectoractivo : true;
                            $descripciontiposector = (isset($params->descripciontiposector)) ? $params->descripciontiposector : null;

                            if ($nombretiposector != null) {

                                $tiposector = new Ttiposector();
                                if($codigotiposector!=null){
                                $tiposector->setCodigotiposector($codigotiposector);
                                }
                                if($descripciontiposector!=null){
                                    $tiposector->setDescripciontiposector($descripciontiposector);
                                }
                                //aqui quede
                                $tiposector->setNombretiposector($nombretiposector);
                                $tiposector->setTiposectoractivo($tiposectoractivo);
                                $tiposector->setCreaciontiposector($creaciontiposector);
                                $tiposector->setModificaciontiposector($modificaciontiposector);

                                $isset_tiposector = $em->getRepository('ModeloBundle:Ttiposector')->findOneBy(array(
                                    "nombretiposector" => $nombretiposector,
                                ));

                                if (!is_object($isset_tiposector)) {

                                    $em->persist($tiposector);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Tipo Sector creado !!',
                                        'tiposector' => $tiposector,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "tiposector",
                                        "valoresrelevantes" => "idtiposector" . ":" . $tiposector->getPkidtiposector(),
                                        "idelemento" => $tiposector->getPkidtiposector(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tipo Sector no creado, Duplicado !!',
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
                'modulo' => "Tipo Sector",
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
    {"pkidtiposector":"valor",
    "codigotiposector":"valor",
    "nombretiposector":"valor",
    "descripciontiposector":"valor",
    "tiposectoractivo":"valor"}
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

                    if (in_array("PERM_TIPO_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creaciontiposector = new \Datetime("now");
                            $modificaciontiposector = new \Datetime("now");
                            $pkidtiposector = (isset($params->pkidtiposector)) ? $params->pkidtiposector : null;
                            $codigotiposector = (isset($params->codigotiposector)) ? $params->codigotiposector : null;
                            $nombretiposector = (isset($params->nombretiposector)) ? $params->nombretiposector : null;
                            $tiposectoractivo = (isset($params->tiposectoractivo)) ? $params->tiposectoractivo : true;
                            $descripciontiposector = (isset($params->descripciontiposector)) ? $params->descripciontiposector : null;

                            if ($nombretiposector != null  && $pkidtiposector != null) {

                                $tiposector = $this->getDoctrine()->getRepository("ModeloBundle:Ttiposector")->findOneBy(array(
                                    "pkidtiposector" => $pkidtiposector,
                                ));

                                if(!is_object($tiposector)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo sector no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($codigotiposector!=null){
                                    $tiposector->setCodigotiposector($codigotiposector);
                                    }
                                    if($descripciontiposector!=null){
                                        $tiposector->setDescripciontiposector($descripciontiposector);
                                    }
                                if ($nombretiposector != null) {
                                    $nombretiposector_old = $tiposector->getNombretiposector();
                                    $tiposector_id = $tiposector->getPkidtiposector();

                                    $tiposector->setNombretiposector("p");
                                    $em->persist($tiposector);
                                    $em->flush();

                                    $isset_tiposector = $em->getRepository('ModeloBundle:Ttiposector')->findOneBy(array(
                                        "nombretiposector" => $nombretiposector,
                                    ));

                                    if (!is_object($isset_tiposector)) {
                                        $tiposector->setNombretiposector($nombretiposector);
                                    } else {
                                        $tiposector_old_id = $em->getRepository('ModeloBundle:Ttiposector')->findOneBy(array(
                                            "pkidtiposector" => $tiposector_id,
                                        ));

                                        $tiposector_old_id->setNombretiposector($nombretiposector_old);
                                        $em->persist($tiposector_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Tipo Sector no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $tiposector->setTiposectoractivo($tiposectoractivo);
                                //$tiposector->setCreaciontiposector($creaciontiposector);
                                $tiposector->setModificaciontiposector($modificaciontiposector);

                                $em->persist($tiposector);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Tipo Sector actualizado !!',
                                    'tiposector' => $tiposector,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Tipo Sector",
                                    "valoresrelevantes" => "idtiposector" . ":" . $tiposector->getPkidtiposector(),
                                    "idelemento" => $tiposector->getPkidtiposector(),
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
                'modulo' => "Tipo Sector",
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
    {"pkidtiposector":"valor"}
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

                    if (in_array("PERM_TIPO_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidtiposector = (isset($params->pkidtiposector)) ? $params->pkidtiposector : null;

                            if ($pkidtiposector != null) {

                                $tiposector = $this->getDoctrine()->getRepository("ModeloBundle:Ttiposector")->findOneBy(array(
                                    "pkidtiposector" => $pkidtiposector,
                                ));

                                $isset_tiposector_sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "fkidtiposector" => $pkidtiposector,
                                ));

                                if (!is_object($isset_tiposector_sector)) {

                                            if (is_object($tiposector)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Tipo Sector",
                                                    "valoresrelevantes" => "idtiposector" . ":" . $tiposector->getPkidtiposector(),
                                                    "idelemento" => $tiposector->getPkidtiposector(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($tiposector);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Tipo Sector se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Tipo Sector a eliminar no existe !!',
                                                );
                                            }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el tipo sector, pertenece a un sector !!',
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
                'modulo' => "Tipo Sector",
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
