<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tactividadcomercial;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TactividadcomercialController extends Controller
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
                        
                        $query = "SELECT pkidactividadcomercial,nombreactividadcomercial FROM tactividadcomercial where actividadcomercialactivo = true  order by nombreactividadcomercial ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $actividadcomercial = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'actividadcomercial' => $actividadcomercial,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_ACTIVIDAD_COMERCIALES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidactividadcomercial, codigoactividadcomercial, nombreactividadcomercial, actividadcomercialactivo, creacionactividadcomercial,
                        modificacionactividadcomercial,descripcionactividadcomercial FROM tactividadcomercial  order by nombreactividadcomercial ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $actividadcomercial = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidactividadcomercial = array("nombrecampo"=>"pkidactividadcomercial","nombreetiqueta"=>"Id Actividad Comercial","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombreactividadcomercial = array("nombrecampo"=>"nombreactividadcomercial","nombreetiqueta"=>"Nombre Actividad Comercial","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigoactividadcomercial = array("nombrecampo"=>"codigoactividadcomercial","nombreetiqueta"=>"Codigo Actividad Comercial","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripcionactividadcomercial = array("nombrecampo"=>"descripcionactividadcomercial","nombreetiqueta"=>"Descripcion Actividad Comercial","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $actividadcomercialactivo = array("nombrecampo"=>"actividadcomercialactivo","nombreetiqueta"=>"Actividad Comercial Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        
                        
                        array_push($cabeceras,$pkidactividadcomercial);
                        array_push($cabeceras,$nombreactividadcomercial);
                        array_push($cabeceras,$codigoactividadcomercial);
                        array_push($cabeceras,$descripcionactividadcomercial);
                        array_push($cabeceras,$actividadcomercialactivo);

                        $title=array("Nueva","Actividad Comercial");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'actividadcomercial' => $actividadcomercial,
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
                'modulo' => "Actividad Comercial",
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

    /*Este funcion realiza la inserccion de un Actividad Comercial nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigoactividadcomercial":"valor",
    "nombreactividadcomercial":"valor",
    "descripcionactividadcomercial":"valor",
    "actividadcomercialactivo":"valor"}
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

                    if (in_array("PERM_ACTIVIDAD_COMERCIALES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionactividadcomercial = new \Datetime("now");
                            $modificacionactividadcomercial = new \Datetime("now");

                            $codigoactividadcomercial = (isset($params->codigoactividadcomercial)) ? $params->codigoactividadcomercial : null;
                            $nombreactividadcomercial = (isset($params->nombreactividadcomercial)) ? $params->nombreactividadcomercial : null;
                            $actividadcomercialactivo = (isset($params->actividadcomercialactivo)) ? $params->actividadcomercialactivo : true;
                            $descripcionactividadcomercial = (isset($params->descripcionactividadcomercial)) ? $params->descripcionactividadcomercial : null;

                            if ($nombreactividadcomercial != null) {

                                $actividadcomercial = new Tactividadcomercial();
                                if($codigoactividadcomercial!=null){
                                $actividadcomercial->setCodigoactividadcomercial($codigoactividadcomercial);
                                }
                                if($descripcionactividadcomercial!=null){
                                    $actividadcomercial->setDescripcionactividadcomercial($descripcionactividadcomercial);
                                }
                                //aqui quede
                                $actividadcomercial->setNombreactividadcomercial($nombreactividadcomercial);
                                $actividadcomercial->setActividadcomercialactivo($actividadcomercialactivo);
                                $actividadcomercial->setCreacionactividadcomercial($creacionactividadcomercial);
                                $actividadcomercial->setModificacionactividadcomercial($modificacionactividadcomercial);

                                $isset_actividadcomercial = $em->getRepository('ModeloBundle:Tactividadcomercial')->findOneBy(array(
                                    "nombreactividadcomercial" => $nombreactividadcomercial,
                                ));

                                if (!is_object($isset_actividadcomercial)) {

                                    $em->persist($actividadcomercial);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Actividad Comercial creado !!',
                                        'actividadcomercial' => $actividadcomercial,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "actividadcomercial",
                                        "valoresrelevantes" => "idactividadcomercial" . ":" . $actividadcomercial->getPkidactividadcomercial(),
                                        "idelemento" => $actividadcomercial->getPkidactividadcomercial(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Actividad Comercial no creada, Duplicada !!',
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
                'modulo' => "Actividad Comercial",
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
    {"pkidactividadcomercial":"valor",
    "codigoactividadcomercial":"valor",
    "nombreactividadcomercial":"valor",
    "descripcionactividadcomercial":"valor",
    "actividadcomercialactivo":"valor"}
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

                    if (in_array("PERM_ACTIVIDAD_COMERCIALES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacionactividadcomercial = new \Datetime("now");
                            $modificacionactividadcomercial = new \Datetime("now");
                            $pkidactividadcomercial = (isset($params->pkidactividadcomercial)) ? $params->pkidactividadcomercial : null;
                            $codigoactividadcomercial = (isset($params->codigoactividadcomercial)) ? $params->codigoactividadcomercial : null;
                            $nombreactividadcomercial = (isset($params->nombreactividadcomercial)) ? $params->nombreactividadcomercial : null;
                            $actividadcomercialactivo = (isset($params->actividadcomercialactivo)) ? $params->actividadcomercialactivo : true;
                            $descripcionactividadcomercial = (isset($params->descripcionactividadcomercial)) ? $params->descripcionactividadcomercial : null;

                            if ($nombreactividadcomercial != null &&  $pkidactividadcomercial != null) {

                                $actividadcomercial = $this->getDoctrine()->getRepository("ModeloBundle:Tactividadcomercial")->findOneBy(array(
                                    "pkidactividadcomercial" => $pkidactividadcomercial,
                                ));

                                if(!is_object($actividadcomercial)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la actividadcomercial no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($codigoactividadcomercial!=null){
                                    $actividadcomercial->setCodigoactividadcomercial($codigoactividadcomercial);
                                    }
                                    if($descripcionactividadcomercial!=null){
                                        $actividadcomercial->setDescripcionactividadcomercial($descripcionactividadcomercial);
                                    }
                                if ($nombreactividadcomercial != null) {
                                    $nombreactividadcomercial_old = $actividadcomercial->getNombreactividadcomercial();
                                    $actividadcomercial_id = $actividadcomercial->getPkidactividadcomercial();

                                    $actividadcomercial->setNombreactividadcomercial("p");
                                    $em->persist($actividadcomercial);
                                    $em->flush();

                                    $isset_actividadcomercial = $em->getRepository('ModeloBundle:Tactividadcomercial')->findOneBy(array(
                                        "nombreactividadcomercial" => $nombreactividadcomercial,
                                    ));

                                    if (!is_object($isset_actividadcomercial)) {
                                        $actividadcomercial->setNombreactividadcomercial($nombreactividadcomercial);
                                    } else {
                                        $actividadcomercial_old_id = $em->getRepository('ModeloBundle:Tactividadcomercial')->findOneBy(array(
                                            "pkidactividadcomercial" => $actividadcomercial_id,
                                        ));

                                        $actividadcomercial_old_id->setNombreactividadcomercial($nombreactividadcomercial_old);
                                        $em->persist($actividadcomercial_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Actividad Comercial no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $actividadcomercial->setActividadcomercialactivo($actividadcomercialactivo);
                                //$actividadcomercial->setCreacionactividadcomercial($creacionactividadcomercial);
                                $actividadcomercial->setModificacionactividadcomercial($modificacionactividadcomercial);

                                $em->persist($actividadcomercial);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Actividad Comercial actualizado !!',
                                    'actividadcomercial' => $actividadcomercial,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Actividad Comercial",
                                    "valoresrelevantes" => "idactividadcomercial" . ":" . $actividadcomercial->getPkidactividadcomercial(),
                                    "idelemento" => $actividadcomercial->getPkidactividadcomercial(),
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
                'modulo' => "Actividad Comercial",
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
    {"pkidactividadcomercial":"valor"}
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

                    if (in_array("PERM_ACTIVIDAD_COMERCIALES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidactividadcomercial = (isset($params->pkidactividadcomercial)) ? $params->pkidactividadcomercial : null;

                            if ($pkidactividadcomercial != null) {

                                $actividadcomercial = $this->getDoctrine()->getRepository("ModeloBundle:Tactividadcomercial")->findOneBy(array(
                                    "pkidactividadcomercial" => $pkidactividadcomercial,
                                ));
                                
                                $isset_actividadcomercial_puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "fkidactividadcomercial" => $pkidactividadcomercial,
                                ));

                                if (!is_object($isset_actividadcomercial_puesto)) {
                            

                                            if (is_object($actividadcomercial)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Actividad Comercial",
                                                    "valoresrelevantes" => "idactividadcomercial" . ":" . $actividadcomercial->getPkidactividadcomercial(),
                                                    "idelemento" => $actividadcomercial->getPkidactividadcomercial(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($actividadcomercial);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Actividad Comercial se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Actividad Comercial a eliminar no existe !!',
                                                );
                                            }
                                            
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Actividad Comercial, pertenece a un puesto !!',
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
                'modulo' => "Actividad Comercial",
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
