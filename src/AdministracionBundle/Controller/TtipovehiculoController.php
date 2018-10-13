<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttipovehiculo;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtipovehiculoController extends Controller
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

                        $query = "SELECT pkidtipovehiculo, codigotipovehiculo, nombretipovehiculo, tipovehiculoactivo, creaciontipovehiculo,
                        modificaciontipovehiculo,descripciontipovehiculo FROM ttipovehiculo where tipovehiculoactivo=true  order by nombretipovehiculo ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipovehiculo = $stmt->fetchAll();
                        
			$data = array(
                            'status'    => 'Success',
                            'tipovehiculo'     => $tipovehiculo,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_VEHICULOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidtipovehiculo, codigotipovehiculo, nombretipovehiculo, tipovehiculoactivo, creaciontipovehiculo,
                        modificaciontipovehiculo,descripciontipovehiculo FROM ttipovehiculo  order by nombretipovehiculo ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipovehiculo = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidtipovehiculo = array("nombrecampo"=>"pkidtipovehiculo","nombreetiqueta"=>"Id Tipo Vehiculo","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipovehiculo = array("nombrecampo"=>"nombretipovehiculo","nombreetiqueta"=>"Nombre Tipo Vehiculo","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotipovehiculo = array("nombrecampo"=>"codigotipovehiculo","nombreetiqueta"=>"Codigo Tipo Vehiculo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontipovehiculo = array("nombrecampo"=>"descripciontipovehiculo","nombreetiqueta"=>"Descripcion Tipo Vehiculo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tipovehiculoactivo = array("nombrecampo"=>"tipovehiculoactivo","nombreetiqueta"=>"Tipo Vehiculo Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        array_push($cabeceras,$pkidtipovehiculo);
                        array_push($cabeceras,$nombretipovehiculo);
                        array_push($cabeceras,$codigotipovehiculo);
                        array_push($cabeceras,$descripciontipovehiculo);
                        array_push($cabeceras,$tipovehiculoactivo);

                        $title=array("Nuevo","Tipo Vehiculo");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'tipovehiculo' => $tipovehiculo,
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
                'modulo' => "Tipo Vehiculo",
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

    /*Este funcion realiza la inserccion de un Tipo Vehiculo nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotipovehiculo":"valor",
    "nombretipovehiculo":"valor",
    "descripciontipovehiculo":"valor",
    "tipovehiculoactivo":"valor"}
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

                    if (in_array("PERM_TIPO_VEHICULOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontipovehiculo = new \Datetime("now");
                            $modificaciontipovehiculo = new \Datetime("now");

                            $codigotipovehiculo = (isset($params->codigotipovehiculo)) ? $params->codigotipovehiculo : null;
                            $nombretipovehiculo = (isset($params->nombretipovehiculo)) ? $params->nombretipovehiculo : null;
                            $tipovehiculoactivo = (isset($params->tipovehiculoactivo)) ? $params->tipovehiculoactivo : true;
                            $descripciontipovehiculo = (isset($params->descripciontipovehiculo)) ? $params->descripciontipovehiculo : null;

                            if ($nombretipovehiculo != null) {

                                $tipovehiculo = new Ttipovehiculo();
                                if($codigotipovehiculo!=null){
                                $tipovehiculo->setCodigotipovehiculo($codigotipovehiculo);
                                }
                                if($descripciontipovehiculo!=null){
                                    $tipovehiculo->setDescripciontipovehiculo($descripciontipovehiculo);
                                }
                                //aqui quede
                                $tipovehiculo->setNombretipovehiculo($nombretipovehiculo);
                                $tipovehiculo->setTipovehiculoactivo($tipovehiculoactivo);
                                $tipovehiculo->setCreaciontipovehiculo($creaciontipovehiculo);
                                $tipovehiculo->setModificaciontipovehiculo($modificaciontipovehiculo);

                                $isset_tipovehiculo = $em->getRepository('ModeloBundle:Ttipovehiculo')->findOneBy(array(
                                    "nombretipovehiculo" => $nombretipovehiculo,
                                ));

                                if (!is_object($isset_tipovehiculo)) {

                                    $em->persist($tipovehiculo);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Tipo Vehiculo creado !!',
                                        'tipovehiculo' => $tipovehiculo,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "tipovehiculo",
                                        "valoresrelevantes" => "idtipovehiculo" . ":" . $tipovehiculo->getPkidtipovehiculo(),
                                        "idelemento" => $tipovehiculo->getPkidtipovehiculo(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tipo Vehiculo no creado, Duplicado !!',
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
                'modulo' => "Tipo Vehiculo",
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
    {"pkidtipovehiculo":"valor",
    "codigotipovehiculo":"valor",
    "nombretipovehiculo":"valor",
    "descripciontipovehiculo":"valor",
    "tipovehiculoactivo":"valor"}
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

                    if (in_array("PERM_TIPO_VEHICULOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creaciontipovehiculo = new \Datetime("now");
                            $modificaciontipovehiculo = new \Datetime("now");
                            $pkidtipovehiculo = (isset($params->pkidtipovehiculo)) ? $params->pkidtipovehiculo : null;
                            $codigotipovehiculo = (isset($params->codigotipovehiculo)) ? $params->codigotipovehiculo : null;
                            $nombretipovehiculo = (isset($params->nombretipovehiculo)) ? $params->nombretipovehiculo : null;
                            $tipovehiculoactivo = (isset($params->tipovehiculoactivo)) ? $params->tipovehiculoactivo : true;
                            $descripciontipovehiculo = (isset($params->descripciontipovehiculo)) ? $params->descripciontipovehiculo : null;

                            if ($nombretipovehiculo != null  && $pkidtipovehiculo != null) {

                                $tipovehiculo = $this->getDoctrine()->getRepository("ModeloBundle:Ttipovehiculo")->findOneBy(array(
                                    "pkidtipovehiculo" => $pkidtipovehiculo,
                                ));

                                if(!is_object($tipovehiculo)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo vehiculo no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($codigotipovehiculo!=null){
                                    $tipovehiculo->setCodigotipovehiculo($codigotipovehiculo);
                                    }
                                    if($descripciontipovehiculo!=null){
                                        $tipovehiculo->setDescripciontipovehiculo($descripciontipovehiculo);
                                    }
                                if ($nombretipovehiculo != null) {
                                    $nombretipovehiculo_old = $tipovehiculo->getNombretipovehiculo();
                                    $tipovehiculo_id = $tipovehiculo->getPkidtipovehiculo();

                                    $tipovehiculo->setNombretipovehiculo("p");
                                    $em->persist($tipovehiculo);
                                    $em->flush();

                                    $isset_tipovehiculo = $em->getRepository('ModeloBundle:Ttipovehiculo')->findOneBy(array(
                                        "nombretipovehiculo" => $nombretipovehiculo,
                                    ));

                                    if (!is_object($isset_tipovehiculo)) {
                                        $tipovehiculo->setNombretipovehiculo($nombretipovehiculo);
                                    } else {
                                        $tipovehiculo_old_id = $em->getRepository('ModeloBundle:Ttipovehiculo')->findOneBy(array(
                                            "pkidtipovehiculo" => $tipovehiculo_id,
                                        ));

                                        $tipovehiculo_old_id->setNombretipovehiculo($nombretipovehiculo_old);
                                        $em->persist($tipovehiculo_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Tipo Vehiculo no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $tipovehiculo->setTipovehiculoactivo($tipovehiculoactivo);
                                //$tipovehiculo->setCreaciontipovehiculo($creaciontipovehiculo);
                                $tipovehiculo->setModificaciontipovehiculo($modificaciontipovehiculo);

                                $em->persist($tipovehiculo);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Tipo Vehiculo actualizado !!',
                                    'tipovehiculo' => $tipovehiculo,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Tipo Vehiculo",
                                    "valoresrelevantes" => "idtipovehiculo" . ":" . $tipovehiculo->getPkidtipovehiculo(),
                                    "idelemento" => $tipovehiculo->getPkidtipovehiculo(),
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
                'modulo' => "Tipo Vehiculo",
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
    {"pkidtipovehiculo":"valor"}
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

                    if (in_array("PERM_TIPO_VEHICULOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidtipovehiculo = (isset($params->pkidtipovehiculo)) ? $params->pkidtipovehiculo : null;

                            if ($pkidtipovehiculo != null) {

                                $tipovehiculo = $this->getDoctrine()->getRepository("ModeloBundle:Ttipovehiculo")->findOneBy(array(
                                    "pkidtipovehiculo" => $pkidtipovehiculo,
                                ));

                                /*
                                $isset_tipovehiculo_cualquiera = $this->getDoctrine()->getRepository("ModeloBundle:Tcualquiera")->findOneBy(array(
                                    "fkidtipovehiculo" => $pkidtipovehiculo,
                                ));

                                if (!is_object($isset_tipovehiculo_cualquiera)) {
                                    */
                                            if (is_object($tipovehiculo)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Tipo Vehiculo",
                                                    "valoresrelevantes" => "idtipovehiculo" . ":" . $tipovehiculo->getPkidtipovehiculo(),
                                                    "idelemento" => $tipovehiculo->getPkidtipovehiculo(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($tipovehiculo);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Tipo Vehiculo se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Tipo Vehiculo a eliminar no existe !!',
                                                );
                                            }
                                            /*
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Tipo Vehiculo, pertenece a un cualquiera !!',
                                    );
                                }
                                */

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
                'modulo' => "Tipo Vehiculo",
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
