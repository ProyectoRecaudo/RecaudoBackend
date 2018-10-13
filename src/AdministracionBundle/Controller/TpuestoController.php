<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tpuesto;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TpuestoController extends Controller
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

                        $query = "SELECT pkidpuesto, codigopuesto, numeropuesto, puestoactivo, creacionpuesto,imagenpuesto,
                        modificacionpuesto,alto,ancho
                        ,pkidsector,nombresector
                        ,pkidestadoinfraestructura,nombreestadoinfraestructura
                        ,pkidactividadcomercial,nombreactividadcomercial
                        ,pkidtipopuesto,nombretipopuesto,
                        pkidplaza,nombreplaza,
                        pkidzona,nombrezona
                         FROM tpuesto join tsector on tpuesto.fkidsector=tsector.pkidsector
                        join testadoinfraestructura on tpuesto.fkidestadoinfraestructura=testadoinfraestructura.pkidestadoinfraestructura
                        join tactividadcomercial on tpuesto.fkidactividadcomercial=tactividadcomercial.pkidactividadcomercial
                        join ttipopuesto on tpuesto.fkidtipopuesto=ttipopuesto.pkidtipopuesto
                        join tzona on tzona.pkidzona=tsector.fkidzona
                        join tplaza on tplaza.pkidplaza=tzona.fkidplaza where puestoactivo=true
                         order by numeropuesto ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $puesto = $stmt->fetchAll();

                        
			            $data = array(
                            'status'    => 'Success',
                            'multa'     => $multas,
                        );
                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }

                    if (in_array("PERM_PUESTOS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT pkidpuesto, codigopuesto, numeropuesto, puestoactivo, creacionpuesto,imagenpuesto,
                        modificacionpuesto,alto,ancho
                        ,pkidsector,nombresector
                        ,pkidestadoinfraestructura,nombreestadoinfraestructura
                        ,pkidactividadcomercial,nombreactividadcomercial
                        ,pkidtipopuesto,nombretipopuesto,
                        pkidplaza,nombreplaza,
                        pkidzona,nombrezona
                         FROM tpuesto join tsector on tpuesto.fkidsector=tsector.pkidsector
                        join testadoinfraestructura on tpuesto.fkidestadoinfraestructura=testadoinfraestructura.pkidestadoinfraestructura
                        join tactividadcomercial on tpuesto.fkidactividadcomercial=tactividadcomercial.pkidactividadcomercial
                        join ttipopuesto on tpuesto.fkidtipopuesto=ttipopuesto.pkidtipopuesto
                        join tzona on tzona.pkidzona=tsector.fkidzona
                        join tplaza on tplaza.pkidplaza=tzona.fkidplaza
                         order by numeropuesto ASC;
                ";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $puesto = $stmt->fetchAll();

                        $array_all = array();
                        foreach ($puesto as $puestos) {
                            $puesto_array = array("pkidpuesto" => $puestos['pkidpuesto'],
                                "codigopuesto" => $puestos['codigopuesto'],
                                "numeropuesto" => $puestos['numeropuesto'],
                                "puestoactivo" => $puestos['puestoactivo'],
                                "imagenpuesto" => $puestos['imagenpuesto'],
                                "alto" => $puestos['alto'],
                                "ancho" => $puestos['ancho'],
                                "sector" => array(
                                    "pkidsector" => $puestos['pkidsector'],
                                    "nombresector" => $puestos['nombresector'],
                                    "zona" => array(
                                        "pkidzona" => $puestos['pkidzona'],
                                        "nombrezona" => $puestos['nombrezona'],
                                        "plaza" => array(
                                            "pkidplaza" => $puestos['pkidplaza'],
                                            "nombreplaza" => $puestos['nombreplaza'],
                                        ),
                                    ),
                                ),
                                "estadoinfraestructura" => array("pkidestadoinfraestructura" => $puestos['pkidestadoinfraestructura'], "nombreestadoinfraestructura" => $puestos['nombreestadoinfraestructura']),
                                "actividadcomercial" => array("pkidactividadcomercial" => $puestos['pkidactividadcomercial'], "nombreactividadcomercial" => $puestos['nombreactividadcomercial']),
                                "tipopuesto" => array("pkidtipopuesto" => $puestos['pkidtipopuesto'], "nombretipopuesto" => $puestos['nombretipopuesto']),

                            );
                            array_push($array_all, $puesto_array);
                        }

                        $cabeceras = array("Nombre Puesto", "Descripción Puesto", "Puesto Activo/Inactivo", "Creación", "Modificación");

                        $data = array(
                            'status' => 'Exito',
                            'cabeceras' => $cabeceras,
                            'puesto' => $array_all,
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
                'modulo' => "Puesto",
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

    /*Este funcion realiza la inserccion de un Puesto nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigopuesto":"valor",
    "numeropuesto":"valor",
    "alto":"valor",
    "ancho":"valor",
    "puestoactivo":"valor",
    "fkidsector":"valor",
    "fkidestadoinfraestructura":"valor",
    "fkidactividadcomercial":"valor",
    "fkidtipopuesto":"valor"}
    opcionalmente se debe enviar una imagen del puesto con el nombre fichero_puesto
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

                    if (in_array("PERM_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creacionpuesto = new \Datetime("now");
                            $modificacionpuesto = new \Datetime("now");

                            $codigopuesto = (isset($params->codigopuesto)) ? $params->codigopuesto : null;
                            $numeropuesto = (isset($params->numeropuesto)) ? $params->numeropuesto : null;
                            $puestoactivo = (isset($params->puestoactivo)) ? $params->puestoactivo : true;
                            $alto = (isset($params->alto)) ? $params->alto : null;
                            $ancho = (isset($params->ancho)) ? $params->ancho : null;
                            $fkidsector = (isset($params->fkidsector)) ? $params->fkidsector : null;
                            $fkidestadoinfraestructura = (isset($params->fkidestadoinfraestructura)) ? $params->fkidestadoinfraestructura : null;
                            $fkidactividadcomercial = (isset($params->fkidactividadcomercial)) ? $params->fkidactividadcomercial : null;
                            $fkidtipopuesto = (isset($params->fkidtipopuesto)) ? $params->fkidtipopuesto : null;

                            if ($numeropuesto != null  && $fkidsector != null && $fkidestadoinfraestructura != null && $fkidactividadcomercial != null && $fkidtipopuesto != null) {

                                $puesto = new Tpuesto();
                                //aqui quede
                                $puesto->setNumeropuesto($numeropuesto);
                                $puesto->setPuestoactivo($puestoactivo);
                                if ($codigopuesto != null) {
                                    $puesto->setCodigopuesto($codigopuesto);
                                }

                                $puesto->setCreacionpuesto($creacionpuesto);
                                $puesto->setModificacionpuesto($modificacionpuesto);
                                if ($alto != null) {
                                    $puesto->setAlto($alto);
                                }

                                if ($ancho != null) {
                                    $puesto->setAncho($ancho);
                                }

                                $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));
                                if (!is_object($isset_sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe !! !!',
                                        'codigo' => $fkidsector,
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidsector($isset_sector);

                                $isset_estadoinfraestructura = $em->getRepository('ModeloBundle:Testadoinfraestructura')->findOneBy(array(
                                    "pkidestadoinfraestructura" => $fkidestadoinfraestructura,
                                ));
                                if (!is_object($isset_estadoinfraestructura)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del estadoinfraestructura de la infraestructura no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidestadoinfraestructura($isset_estadoinfraestructura);

                                $isset_actividadcomercial = $em->getRepository('ModeloBundle:Tactividadcomercial')->findOneBy(array(
                                    "pkidactividadcomercial" => $fkidactividadcomercial,
                                ));
                                if (!is_object($isset_actividadcomercial)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la actividadcomercial comercial no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidactividadcomercial($isset_actividadcomercial);

                                $isset_tipopuesto = $em->getRepository('ModeloBundle:Ttipopuesto')->findOneBy(array(
                                    "pkidtipopuesto" => $fkidtipopuesto,
                                ));
                                if (!is_object($isset_tipopuesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo de puesto no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidtipopuesto($isset_tipopuesto);

                                $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                    "numeropuesto" => $numeropuesto,
                                ));

                                if (!is_object($isset_puesto)) {

                                    //imagen puesto
                                    if (isset($_FILES['fichero_puesto'])) {

                                        if ($_FILES['fichero_puesto']['size'] <= 5242880) {

                                            if ($_FILES['fichero_puesto']['type'] == "image/png" || $_FILES['fichero_puesto']['type'] == "image/jpg" || $_FILES['fichero_puesto']['type'] == "image/jpeg" || $_FILES['fichero_puesto']['type'] == "image/tiff") {

                                                $em->persist($puesto);
                                                $em->flush();

                                                $dir_subida = '../web/puestos/';
                                                $extension = explode("/", $_FILES['fichero_puesto']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($puesto->getPkidpuesto() . "_puesto_" . $creacionpuesto->format('Y-m-d_H-i-s') . "." . $extension);

                                                if (move_uploaded_file($_FILES['fichero_puesto']['tmp_name'], $fichero_subido)) {
                                                    $puesto_images = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                                        "pkidpuesto" => $puesto->getPkidpuesto(),
                                                    ));

                                                    $puesto_images->setImagenpuesto($fichero_subido);
                                                    $em->persist($puesto_images);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Exito',
                                                        'msg' => 'Puesto creado !!',
                                                        'rutaimagen' => $fichero_subido,
                                                        'puesto' => $puesto_images,
                                                    );

                                                    $datos = array(
                                                        "idusuario" => $identity->sub,
                                                        "nombreusuario" => $identity->name,
                                                        "identificacionusuario" => $identity->identificacion,
                                                        "accion" => "insertar",
                                                        "tabla" => "Puestos",
                                                        "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                                        "idelemento" => $puesto->getPkidpuesto(),
                                                        "origen" => "web",
                                                    );

                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));

                                                } else {
                                                    $em->remove($puesto);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg' => 'No se ha podido ingresar la imagen del puesto, intente nuevamente !!',
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
                                        $em->persist($puesto);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Puesto creado !!',
                                            'rutaimagen' => 'No se cargo ninguna imagen !!',
                                            'puesto' => $puesto,
                                        );

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "Puestos",
                                            "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                            "idelemento" => $puesto->getPkidpuesto(),
                                            "origen" => "web",
                                        );

                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));
                                    }
                                    //imagen puesto

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Puesto no creado, Duplicado !!',
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
                'modulo' => "Puesto",
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
    {"pkidpuesto":"valor",
    "numeropuesto":"valor",
    "alto":"valor",
    "ancho":"valor",
    "puestoactivo":"valor",
    "fkidsector":"valor",
    "fkidestadoinfraestructura":"valor",
    "fkidactividadcomercial":"valor",
    "fkidtipopuesto":"valor"}
    opcionalmente se debe enviar una imagen del puesto con el nombre fichero_puesto
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

                    if (in_array("PERM_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            //$creacionpuesto = new \Datetime("now");
                            $modificacionpuesto = new \Datetime("now");
                            $pkidpuesto = (isset($params->pkidpuesto)) ? $params->pkidpuesto : null;
                            $codigopuesto = (isset($params->codigopuesto)) ? $params->codigopuesto : null;
                            $numeropuesto = (isset($params->numeropuesto)) ? $params->numeropuesto : null;
                            $puestoactivo = (isset($params->puestoactivo)) ? $params->puestoactivo : true;
                            $alto = (isset($params->alto)) ? $params->alto : null;
                            $ancho = (isset($params->ancho)) ? $params->ancho : null;
                            $fkidsector = (isset($params->fkidsector)) ? $params->fkidsector : null;
                            $fkidestadoinfraestructura = (isset($params->fkidestadoinfraestructura)) ? $params->fkidestadoinfraestructura : null;
                            $fkidactividadcomercial = (isset($params->fkidactividadcomercial)) ? $params->fkidactividadcomercial : null;
                            $fkidtipopuesto = (isset($params->fkidtipopuesto)) ? $params->fkidtipopuesto : null;

                            if ($numeropuesto != null  && $fkidsector != null && $fkidestadoinfraestructura != null && $fkidactividadcomercial != null && $fkidtipopuesto != null) {

                                $puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "pkidpuesto" => $pkidpuesto,
                                ));
                                if (!is_object($puesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del puesto no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                //aqui quede
                                if ($numeropuesto != null) {
                                    $numeropuesto_old = $puesto->getnumeropuesto();
                                    $puesto_id = $puesto->getPkidpuesto();

                                    $puesto->setnumeropuesto("p");
                                    $em->persist($puesto);
                                    $em->flush();

                                    $isset_puesto = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                        "numeropuesto" => $numeropuesto,
                                    ));

                                    if (!is_object($isset_puesto)) {
                                        $puesto->setnumeropuesto($numeropuesto);
                                    } else {
                                        $puesto_old_id = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                            "pkidpuesto" => $puesto_id,
                                        ));

                                        $puesto_old_id->setnumeropuesto($numeropuesto_old);
                                        $em->persist($puesto_old_id);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'error',
                                            'msg' => 'Puesto no actualizado,  el numero del puesto enviado ya existe !!',
                                        );
                                        return $helpers->json($data);
                                    }

                                }
                                $puesto->setPuestoactivo($puestoactivo);
                                if ($codigopuesto != null) {
                                    $puesto->setCodigopuesto($codigopuesto);
                                }

                                //$puesto->setCreacionpuesto($creacionpuesto);
                                $puesto->setModificacionpuesto($modificacionpuesto);
                                if ($alto != null) {
                                    $puesto->setAlto($alto);
                                }

                                if ($ancho != null) {
                                    $puesto->setAncho($ancho);
                                }

                                $isset_sector = $em->getRepository('ModeloBundle:Tsector')->findOneBy(array(
                                    "pkidsector" => $fkidsector,
                                ));
                                if (!is_object($isset_sector)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del sector no existe !! !!',
                                        'codigo' => $fkidsector,
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidsector($isset_sector);

                                $isset_estadoinfraestructura = $em->getRepository('ModeloBundle:Testadoinfraestructura')->findOneBy(array(
                                    "pkidestadoinfraestructura" => $fkidestadoinfraestructura,
                                ));
                                if (!is_object($isset_estadoinfraestructura)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del estadoinfraestructura de la infraestructura no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidestadoinfraestructura($isset_estadoinfraestructura);

                                $isset_actividadcomercial = $em->getRepository('ModeloBundle:Tactividadcomercial')->findOneBy(array(
                                    "pkidactividadcomercial" => $fkidactividadcomercial,
                                ));
                                if (!is_object($isset_actividadcomercial)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la actividadcomercial comercial no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidactividadcomercial($isset_actividadcomercial);

                                $isset_tipopuesto = $em->getRepository('ModeloBundle:Ttipopuesto')->findOneBy(array(
                                    "pkidtipopuesto" => $fkidtipopuesto,
                                ));
                                if (!is_object($isset_tipopuesto)) {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id del tipo de puesto no existe !! !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $puesto->setFkidtipopuesto($isset_tipopuesto);

                                $em->persist($puesto);
                                $em->flush();

                                $data = array(
                                    'status' => 'Exito',
                                    'msg' => 'Puesto actualizado !!',
                                    'puesto' => $puesto,
                                );

                                $datos = array(
                                    "idusuario" => $identity->sub,
                                    "nombreusuario" => $identity->name,
                                    "identificacionusuario" => $identity->identificacion,
                                    "accion" => "editar",
                                    "tabla" => "Puesto",
                                    "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                    "idelemento" => $puesto->getPkidpuesto(),
                                    "origen" => "web",
                                );

                                $auditoria = $this->get(Auditoria::class);
                                $auditoria->auditoria(json_encode($datos));

                                //codigo imagen
                                if (isset($_FILES['fichero_puesto'])) {

                                    if ($_FILES['fichero_puesto']['size'] <= 5242880) {

                                        if ($_FILES['fichero_puesto']['type'] == "image/png" || $_FILES['fichero_puesto']['type'] == "image/jpg" || $_FILES['fichero_puesto']['type'] == "image/jpeg" || $_FILES['fichero_puesto']['type'] == "image/tiff") {

                                            $em->persist($puesto);
                                            $em->flush();

                                            $dir_subida = '../web/puestos/';
                                            $extension = explode("/", $_FILES['fichero_puesto']['type'])[1];
                                            $fichero_subido = $dir_subida . basename($puesto->getPkidpuesto() . "_puesto_" . $modificacionpuesto->format('Y-m-d_H-i-s') . "." . $extension);

                                            if (move_uploaded_file($_FILES['fichero_puesto']['tmp_name'], $fichero_subido)) {

                                                $puesto_images = $em->getRepository('ModeloBundle:Tpuesto')->findOneBy(array(
                                                    "pkidpuesto" => $puesto->getPkidpuesto(),
                                                ));

                                                $imagen_old = $puesto->getImagenpuesto();
                                                if ($imagen_old != null) {
                                                    unlink($puesto->getImagenpuesto());
                                                }
                                                $puesto_images->setImagenpuesto($fichero_subido);
                                                $em->persist($puesto_images);
                                                $em->flush();

                                                $data = array(
                                                    'status' => 'Exito',
                                                    'msg' => 'Puesto actualizado !!',
                                                    'rutaimagen' => $fichero_subido,
                                                    'puesto' => $puesto_images,
                                                );

                                                $datos = array(
                                                    "idusuario" => $identity->sub,
                                                    "nombreusuario" => $identity->name,
                                                    "identificacionusuario" => $identity->identificacion,
                                                    "accion" => "editar",
                                                    "tabla" => "Puestos",
                                                    "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                                    "idelemento" => $puesto->getPkidpuesto(),
                                                    "origen" => "web",
                                                );

                                                $auditoria = $this->get(Auditoria::class);
                                                $auditoria->auditoria(json_encode($datos));

                                            } else {
                                                $data = array(
                                                    'status' => 'Exito/Error',
                                                    'msg' => 'Se actualizaron los datos correctamente, no se pudo actualizar la imagen del puesto, intente nuevamente !!',
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
                                    $em->persist($puesto);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'Puesto actualizado !!',
                                        'rutaimagen' => 'No se cargo ninguna imagen !!',
                                        'puesto' => $puesto,
                                    );

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "editar",
                                        "tabla" => "Puestos",
                                        "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                        "idelemento" => $puesto->getPkidpuesto(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));
                                }
                                //codigo imagen

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
                'modulo' => "Puesto",
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
    {"pkidpuesto":"valor"}
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

                    if (in_array("PERM_PUESTOS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $pkidpuesto = (isset($params->pkidpuesto)) ? $params->pkidpuesto : null;

                            if ($pkidpuesto != null) {

                                $puesto = $this->getDoctrine()->getRepository("ModeloBundle:Tpuesto")->findOneBy(array(
                                    "pkidpuesto" => $pkidpuesto,
                                ));
                                /*
                                $isset_puesto_cualquiera = $this->getDoctrine()->getRepository("ModeloBundle:Tcualquiera")->findOneBy(array(
                                "fkidpuesto" => $pkidpuesto,
                                ));

                                if (!is_object($isset_puesto_cualquiera)) {
                                 */
                                if (is_object($puesto)) {

                                    $datos = array(
                                        "idusuario" => $identity->sub,
                                        "nombreusuario" => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        "accion" => "eliminar",
                                        "tabla" => "Puesto",
                                        "valoresrelevantes" => "idpuesto" . ":" . $puesto->getPkidpuesto(),
                                        "idelemento" => $puesto->getPkidpuesto(),
                                        "origen" => "web",
                                    );

                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));

                                    $em->remove($puesto);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'Exito',
                                        'msg' => 'El Puesto se ha eliminado correctamente !!',
                                    );

                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El Puesto a eliminar no existe !!',
                                    );
                                }
                                /*
                            } else {
                            $data = array(
                            'status' => 'error',
                            'msg' => 'No se puede eliminar el Puesto, pertenece a un cualquiera !!',
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
                'modulo' => "Puesto",
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
