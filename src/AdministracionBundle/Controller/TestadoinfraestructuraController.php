<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Testadoinfraestructura;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TestadoinfraestructuraController extends Controller
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
                        
                        $query = "SELECT pkidestadoinfraestructura, codigoestadoinfraestructura, nombreestadoinfraestructura, estadoinfraestructuraactivo, creacionestadoinfraestructura,
                        modificacionestadoinfraestructura,descripcionestadoinfraestructura FROM Testadoinfraestructura where estadoinfraestructuraactivo = true  order by nombreestadoinfraestructura ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $estadoinfraestructura = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'estadoinfraestructura' => $estadoinfraestructura,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_ESTADO_INFRAESTRUCTURAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidestadoinfraestructura, codigoestadoinfraestructura, nombreestadoinfraestructura, estadoinfraestructuraactivo, creacionestadoinfraestructura,
                        modificacionestadoinfraestructura,descripcionestadoinfraestructura FROM Testadoinfraestructura  order by nombreestadoinfraestructura ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $estadoinfraestructura = $stmt->fetchAll();

                        $cabeceras=array();

                        $pkidestadoinfraestructura = array("nombrecampo"=>"pkidestadoinfraestructura","nombreetiqueta"=>"Id Estado Infraestructura","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombreestadoinfraestructura = array("nombrecampo"=>"nombreestadoinfraestructura","nombreetiqueta"=>"Nombre Estado Infraestructura","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $codigoestadoinfraestructura = array("nombrecampo"=>"codigoestadoinfraestructura","nombreetiqueta"=>"Codigo Estado Infraestructura","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $descripcionestadoinfraestructura = array("nombrecampo"=>"descripcionestadoinfraestructura","nombreetiqueta"=>"Descripcion Estado Infraestructura","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $estadoinfraestructuraactivo = array("nombrecampo"=>"estadoinfraestructuraactivo","nombreetiqueta"=>"Estado Infraestructura Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        
                        
                        array_push($cabeceras,$pkidestadoinfraestructura);
                        array_push($cabeceras,$nombreestadoinfraestructura);
                        array_push($cabeceras,$codigoestadoinfraestructura);
                        array_push($cabeceras,$estadoinfraestructuraactivo);
                        array_push($cabeceras,$descripcionestadoinfraestructura);

                        $title=array("Nuevo","Estado Infraestructura");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'estadoinfraestructura' => $estadoinfraestructura,
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
                'modulo' => "Estado Infraestructura",
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

    /*Este funcion realiza la inserccion de un Estado Infraestructura nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigoestadoinfraestructura":"valor",
    "nombreestadoinfraestructura":"valor",
    "descripcionestadoinfraestructura":"valor",
    "estadoinfraestructuraactivo":"valor"}
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

                    if (in_array("PERM_ESTADO_INFRAESTRUCTURAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionestadoinfraestructura = new \Datetime("now");
                            $modificacionestadoinfraestructura = new \Datetime("now");

                            $codigoestadoinfraestructura = (isset($params->codigoestadoinfraestructura)) ? $params->codigoestadoinfraestructura : null;
                            $nombreestadoinfraestructura = (isset($params->nombreestadoinfraestructura)) ? $params->nombreestadoinfraestructura : null;
                            $estadoinfraestructuraactivo = (isset($params->estadoinfraestructuraactivo)) ? $params->estadoinfraestructuraactivo : true;
                            $descripcionestadoinfraestructura = (isset($params->descripcionestadoinfraestructura)) ? $params->descripcionestadoinfraestructura : null;

                            if ($nombreestadoinfraestructura != null) {

                                $estadoinfraestructura = new Testadoinfraestructura();
                                if ($codigoestadoinfraestructura != null) {
                                    $estadoinfraestructura->setCodigoestadoinfraestructura($codigoestadoinfraestructura);
                                }
                                if ($descripcionestadoinfraestructura != null) {
                                    $estadoinfraestructura->setDescripcionestadoinfraestructura($descripcionestadoinfraestructura);
                                }
                                //aqui quede
                                $estadoinfraestructura->setNombreestadoinfraestructura($nombreestadoinfraestructura);
                                $estadoinfraestructura->setEstadoinfraestructuraactivo($estadoinfraestructuraactivo);
                                $estadoinfraestructura->setCreacionestadoinfraestructura($creacionestadoinfraestructura);
                                $estadoinfraestructura->setModificacionestadoinfraestructura($modificacionestadoinfraestructura);

                                $isset_estadoinfraestructura = $em->getRepository('ModeloBundle:Testadoinfraestructura')->findOneBy(array(
                                    "nombreestadoinfraestructura" => $nombreestadoinfraestructura,
                                ));

                                if (!is_object($isset_estadoinfraestructura)) {

                                    $em->persist($estadoinfraestructura);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Estado Infraestructura creado !!',
                                        'estadoinfraestructura' => $estadoinfraestructura,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "estadoinfraestructura",
                                        "valoresrelevantes" => "idestadoinfraestructura" . ":" . $estadoinfraestructura->getPkidestadoinfraestructura(),
                                        "idelemento" => $estadoinfraestructura->getPkidestadoinfraestructura(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Estado Infraestructura no creado, Duplicado !!',
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
                'modulo' => "Estado Infraestructura",
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
    {"pkidestadoinfraestructura":"valor",
    "codigoestadoinfraestructura":"valor",
    "nombreestadoinfraestructura":"valor",
    "descripcionestadoinfraestructura":"valor",
    "estadoinfraestructuraactivo":"valor"}
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

                    if (in_array("PERM_ESTADO_INFRAESTRUCTURAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacionestadoinfraestructura = new \Datetime("now");
                            $modificacionestadoinfraestructura = new \Datetime("now");
                            $pkidestadoinfraestructura = (isset($params->pkidestadoinfraestructura)) ? $params->pkidestadoinfraestructura : null;
                            $codigoestadoinfraestructura = (isset($params->codigoestadoinfraestructura)) ? $params->codigoestadoinfraestructura : null;
                            $nombreestadoinfraestructura = (isset($params->nombreestadoinfraestructura)) ? $params->nombreestadoinfraestructura : null;
                            $estadoinfraestructuraactivo = (isset($params->estadoinfraestructuraactivo)) ? $params->estadoinfraestructuraactivo : true;
                            $descripcionestadoinfraestructura = (isset($params->descripcionestadoinfraestructura)) ? $params->descripcionestadoinfraestructura : null;

                            if ($nombreestadoinfraestructura != null && $pkidestadoinfraestructura != null) {

                                $estadoinfraestructura = $this->getDoctrine()->getRepository("ModeloBundle:Testadoinfraestructura")->findOneBy(array(
                                    "pkidestadoinfraestructura" => $pkidestadoinfraestructura,
                                ));

                                if(!is_object($estadoinfraestructura)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del estadoinfraestructura no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if ($codigoestadoinfraestructura != null) {
                                    $estadoinfraestructura->setCodigoestadoinfraestructura($codigoestadoinfraestructura);
                                }
                                if ($descripcionestadoinfraestructura != null) {
                                    $estadoinfraestructura->setDescripcionestadoinfraestructura($descripcionestadoinfraestructura);
                                }
                                if ($nombreestadoinfraestructura != null) {
                                    $nombreestadoinfraestructura_old = $estadoinfraestructura->getNombreestadoinfraestructura();
                                    $estadoinfraestructura_id = $estadoinfraestructura->getPkidestadoinfraestructura();

                                    $estadoinfraestructura->setNombreestadoinfraestructura("p");
                                    $em->persist($estadoinfraestructura);
                                    $em->flush();

                                    $isset_estadoinfraestructura = $em->getRepository('ModeloBundle:Testadoinfraestructura')->findOneBy(array(
                                        "nombreestadoinfraestructura" => $nombreestadoinfraestructura,
                                    ));

                                    if (!is_object($isset_estadoinfraestructura)) {
                                        $estadoinfraestructura->setNombreestadoinfraestructura($nombreestadoinfraestructura);
                                    } else {
                                        $estadoinfraestructura_old_id = $em->getRepository('ModeloBundle:Testadoinfraestructura')->findOneBy(array(
                                            "pkidestadoinfraestructura" => $estadoinfraestructura_id,
                                        ));

                                        $estadoinfraestructura_old_id->setNombreestadoinfraestructura($nombreestadoinfraestructura_old);
                                        $em->persist($estadoinfraestructura_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Estado Infraestructura no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $estadoinfraestructura->setEstadoinfraestructuraactivo($estadoinfraestructuraactivo);
                                //$estadoinfraestructura->setCreacionestadoinfraestructura($creacionestadoinfraestructura);
                                $estadoinfraestructura->setModificacionestadoinfraestructura($modificacionestadoinfraestructura);

                                $em->persist($estadoinfraestructura);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Estado Infraestructura actualizado !!',
                                    'estadoinfraestructura' => $estadoinfraestructura,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Estado Infraestructura",
                                    "valoresrelevantes" => "idestadoinfraestructura" . ":" . $estadoinfraestructura->getPkidestadoinfraestructura(),
                                    "idelemento" => $estadoinfraestructura->getPkidestadoinfraestructura(),
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
                'modulo' => "Estado Infraestructura",
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
    {"pkidestadoinfraestructura":"valor"}
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

                    if (in_array("PERM_ESTADO_INFRAESTRUCTURAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidestadoinfraestructura = (isset($params->pkidestadoinfraestructura)) ? $params->pkidestadoinfraestructura : null;

                            if ($pkidestadoinfraestructura != null) {

                                $estadoinfraestructura = $this->getDoctrine()->getRepository("ModeloBundle:Testadoinfraestructura")->findOneBy(array(
                                    "pkidestadoinfraestructura" => $pkidestadoinfraestructura,
                                ));
                                
                                $isset_estadoinfraestructura_puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                "fkidestadoinfraestructura" => $pkidestadoinfraestructura,
                                ));

                                if (!is_object($isset_estadoinfraestructura_puesto)) {
                                 

                                if (is_object($estadoinfraestructura)) {

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "eliminar",
                                        "tabla" => "Estado Infraestructura",
                                        "valoresrelevantes" => "idestadoinfraestructura" . ":" . $estadoinfraestructura->getPkidestadoinfraestructura(),
                                        "idelemento" => $estadoinfraestructura->getPkidestadoinfraestructura(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                    $em->remove($estadoinfraestructura);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'El Estado Infraestructura se ha eliminado correctamente !!',
                                    );

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El Estado Infraestructura a eliminar no existe !!',
                                    );
                                }
                                
                            } else {
                            $data = array(
                            'status' => 'error',
                            'msg' => 'No se puede eliminar el Estado Infraestructura, pertenece a un puesto !!',
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
                'modulo' => "Estado Infraestructura",
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
