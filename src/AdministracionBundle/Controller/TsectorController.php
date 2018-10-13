<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tsector;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TsectorController extends Controller
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

                    $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                    $activo = $request->get('activo', null);   
                    if($activo != null && $activo == "true"){
                    if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                        
                        
                        $query = "SELECT pkidsector, nombresector,fkidplaza FROM tsector
                        join tzona on tsector.fkidzona=tzona.pkidzona join
                        ttiposector on tsector.fkidtiposector=ttiposector.pkidtiposector join tplaza on tplaza.pkidplaza=tzona.fkidplaza where sectoractivo=true order by nombresector ASC;
                ";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $sector = $stmt->fetchAll();

			            $data = array(
                            'status'    => 'Success',
                            'sector'     => $sector,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }


                        $zona = $request->get('zona', null);
                        $params = json_decode($zona);

                        if ($zona != null) {
                            if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            $pkidzona = (isset($params->pkidzona)) ? $params->pkidzona : null;

                            if ($pkidzona != null) {
                                    $query = "SELECT pkidsector, codigosector, nombresector, sectoractivo, creacionsector, 
                                    modificacionsector, fkidzona, fkidtiposector FROM tsector where fkidzona = $pkidzona 
                                            ";
                            }

                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $sector = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Exito',
                                'sector' => $sector,
                            );
                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                        }

                        $pkidplaza = $request->get('pkidplaza', null);

                        if ($pkidplaza != null) {
                            if(in_array("PERM_GENERICOS", $permisosDeserializados)){        

                                    $query = "SELECT nombresector, pkidsector FROM tplaza join tzona on tplaza.pkidplaza=tzona.fkidplaza join tsector on tzona.pkidzona=tsector.fkidzona where pkidplaza = $pkidplaza 
                                            ";

                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $sector = $stmt->fetchAll();

                            $data = array(
                                'status' => 'Exito',
                                'sector' => $sector,
                            );
                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                        }

                    if (in_array("PERM_SECTORES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidsector, codigosector, nombresector, sectoractivo, creacionsector,
                        modificacionsector,fkidzona,nombrezona,fkidtiposector,nombretiposector,fkidplaza,nombreplaza FROM tsector
                        join tzona on tsector.fkidzona=tzona.pkidzona join
                        ttiposector on tsector.fkidtiposector=ttiposector.pkidtiposector join tplaza on tplaza.pkidplaza=tzona.fkidplaza  order by nombresector ASC;
                ";

                        

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $sector = $stmt->fetchAll();

                        $cabeceras = array("Nombre Sector", "Descripción Sector", "Sector Activo/Inactivo", "Creación", "Modificación");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'sector' => $sector,
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
                'modulo' => "Sector",
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

    /*Este funcion realiza la inserccion de un Sector nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigosector":"valor",
    "nombresector":"valor",
    "sectoractivo":"valor",
    "fkidzona":"valor",
    "fkidtiposector":"valor"
    }
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

                    if (in_array("PERM_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionsector = new \Datetime("now");
                            $modificacionsector = new \Datetime("now");

                            $codigosector = (isset($params->codigosector)) ? $params->codigosector : null;
                            $nombresector = (isset($params->nombresector)) ? $params->nombresector : null;
                            $sectoractivo = (isset($params->sectoractivo)) ? $params->sectoractivo : true;
                            $fkidtiposector = (isset($params->fkidtiposector)) ? $params->fkidtiposector : null;
                            $fkidzona = (isset($params->fkidzona)) ? $params->fkidzona : null;

                            if ($nombresector != null && $fkidtiposector != null && $fkidzona != null) {

                                $sector = new Tsector();
                                if ($codigosector != null) {
                                    $sector->setCodigosector($codigosector);
                                }
                                //aqui quede
                                $sector->setNombresector($nombresector);
                                $sector->setSectoractivo($sectoractivo);
                                $sector->setCreacionsector($creacionsector);
                                $sector->setModificacionsector($modificacionsector);
                                $isset_zona = $em->getRepository('ModeloBundle:Tzona')->find($fkidzona);
                                $sector->setFkidzona($isset_zona);
                                $isset_tiposector = $em->getRepository('ModeloBundle:Ttiposector')->find($fkidtiposector);
                                $sector->setFkidtiposector($isset_tiposector);

                                $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "nombresector" => $nombresector,
                                ));

                                if (!is_object($isset_sector)) {

                                    $em->persist($sector);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Sector creado !!',
                                        'sector' => $sector,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "insertar",
                                        "tabla" => "sector",
                                        "valoresrelevantes" => "idsector" . ":" . $sector->getPkidsector(),
                                        "idelemento" => $sector->getPkidsector(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Sector no creado, Duplicado !!',
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
                'modulo' => "Sector",
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
    {"pkidsector":"valor",
    "codigosector":"valor",
    "nombresector":"valor",
    "sectoractivo":"valor"
    "fkidzona":"valor",
    "fkidtiposector":"valor"
    }
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

                    if (in_array("PERM_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacionsector = new \Datetime("now");
                            $modificacionsector = new \Datetime("now");
                            $pkidsector = (isset($params->pkidsector)) ? $params->pkidsector : null;
                            $codigosector = (isset($params->codigosector)) ? $params->codigosector : null;
                            $nombresector = (isset($params->nombresector)) ? $params->nombresector : null;
                            $sectoractivo = (isset($params->sectoractivo)) ? $params->sectoractivo : true;
                            $fkidtiposector = (isset($params->fkidtiposector)) ? $params->fkidtiposector : null;
                            $fkidzona = (isset($params->fkidzona)) ? $params->fkidzona : null;

                            if ($nombresector != null && $pkidsector != null && $fkidtiposector != null && $fkidzona != null) {

                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $pkidsector,
                                ));

                                if (!is_object($sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe !!',
                                    );
                                    return $helpers->json($data);
                                }

                                if ($codigosector != null) {
                                    $sector->setCodigosector($codigosector);
                                }
                                if ($nombresector != null) {
                                    $nombresector_old = $sector->getNombresector();
                                    $sector_id = $sector->getPkidsector();

                                    $sector->setNombresector("p");
                                    $em->persist($sector);
                                    $em->flush();

                                    $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                        "nombresector" => $nombresector,
                                    ));

                                    if (!is_object($isset_sector)) {
                                        $sector->setNombresector($nombresector);
                                    } else {
                                        $sector_old_id = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                            "pkidsector" => $sector_id,
                                        ));

                                        $sector_old_id->setNombresector($nombresector_old);
                                        $em->persist($sector_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Sector no actualizado,  el nombre enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }

                                $sector->setSectoractivo($sectoractivo);
                                //$sector->setCreacionsector($creacionsector);
                                $sector->setModificacionsector($modificacionsector);
                                $isset_zona = $em->getRepository('ModeloBundle:Tzona')->find($fkidzona);
                                $sector->setFkidzona($isset_zona);
                                $isset_tiposector = $em->getRepository('ModeloBundle:Ttiposector')->find($fkidtiposector);
                                $sector->setFkidtiposector($isset_tiposector);

                                $em->persist($sector);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Sector actualizado !!',
                                    'sector' => $sector,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Sector",
                                    "valoresrelevantes" => "idsector" . ":" . $sector->getPkidsector(),
                                    "idelemento" => $sector->getPkidsector(),
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
                'modulo' => "Sector",
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
    {"pkidsector":"valor"}
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

                    if (in_array("PERM_SECTORES", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidsector = (isset($params->pkidsector)) ? $params->pkidsector : null;

                            if ($pkidsector != null) {

                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $pkidsector,
                                ));

                                $isset_sector_puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "fkidsector" => $pkidsector,
                                ));

                                if (!is_object($isset_sector_puesto)) {

                                    if (is_object($sector)) {

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "eliminar",
                                            "tabla" => "Sector",
                                            "valoresrelevantes" => "idsector" . ":" . $sector->getPkidsector(),
                                            "idelemento" => $sector->getPkidsector(),
                                            "origen" => "web",
                                        );

                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));

                                        $em->remove($sector);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'El Sector se ha eliminado correctamente !!',
                                        );

                                    } else {
                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'El Sector a eliminar no existe !!',
                                        );
                                    }

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'No se puede eliminar el Sector, pertenece a un puesto !!',
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
                'modulo' => "Sector",
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
