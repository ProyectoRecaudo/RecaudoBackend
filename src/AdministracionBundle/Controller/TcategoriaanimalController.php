<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tcategoriaanimal;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TcategoriaanimalController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todas las categorias de tipo animal a la base de datos
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
                        
                        $query = "SELECT 
                                    pkidcategoriaanimal,
                                    nombrecategoriaanimal
                                FROM tcategoriaanimal where categoriaanimalactivo=true
                                ORDER by nombrecategoriaanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $categoriasanimal = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'categoriaanimal' => $categoriasanimal,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT 
                                    pkidcategoriaanimal,
                                    nombrecategoriaanimal,
                                    categoriaanimalactivo,
                                    descripcioncategoriaanimal,
                                    codigocategoriaanimal
                                FROM tcategoriaanimal
                                ORDER by nombrecategoriaanimal ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $categoriasanimal = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($categoriasanimal as $categoriaanimal) {
                            $categoriaanimalList = array(
                                "pkidcategoriaanimal"        => $categoriaanimal['pkidcategoriaanimal'],
                                "nombrecategoriaanimal"      => $categoriaanimal['nombrecategoriaanimal'],
                                "descripcioncategoriaanimal" => $categoriaanimal['descripcioncategoriaanimal'],
                                "categoriaanimalactivo"      => $categoriaanimal['categoriaanimalactivo'],
                                "codigocategoriaanimal"      => $categoriaanimal['codigocategoriaanimal']
                            );
                            array_push($array_all, $categoriaanimalList);
                        }

                        $cabeceras=array();

                        $pkidcategoriaanimal = array("nombrecampo"=>"pkidcategoriaanimal","nombreetiqueta"=>"Id Categoria Animal","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");                       
                        $nombrecategoriaanimal = array("nombrecampo"=>"nombrecategoriaanimal","nombreetiqueta"=>"Nombre Categoria Animal","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                       
                        $descripcioncategoriaanimal = array("nombrecampo"=>"descripcioncategoriaanimal","nombreetiqueta"=>"Descripcion Categoria Animal","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $categoriaanimalactivo = array("nombrecampo"=>"categoriaanimalactivo","nombreetiqueta"=>"Categoria Animal Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");                       
                        $codigocategoriaanimal = array("nombrecampo"=>"codigocategoriaanimal","nombreetiqueta"=>"Codigo Categoria Animal","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");                          
                        
                        array_push($cabeceras,$pkidcategoriaanimal);
                        array_push($cabeceras,$nombrecategoriaanimal);
                        array_push($cabeceras,$descripcioncategoriaanimal);
                        array_push($cabeceras,$categoriaanimalactivo);
                        array_push($cabeceras,$codigocategoriaanimal);
                        
                        $title=array("Nuevo","Categoria Animal");

                        $data = array(
                            'status'          => 'Success',
                            'cabeceras'       => $cabeceras,
                            'categoriaanimal' => $array_all,
                            'title'           => $title
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
                'modulo' => "Categoria Animal",
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

    /*Este funcion realiza la inserccion de una Categoria Animal nueva
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {
    "nombrecategoriaanimal":"valor",
    "descripcioncategoriaanimal":"valor",
    "categoriaanimalactivo":"valor"}
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacioncategoriaanimal = new \Datetime("now");
                            $modificacioncategoriaanimal = new \Datetime("now");

                            
                            $nombrecategoriaanimal = (isset($params->nombrecategoriaanimal)) ? $params->nombrecategoriaanimal : null;
                            $categoriaanimalactivo = (isset($params->categoriaanimalactivo)) ? $params->categoriaanimalactivo : true;
                            $descripcioncategoriaanimal = (isset($params->descripcioncategoriaanimal)) ? $params->descripcioncategoriaanimal : null;
                            $codigocategoriaanimal = (isset($params->codigocategoriaanimal)) ? $params->codigocategoriaanimal : null;

                            if ($nombrecategoriaanimal != null) {

                                $categoriaanimal = new Tcategoriaanimal();
                                if($descripcioncategoriaanimal!=null){
                                    $categoriaanimal->setDescripcioncategoriaanimal($descripcioncategoriaanimal);
                                }
                                //aqui quede
                                $categoriaanimal->setNombrecategoriaanimal($nombrecategoriaanimal);
                                $categoriaanimal->setcategoriaanimalactivo($categoriaanimalactivo);
                                $categoriaanimal->setCreacioncategoriaanimal($creacioncategoriaanimal);
                                $categoriaanimal->setModificacioncategoriaanimal($modificacioncategoriaanimal);
                                $categoriaanimal->setCodigocategoriaanimal($codigocategoriaanimal);

                                $isset_categoriaanimal = $em->getRepository('ModeloBundle:Tcategoriaanimal')->findOneBy(array(
                                    "nombrecategoriaanimal" => $nombrecategoriaanimal,
                                ));

                                if (!is_object($isset_categoriaanimal)) {

                                    $em->persist($categoriaanimal);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Categoria Animal creado !!',
                                        'categoriaanimal' => $categoriaanimal,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "categoriaanimal",
                                        "valoresrelevantes" => "idcategoriaanimal" . ":" . $categoriaanimal->getPkidcategoriaanimal(),
                                        "idelemento" => $categoriaanimal->getPkidcategoriaanimal(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Categoria Animal no creado, Duplicado !!',
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
                'modulo' => "Categoria Animal",
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

    /*Esta funcion realiza la actualizacion de una categoria animal,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidcategoriaanimal":"valor",
    "nombrecategoriaanimal":"valor",
    "descripcioncategoriaanimal":"valor",
    "categoriaanimalactivo":"valor"}
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacioncategoriaanimal = new \Datetime("now");
                            $modificacioncategoriaanimal = new \Datetime("now");
                            $pkidcategoriaanimal = (isset($params->pkidcategoriaanimal)) ? $params->pkidcategoriaanimal : null;
                            
                            $nombrecategoriaanimal = (isset($params->nombrecategoriaanimal)) ? $params->nombrecategoriaanimal : null;
                            $categoriaanimalactivo = (isset($params->categoriaanimalactivo)) ? $params->categoriaanimalactivo : true;
                            $descripcioncategoriaanimal = (isset($params->descripcioncategoriaanimal)) ? $params->descripcioncategoriaanimal : null;
                            $codigocategoriaanimal = (isset($params->codigocategoriaanimal)) ? $params->codigocategoriaanimal : null;

                            if ($nombrecategoriaanimal != null && $pkidcategoriaanimal != null) {

                                $categoriaanimal = $this->getDoctrine()->getRepository("ModeloBundle:Tcategoriaanimal")->findOneBy(array(
                                    "pkidcategoriaanimal" => $pkidcategoriaanimal,
                                ));

                                if(!is_object($categoriaanimal)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del Categoria Animal no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if($descripcioncategoriaanimal!=null){
                                    $categoriaanimal->setDescripcioncategoriaanimal($descripcioncategoriaanimal);
                                }

                                if($codigocategoriaanimal!=null){
                                    $categoriaanimal->setCodigocategoriaanimal($codigocategoriaanimal);
                                }

                                if ($nombrecategoriaanimal != null) {
                                    $nombrecategoriaanimal_old = $categoriaanimal->getNombrecategoriaanimal();
                                    $categoriaanimal_id = $categoriaanimal->getPkidcategoriaanimal();

                                    $categoriaanimal->setNombrecategoriaanimal("p");
                                    $em->persist($categoriaanimal);
                                    $em->flush();

                                    $isset_categoriaanimal = $em->getRepository('ModeloBundle:Tcategoriaanimal')->findOneBy(array(
                                        "nombrecategoriaanimal" => $nombrecategoriaanimal,
                                    ));

                                    if (!is_object($isset_categoriaanimal)) {
                                        $categoriaanimal->setNombrecategoriaanimal($nombrecategoriaanimal);
                                    } else {
                                        $categoriaanimal_old_id = $em->getRepository('ModeloBundle:Tcategoriaanimal')->findOneBy(array(
                                            "pkidcategoriaanimal" => $categoriaanimal_id,
                                        ));

                                        $categoriaanimal_old_id->setNombrecategoriaanimal($nombrecategoriaanimal_old);
                                        $em->persist($categoriaanimal_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Categoria Animal no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $categoriaanimal->setcategoriaanimalactivo($categoriaanimalactivo);
                                //$categoriaanimal->setCreacioncategoriaanimal($creacioncategoriaanimal);
                                $categoriaanimal->setModificacioncategoriaanimal($modificacioncategoriaanimal);

                                $em->persist($categoriaanimal);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Categoria Animal actualizado !!',
                                    'categoriaanimal' => $categoriaanimal,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Categoria Animal",
                                    "valoresrelevantes" => "idcategoriaanimal" . ":" . $categoriaanimal->getPkidcategoriaanimal(),
                                    "idelemento" => $categoriaanimal->getPkidcategoriaanimal(),
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
                'modulo' => "Categoria Animal",
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

    /*Este funcion realiza la eliminacion de una categoria animal
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidcategoriaanimal":"valor"}
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidcategoriaanimal = (isset($params->pkidcategoriaanimal)) ? $params->pkidcategoriaanimal : null;

                            if ($pkidcategoriaanimal != null) {

                                $categoriaanimal = $this->getDoctrine()->getRepository("ModeloBundle:Tcategoriaanimal")->findOneBy(array(
                                    "pkidcategoriaanimal" => $pkidcategoriaanimal,
                                ));


                                            if (is_object($categoriaanimal)) {

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "eliminar",
                                                    "tabla" => "Categoria Animal",
                                                    "valoresrelevantes" => "idcategoriaanimal" . ":" . $categoriaanimal->getPkidcategoriaanimal(),
                                                    "idelemento" => $categoriaanimal->getPkidcategoriaanimal(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                                $em->remove($categoriaanimal);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'El Categoria Animal se ha eliminado correctamente !!',
                                                );

                                            } else {
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg' => 'El Categoria Animal a eliminar no existe !!',
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
                'modulo' => "Categoria Animal",
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
