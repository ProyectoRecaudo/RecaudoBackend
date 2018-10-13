<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttipopuesto;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtipopuestoController extends Controller
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
                        
                        $query = "SELECT pkidtipopuesto, codigotipopuesto, nombretipopuesto, tipopuestoactivo, creaciontipopuesto,
                        modificaciontipopuesto,descripciontipopuesto FROM ttipopuesto where tipopuestoactivo = true  order by nombretipopuesto ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipopuesto = $stmt->fetchAll();

			        $data = array(
                            'status'    => 'Success',
                            'tipopuesto'     => $tipopuesto,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_TIPO_PUESTOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidtipopuesto, codigotipopuesto, nombretipopuesto, tipopuestoactivo, creaciontipopuesto,
                        modificaciontipopuesto,descripciontipopuesto FROM ttipopuesto  order by nombretipopuesto ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tipopuesto = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidtipopuesto = array("nombrecampo"=>"pkidtipopuesto","nombreetiqueta"=>"Id Tipo Puesto","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombretipopuesto = array("nombrecampo"=>"nombretipopuesto","nombreetiqueta"=>"Nombre Tipo Puesto","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigotipopuesto = array("nombrecampo"=>"codigotipopuesto","nombreetiqueta"=>"Codigo Tipo Puesto","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripciontipopuesto = array("nombrecampo"=>"descripciontipopuesto","nombreetiqueta"=>"Descripcion Tipo Puesto","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $tipopuestoactivo = array("nombrecampo"=>"tipopuestoactivo","nombreetiqueta"=>"Tipo Puesto Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        

                        array_push($cabeceras,$pkidtipopuesto);
                        array_push($cabeceras,$nombretipopuesto);
                        array_push($cabeceras,$codigotipopuesto);
                        array_push($cabeceras,$descripciontipopuesto);
                        array_push($cabeceras,$tipopuestoactivo);

                        $title=array("Nuevo","Tipo Puesto");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'tipopuesto' => $tipopuesto,
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
                'modulo' => "Tipo Puesto",
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

    /*Este funcion realiza la inserccion de un Tipo Puesto nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotipopuesto":"valor",
    "nombretipopuesto":"valor",
    "descripciontipopuesto":"valor",
    "tipopuestoactivo":"valor"}
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

                    if (in_array("PERM_TIPO_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontipopuesto = new \Datetime("now");
                            $modificaciontipopuesto = new \Datetime("now");

                            $codigotipopuesto = (isset($params->codigotipopuesto)) ? $params->codigotipopuesto : null;
                            $nombretipopuesto = (isset($params->nombretipopuesto)) ? $params->nombretipopuesto : null;
                            $tipopuestoactivo = (isset($params->tipopuestoactivo)) ? $params->tipopuestoactivo : true;
                            $descripciontipopuesto = (isset($params->descripciontipopuesto)) ? $params->descripciontipopuesto : null;

                            if ($nombretipopuesto != null) {

                                $tipopuesto = new Ttipopuesto();
                                if($codigotipopuesto!=null){
                                $tipopuesto->setCodigotipopuesto($codigotipopuesto);
                                }
                                if($descripciontipopuesto!=null){
                                    $tipopuesto->setDescripciontipopuesto($descripciontipopuesto);
                                }
                                //aqui quede
                                $tipopuesto->setNombretipopuesto($nombretipopuesto);
                                $tipopuesto->setTipopuestoactivo($tipopuestoactivo);
                                $tipopuesto->setCreaciontipopuesto($creaciontipopuesto);
                                $tipopuesto->setModificaciontipopuesto($modificaciontipopuesto);

                                $isset_tipopuesto = $em->getRepository('ModeloBundle:Ttipopuesto')->findOneBy(array(
                                    "nombretipopuesto" => $nombretipopuesto,
                                ));

                                if (!is_object($isset_tipopuesto)) {

                                    $em->persist($tipopuesto);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Tipo Puesto creado !!',
                                        'tipopuesto' => $tipopuesto,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "tipopuesto",
                                        "valoresrelevantes" => "idtipopuesto" . ":" . $tipopuesto->getPkidtipopuesto(),
                                        "idelemento" => $tipopuesto->getPkidtipopuesto(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tipo Puesto no creado, Duplicado !!',
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
                'modulo' => "Tipo Puesto",
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
    {"pkidtipopuesto":"valor",
    "codigotipopuesto":"valor",
    "nombretipopuesto":"valor",
    "descripciontipopuesto":"valor",
    "tipopuestoactivo":"valor"}
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

                    if (in_array("PERM_TIPO_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creaciontipopuesto = new \Datetime("now");
                            $modificaciontipopuesto = new \Datetime("now");
                            $pkidtipopuesto = (isset($params->pkidtipopuesto)) ? $params->pkidtipopuesto : null;
                            $codigotipopuesto = (isset($params->codigotipopuesto)) ? $params->codigotipopuesto : null;
                            $nombretipopuesto = (isset($params->nombretipopuesto)) ? $params->nombretipopuesto : null;
                            $tipopuestoactivo = (isset($params->tipopuestoactivo)) ? $params->tipopuestoactivo : true;
                            $descripciontipopuesto = (isset($params->descripciontipopuesto)) ? $params->descripciontipopuesto : null;

                            if ($nombretipopuesto != null  && $pkidtipopuesto != null) {

                                $tipopuesto = $this->getDoctrine()->getRepository("ModeloBundle:Ttipopuesto")->findOneBy(array(
                                    "pkidtipopuesto" => $pkidtipopuesto,
                                ));

                                if(!is_object($tipopuesto)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo de puesto no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($codigotipopuesto!=null){
                                    $tipopuesto->setCodigotipopuesto($codigotipopuesto);
                                    }
                                    if($descripciontipopuesto!=null){
                                        $tipopuesto->setDescripciontipopuesto($descripciontipopuesto);
                                    }
                                if ($nombretipopuesto != null) {
                                    $nombretipopuesto_old = $tipopuesto->getNombretipopuesto();
                                    $tipopuesto_id = $tipopuesto->getPkidtipopuesto();

                                    $tipopuesto->setNombretipopuesto("p");
                                    $em->persist($tipopuesto);
                                    $em->flush();

                                    $isset_tipopuesto = $em->getRepository('ModeloBundle:Ttipopuesto')->findOneBy(array(
                                        "nombretipopuesto" => $nombretipopuesto,
                                    ));

                                    if (!is_object($isset_tipopuesto)) {
                                        $tipopuesto->setNombretipopuesto($nombretipopuesto);
                                    } else {
                                        $tipopuesto_old_id = $em->getRepository('ModeloBundle:Ttipopuesto')->findOneBy(array(
                                            "pkidtipopuesto" => $tipopuesto_id,
                                        ));

                                        $tipopuesto_old_id->setNombretipopuesto($nombretipopuesto_old);
                                        $em->persist($tipopuesto_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Tipo Puesto no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $tipopuesto->setTipopuestoactivo($tipopuestoactivo);
                                //$tipopuesto->setCreaciontipopuesto($creaciontipopuesto);
                                $tipopuesto->setModificaciontipopuesto($modificaciontipopuesto);

                                $em->persist($tipopuesto);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Tipo Puesto actualizado !!',
                                    'tipopuesto' => $tipopuesto,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Tipo Puesto",
                                    "valoresrelevantes" => "idtipopuesto" . ":" . $tipopuesto->getPkidtipopuesto(),
                                    "idelemento" => $tipopuesto->getPkidtipopuesto(),
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
                'modulo' => "Tipo Puesto",
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
    {"pkidtipopuesto":"valor"}
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

                    if (in_array("PERM_TIPO_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidtipopuesto = (isset($params->pkidtipopuesto)) ? $params->pkidtipopuesto : null;

                            if ($pkidtipopuesto != null) {

                                $tipopuesto = $this->getDoctrine()->getRepository("ModeloBundle:Ttipopuesto")->findOneBy(array(
                                    "pkidtipopuesto" => $pkidtipopuesto,
                                ));

                                $isset_tipopuesto_puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "fkidtipopuesto" => $pkidtipopuesto,
                                ));

                                if (!is_object($isset_tipopuesto_puesto)) {

                                            if (is_object($tipopuesto)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Tipo Puesto",
                                                    "valoresrelevantes" => "idtipopuesto" . ":" . $tipopuesto->getPkidtipopuesto(),
                                                    "idelemento" => $tipopuesto->getPkidtipopuesto(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($tipopuesto);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Tipo Puesto se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Tipo Puesto a eliminar no existe !!',
                                                );
                                            }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Tipo Puesto, pertenece a un puesto !!',
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
                'modulo' => "Tipo Puesto",
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
