<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tusuario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TusuarioController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los roles a la base de datos
    se debe enviar como parametro el token del usuario logueado con el nombre de authorization
     */
    /**
     * @Route("/roles")
     */
    public function rolesAction(Request $request)
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

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidrol,nombrerol FROM trol  order by nombrerol ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $roles = $stmt->fetchAll();

                        $data = array(
                            'status' => 'Exito',
                            'roles' => $roles,
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
                'modulo' => "Usuarios",
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

    /*
    Esta funcion realiza una consulta de todos los usuarios a la base de datos
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

                        $query = "SELECT pkidusuario,nombreusuario,contrasenia,apellido,identificacion,rutaimagen,codigousuario,nombrerol,fkidrol,usuarioactivo,numerorecibo FROM tusuario join
                        trol on tusuario.fkidrol=trol.pkidrol where usuarioactivo=true";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $users = $stmt->fetchAll();
                        
			            $data = array(
                            'status'    => 'Success',
                            'users'     => $users,
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

                        $query = "SELECT pkidusuario,nombreusuario,contrasenia,apellido,identificacion,rutaimagen,codigousuario,nombrerol,fkidrol,usuarioactivo,numerorecibo FROM tusuario join
                        trol on tusuario.fkidrol=trol.pkidrol";

                        $filtro = $request->get('filtro', null);  
                        $params = json_decode($filtro);

                        if($filtro != null){
                        $nombrefiltro= (isset($params->nombrefiltro)) ? $params->nombrefiltro : null;
                            
                        if($nombrefiltro != null){
                            $query .=" where nombrerol like '%$nombrefiltro%'";
                        }   
                        }

                        $query .=" ORDER BY nombreusuario ASC;";      
                        
                       

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $users = $stmt->fetchAll();

                        $array_all = array();
                        foreach ($users as $user) {
                            $users_roles = array("pkidusuario" => $user['pkidusuario'],
                                "nombreusuario" => $user['nombreusuario'],
                                "contrasenia" => $user['contrasenia'],
                                "apellido" => $user['apellido'],
                                "identificacion" => $user['identificacion'],
                                "codigousuario" => $user['codigousuario'],
                                "rutaimagen" => $user['rutaimagen'],
                                "roles" => array("pkidrol" => $user['fkidrol'], "nombrerol" => $user['nombrerol']),
                                "usuarioactivo" => $user['usuarioactivo'],
                                "numerorecibo" => $user['numerorecibo'],
                            );
                            array_push($array_all, $users_roles);
                        }

                        //pkidusuario,nombreusuario,apellido,identificacion,rutaimagen,codigousuario,nombrerol,fkidrol,usuarioactivo
                        $cabeceras=array();

                        $pkidusuario = array("nombrecampo"=>"pkidusuario","nombreetiqueta"=>"Id usuario","create-required"=>false,"update-required"=>false,"update"=>false,"create"=>false,"show"=>false,"fk"=>false,"fktable"=>"","pk"=>true,"type"=>"number");
                        $identificacion = array("nombrecampo"=>"identificacion","nombreetiqueta"=>"Identificacion","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"number");
                        $nombreusuario = array("nombrecampo"=>"nombreusuario","nombreetiqueta"=>"Nombre Usuario","create-required"=>true,"update-required"=>true,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $apellido = array("nombrecampo"=>"apellido","nombreetiqueta"=>"Apellido","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $codigousuario = array("nombrecampo"=>"codigousuario","nombreetiqueta"=>"Codigo Usuario","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"string");
                        $usuarioactivo = array("nombrecampo"=>"usuarioactivo","nombreetiqueta"=>"Usuario Activo","create-required"=>false,"update-required"=>false,"update"=>true,"create"=>true,"show"=>true,"fk"=>false,"fktable"=>"","pk"=>false,"type"=>"boolean");              
                        $fkidrol = array("nombrecampo"=>"fkidrol","nombreetiqueta"=>"fkidrol","create-required"=>true,"update-required"=>true,"update"=>false,"create"=>false,"fk"=>true,"show"=>false,"fktable"=>"trol","pk"=>false,"type"=>"number");

                        array_push($cabeceras,$pkidusuario);
                        array_push($cabeceras,$identificacion);
                        array_push($cabeceras,$nombreusuario);
                        array_push($cabeceras,$apellido);
                        array_push($cabeceras,$codigousuario);
                        array_push($cabeceras,$usuarioactivo);
                        array_push($cabeceras,$fkidrol);

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'users' => $array_all,
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
                'modulo' => "Usuarios",
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

    /*Este funcion realiza la inserccion de un usuario nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigousuario":"valor",
    "identificacion":"valor",
    "nombreusuario":"valor",
    "contrasenia":"valor",
    "apellido":"valor",
    "usuarioactivo":"valor",
    "fkidrol":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario, y opcionalmente se debe enviar una imagen del usuario, con el nombre de
    fichero_usuario, y cargar debidamente la imagen.
     */
    /**
     * @Route("/new")
     */
    public function newAction(Request $request)
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

                            $creacionUsuario = new \Datetime("now");
                            $modificacionUsuario = new \Datetime("now");

                            $codigoUsuario = (isset($params->codigousuario)) ? $params->codigousuario : null;
                            $identificacion = (isset($params->identificacion)) ? $params->identificacion : null;
                            if (strlen($identificacion) > 10) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'En identificacion solo se permiten 10 caracteres',
                                );

                                return $helpers->json($data);
                            }
                            $nombreUsuario = (isset($params->nombreusuario)) ? $params->nombreusuario : null;
                            $contrasenia = (isset($params->contrasenia)) ? $params->contrasenia : null;
                            $apellido = (isset($params->apellido)) ? $params->apellido : null;
                            $usuarioActivo = (isset($params->usuarioactivo)) ? $params->usuarioactivo : true;
                            $fkidrol = (isset($params->fkidrol)) ? $params->fkidrol : null;
                            //$numerorecibo = (isset($params->numerorecibo)) ? $params->numerorecibo : null;
                            $idrol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->findOneBy(array(
                                "pkidrol" => $fkidrol,
                            ));

                            if (!is_object($idrol)) {
                                $data = array(
                                    'status' => 'Error',
                                    'msg' => 'El id del rol no existe!!',
                                );
                                return $helpers->json($data);
                            }

                            if ( $identificacion != null && $nombreUsuario != null  && $fkidrol != null && $contrasenia != null) {

                                $user = new Tusuario();

                                $user->setNumerorecibo(1000);

                                if($codigoUsuario!=null){
                                $user->setCodigousuario($codigoUsuario);
                                }
                                $user->setIdentificacion($identificacion);
                                $user->setNombreusuario($nombreUsuario);
                                if ($contrasenia != null) {
                                    //Cifrar la contraseña o password
                                    $pwd = hash('sha256', $contrasenia);
                                    $user->setContrasenia($pwd);
                                }
                                if($apellido!=null){
                                $user->setApellido($apellido);
                                }
                                $user->setUsuarioactivo($usuarioActivo);

                                $idrol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->find($fkidrol);
                                $user->setFkidrol($idrol);
                                $user->setCreacionusuario($creacionUsuario);
                                $user->setModificacionusuario($modificacionUsuario);

                                $isset_user = $em->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
                                    "identificacion" => $identificacion,
                                ));

                                if (!is_object($isset_user)) {

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {

                                            if ($_FILES['fichero_usuario']['type'] == "image/png" || $_FILES['fichero_usuario']['type'] == "image/jpg" || $_FILES['fichero_usuario']['type'] == "image/jpeg" || $_FILES['fichero_usuario']['type'] == "image/tiff") {

                                                $em->persist($user);
                                                $em->flush();

                                                $dir_subida = '../web/uploads/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($user->getPkidusuario() . "_usuario_" .$creacionUsuario->format('Y-m-d_H-i-s') . "." . $extension);

                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $user_images = $em->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
                                                        "pkidusuario" => $user->getPkidusuario(),
                                                    ));

                                                    $user_images->setRutaimagen($fichero_subido);
                                                    $em->persist($user_images);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Exito',
                                                        'msg' => 'Usuario creado !!',
                                                        'rutaimagen' => $fichero_subido,
                                                        'user' => $user_images,
                                                    );

                                                    $datos = array(
                                                        "idusuario" => $identity->sub,
                                                        "nombreusuario" => $identity->name,
                                                        "identificacionusuario" => $identity->identificacion,
                                                        "accion" => "insertar",
                                                        "tabla" => "Usuarios",
                                                        "valoresrelevantes" => "idusuario" . ":" . $user->getPkidusuario(),
                                                        "idelemento" => $user->getPkidusuario(),
                                                        "origen" => "web",
                                                    );

                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));

                                                } else {
                                                    $em->remove($user);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg' => 'No se ha podido ingresar la imagen del usuario, intente nuevamente !!',
                                                    );
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg' => 'Solo se aceptan archivos en formato PNG/JPG/JPEG/TIFF !!',
                                                );
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg' => 'El tamaño de la imagen debe ser MAX 5MB !!',
                                            );
                                        }
                                    } else {
                                        $em->persist($user);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Usuario creado !!',
                                            'rutaimagen' => 'No se cargo ninguna imagen !!',
                                            'user' => $user,
                                        );

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "Usuarios",
                                            "valoresrelevantes" => "idusuario" . ":" . $user->getPkidusuario(),
                                            "idelemento" => $user->getPkidusuario(),
                                            "origen" => "web",
                                        );

                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));
                                    }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Usuario no creado, Duplicado !!',
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
                'modulo' => "Usuarios",
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => "Web",
            );

            try {
                $auditoria = $this->get(Auditoria::class);
                $auditoria->exepcion(json_encode($exepcion));

                $em->remove($user);
                $em->flush();
            } catch (\Exception $a) {

            }

            throw $e;

        }
    }

    /*Este funcion realiza la inserccion de un usuario nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidusuario":"valor",
    "codigousuario":"valor",
    "identificacion":"valor",
    "nombreusuario":"valor",
    "contrasenia":"valor",
    "apellido":"valor",
    "usuarioactivo":"valor",
    "fkidrol":"valor"}
    un parametro con el nombre authorization, y como valor del parametro el token correspondiente
    al login del usuario, y opcionalmente se debe enviar una imagen del usuario, con el nombre de
    fichero_usuario, y cargar debidamente la imagen.
     */
    /**
     * @Route("/edit")
     */
    public function editAction(Request $request)
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

                            $creacionUsuario = new \Datetime("now");
                            $modificacionUsuario = new \Datetime("now");

                            $pkidusuario = (isset($params->pkidusuario)) ? $params->pkidusuario : null;
                            $codigoUsuario = (isset($params->codigousuario)) ? $params->codigousuario : null;
                            $identificacion = (isset($params->identificacion)) ? $params->identificacion : null;
                            if (strlen($identificacion) > 10) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'En identificacion solo se permiten 10 caracteres',
                                );

                                return $helpers->json($data);
                            }
                            $nombreUsuario = (isset($params->nombreusuario)) ? $params->nombreusuario : null;
                            $contrasenia = (isset($params->contrasenia)) ? $params->contrasenia : null;
                            $apellido = (isset($params->apellido)) ? $params->apellido : null;
                            $usuarioActivo = (isset($params->usuarioactivo)) ? $params->usuarioactivo : true;
                            $fkidrol = (isset($params->fkidrol)) ? $params->fkidrol : null;
                            //$numerorecibo = (isset($params->numerorecibo)) ? $params->numerorecibo : null;
                            $idrol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->findOneBy(array(
                                "pkidrol" => $fkidrol,
                            ));

                            if (!is_object($idrol)) {
                                $data = array(
                                    'status' => 'Error',
                                    'msg' => 'El id del rol no existe!!',
                                );
                                return $helpers->json($data);
                            }

                            if ($pkidusuario != null  && $identificacion != null && $nombreUsuario != null && $fkidrol != null) {

                                $user = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $pkidusuario,
                                ));

                                if (!is_object($user)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El usuario a actualizar no existe !!'
                                    );
                                }


                                    if($codigoUsuario!=null){
                                        $user->setCodigousuario($codigoUsuario);
                                        }
                                    if ($identificacion != null) {
                                        $iden_old = $user->getIdentificacion();
                                        $user_id = $user->getPkidusuario();

                                        $user->setIdentificacion(0);
                                        $em->persist($user);
                                        $em->flush();

                                        $isset_user = $em->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
                                            "identificacion" => $identificacion,
                                        ));

                                        if (!is_object($isset_user)) {
                                            $user->setIdentificacion($identificacion);
                                        } else {
                                            $user_old_id = $em->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
                                                "pkidusuario" => $user_id,
                                            ));

                                            $user_old_id->setIdentificacion($iden_old);
                                            $em->persist($user_old_id);
                                            $em->flush();

                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'Usuario no actualizado,  la identificacion enviada ya existe !!',
                                            );
                                            return $helpers->json($data);
                                        }

                                    }
                                    $user->setNombreusuario($nombreUsuario);
                                    if ($contrasenia != null) {
                                        //Cifrar la contraseña o password
                                        $pwd = hash('sha256', $contrasenia);
                                        $user->setContrasenia($pwd);
                                    }
                                    if($apellido!=null){
                                    $user->setApellido($apellido);
                                    }
                                    $user->setUsuarioactivo($usuarioActivo);

                                    $idrol = $this->getDoctrine()->getRepository("ModeloBundle:Trol")->find($fkidrol);
                                    $user->setFkidrol($idrol);
                                    //$user->setCreacionusuario($creacionUsuario);
                                    $user->setModificacionusuario($modificacionUsuario);

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {

                                            if ($_FILES['fichero_usuario']['type'] == "image/png" || $_FILES['fichero_usuario']['type'] == "image/jpg" || $_FILES['fichero_usuario']['type'] == "image/jpeg" || $_FILES['fichero_usuario']['type'] == "image/tiff") {

                                                $em->persist($user);
                                                $em->flush();

                                                $dir_subida = '../web/uploads/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($user->getPkidusuario() . "_usuario_" .$creacionUsuario->format('Y-m-d_H-i-s'). "." . $extension);

                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {

                                                    $user_images = $em->getRepository('ModeloBundle:Tusuario')->findOneBy(array(
                                                        "pkidusuario" => $user->getPkidusuario(),
                                                    ));

                                                    $imagen_old = $user->getRutaimagen();
                                                    if ($imagen_old != null) {
                                                        unlink($imagen_old);
                                                    }
                                                    $user_images->setRutaimagen($fichero_subido);
                                                    $em->persist($user_images);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Exito',
                                                        'msg' => 'Usuario actualizado !!',
                                                        'rutaimagen' => $fichero_subido,
                                                        'user' => $user_images,
                                                    );

                                                    $datos = array(
                                                        "idusuario" => $identity->sub,
                                                        "nombreusuario" => $identity->name,
                                                        "identificacionusuario" => $identity->identificacion,
                                                        "accion" => "editar",
                                                        "tabla" => "Usuarios",
                                                        "valoresrelevantes" => "idusuario" . ":" . $user->getPkidusuario(),
                                                        "idelemento" => $user->getPkidusuario(),
                                                        "origen" => "web",
                                                    );

                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));

                                                } else {
                                                    $data = array(
                                                        'status' => 'Exito/Error',
                                                        'msg' => 'Se actualizaron los datos correctamente, no se pudo actualizar la imagen del usuario, intente nuevamente !!',
                                                    );
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg' => 'Solo se aceptan archivos en formato PNG/JPG/JPEG/TIFF !!',
                                                );
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg' => 'El tamaño de la imagen debe ser MAX 5MB !!',
                                            );
                                        }
                                    } else {
                                        $em->persist($user);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Usuario actualizado !!',
                                            'rutaimagen' => 'No se cargo ninguna imagen !!',
                                            'user' => $user,
                                        );

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "editar",
                                            "tabla" => "Usuarios",
                                            "valoresrelevantes" => "idusuario" . ":" . $user->getPkidusuario(),
                                            "idelemento" => $user->getPkidusuario(),
                                            "origen" => "web",
                                        );

                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));
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
                'modulo' => "Usuarios",
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

    /*Este funcion realiza la eliminacion de un usuario,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidusuario":"valor"}
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

                            $pkidusuario = (isset($params->pkidusuario)) ? $params->pkidusuario : null;

                            if ($pkidusuario != null) {

                                $user = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $pkidusuario,
                                ));
                                $isset_user_zona = $this->getDoctrine()->getRepository("ModeloBundle:Tzona")->findOneBy(array(
                                    "fkidusuario" => $pkidusuario,
                                ));

                                if (!is_object($isset_user_zona)) {
                                    $isset_user_auditoria = $this->getDoctrine()->getRepository("ModeloBundle:Tauditoria")->findOneBy(array(
                                        "fkidusuario" => $pkidusuario,
                                    ));

                                    if (!is_object($isset_user_auditoria)) {

                                        if (is_object($user)) {

                                            $datos = array(
                                                "idusuario" => $identity->sub,
                                                "nombreusuario" => $identity->name,
                                                "identificacionusuario" => $identity->identificacion,
                                                "accion" => "eliminar",
                                                "tabla" => "Usuarios",
                                                "valoresrelevantes" => "idusuario" . ":" . $user->getPkidusuario(),
                                                "idelemento" => $user->getPkidusuario(),
                                                "origen" => "web",
                                            );

                                            $auditoria = $this->get(Auditoria::class);
                                            $auditoria->auditoria(json_encode($datos));

                                            $imagen_remove = $user->getRutaimagen();
                                            if ($imagen_remove != null) {
                                                unlink($user->getRutaimagen());
                                            }

                                            $em->remove($user);
                                            $em->flush();

                                            $data = array(
                                                'status' => 'Exito',
                                                'msg' => 'El usuario se ha eliminado correctamente !!',
                                            );

                                        } else {
                                            $data = array(
                                                'status' => 'error',
                                                'msg' => 'El usuario a eliminar no existe !!',
                                            );
                                        }

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'No se puede eliminar el usuario, se encontraron registros en auditoria !!',
                                        );
                                    }
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el usuario, se encuentra asignado a una zona !!',
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
                'modulo' => "Usuarios",
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
