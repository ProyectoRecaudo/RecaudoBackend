<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tpuerta;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TpuertaController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /*
    Esta funcion realiza una consulta de todos los tipos de puerta a la base de datos
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

                        $query = "SELECT pkidpuerta, codigopuerta, nombrepuerta, puertaactivo, creacionpuerta,
                        modificacionpuerta,fkidplaza,nombreplaza FROM tpuerta join tplaza on tpuerta.fkidplaza=tplaza.pkidplaza where puertaactivo=true order by nombrepuerta ASC;
                ";

                                                

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $puerta = $stmt->fetchAll();
                        
			            $data = array(
                            'status'    => 'Success',
                            'puerta'     => $puerta,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_PUERTAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidpuerta, codigopuerta, nombrepuerta, puertaactivo, creacionpuerta,
                        modificacionpuerta,fkidplaza,nombreplaza FROM tpuerta join tplaza on tpuerta.fkidplaza=tplaza.pkidplaza order by nombrepuerta ASC;
                ";

                                                

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $puerta = $stmt->fetchAll();

                        $cabeceras = array("Nombre puerta", "Descripción puerta", "puerta Activo/Inactivo", "Creación", "Modificación");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'puerta' => $puerta,
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
                'modulo' => "puerta",
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

    /*Este funcion realiza la inserccion de un puerta nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigopuerta":"valor",
    "nombrepuerta":"valor",
    "puertaactivo":"valor",
    "fkidplaza":"valor"}
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

                    if (in_array("PERM_PUERTAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionpuerta = new \Datetime("now");
                            $modificacionpuerta = new \Datetime("now");

                            $codigopuerta = (isset($params->codigopuerta)) ? $params->codigopuerta : null;
                            $nombrepuerta = (isset($params->nombrepuerta)) ? $params->nombrepuerta : null;
                            $puertaactivo = (isset($params->puertaactivo)) ? $params->puertaactivo : true;
                            $fkidplaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;

                            if ($nombrepuerta != null  && $fkidplaza != null) {

                                $puerta = new Tpuerta();
                                if ($codigopuerta != null) {
                                    $puerta->setCodigopuerta($codigopuerta);
                                }
                                //aqui quede 
                                $puerta->setNombrepuerta($nombrepuerta);
                                $puerta->setPuertaactivo($puertaactivo);
                                $puerta->setCreacionpuerta($creacionpuerta);
                                $puerta->setModificacionpuerta($modificacionpuerta);
                                $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array('pkidplaza'=>$fkidplaza));
                                if(!is_object($isset_plaza)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la plaza no existe !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puerta->setFkidplaza($isset_plaza);

                                $isset_puerta = $em->getRepository('ModeloBundle:Tpuerta')->findOneBy(array(
                                    "nombrepuerta" => $nombrepuerta,
                                ));

                                if (!is_object($isset_puerta)) {

                                    $em->persist($puerta);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'puerta creada !!',
                                        'puerta' => $puerta,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "puerta",
                                        "valoresrelevantes" => "idpuerta" . ":" . $puerta->getPkidpuerta(),
                                        "idelemento" => $puerta->getPkidpuerta(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'puerta no creado, Duplicado !!',
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
                'modulo' => "puerta",
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

    /*Esta funcion realiza la actualizacion de un tipo de puerta,
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidpuerta":"valor",
    "codigopuerta":"valor",
    "nombrepuerta":"valor",
    "puertaactivo":"valor",
    "fkidplaza":"valor"}
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

                    if (in_array("PERM_PUERTAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacionpuerta = new \Datetime("now");
                            $modificacionpuerta = new \Datetime("now");
                            $pkidpuerta = (isset($params->pkidpuerta)) ? $params->pkidpuerta : null;
                            $codigopuerta = (isset($params->codigopuerta)) ? $params->codigopuerta : null;
                            $nombrepuerta = (isset($params->nombrepuerta)) ? $params->nombrepuerta : null;
                            $puertaactivo = (isset($params->puertaactivo)) ? $params->puertaactivo : true;
                            $fkidplaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;

                            if ($nombrepuerta != null && $pkidpuerta != null &&  $fkidplaza != null) {

                                $puerta = $this->getDoctrine()->getRepository("ModeloBundle:Tpuerta")->findOneBy(array(
                                    "pkidpuerta" => $pkidpuerta,
                                ));

                                if(!is_object($puerta)){
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la puerta de acceso no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if ($codigopuerta != null) {
                                    $puerta->setCodigopuerta($codigopuerta);
                                }
                                if ($nombrepuerta != null) {
                                    $nombrepuerta_old = $puerta->getNombrepuerta();
                                    $puerta_id = $puerta->getPkidpuerta();

                                    $puerta->setNombrepuerta("p");
                                    $em->persist($puerta);
                                    $em->flush();

                                    $isset_puerta = $em->getRepository('ModeloBundle:Tpuerta')->findOneBy(array(
                                        "nombrepuerta" => $nombrepuerta,
                                    ));

                                    if (!is_object($isset_puerta)) {
                                        $puerta->setNombrepuerta($nombrepuerta);
                                    } else {
                                        $puerta_old_id = $em->getRepository('ModeloBundle:Tpuerta')->findOneBy(array(
                                            "pkidpuerta" => $puerta_id,
                                        ));

                                        $puerta_old_id->setNombrepuerta($nombrepuerta_old);
                                        $em->persist($puerta_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'puerta no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $puerta->setPuertaactivo($puertaactivo);
                                $puerta->setModificacionpuerta($modificacionpuerta);
                                $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->find($fkidplaza);
                                $puerta->setFkidplaza($isset_plaza);

                                $em->persist($puerta);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Puerta actualizada !!',
                                    'puerta' => $puerta,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "puerta",
                                    "valoresrelevantes" => "idpuerta" . ":" . $puerta->getPkidpuerta(),
                                    "idelemento" => $puerta->getPkidpuerta(),
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
                'modulo' => "Puerta",
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

    /*Este funcion realiza la eliminacion de un tipo de puerta
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"pkidpuerta":"valor"}
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

                    if (in_array("PERM_PUERTAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidpuerta = (isset($params->pkidpuerta)) ? $params->pkidpuerta : null;

                            if ($pkidpuerta != null) {

                                $puerta = $this->getDoctrine()->getRepository("ModeloBundle:Tpuerta")->findOneBy(array(
                                    "pkidpuerta" => $pkidpuerta,
                                ));

                                if (is_object($puerta)) {

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "eliminar",
                                        "tabla" => "puerta",
                                        "valoresrelevantes" => "idpuerta" . ":" . $puerta->getPkidpuerta(),
                                        "idelemento" => $puerta->getPkidpuerta(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                    $em->remove($puerta);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'la puerta se ha eliminado correctamente !!',
                                    );

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'la puerta a eliminar no existe !!',
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
                'modulo' => "puerta",
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
