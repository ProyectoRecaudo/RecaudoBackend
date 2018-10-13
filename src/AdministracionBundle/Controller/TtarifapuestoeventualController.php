<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Ttarifapuestoeventual;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TtarifapuestoeventualController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /**
     * @Route("/query")
     * Funcion para mostrar las tarifapuestoeventual registradas
     * Recibe pkidplaza para filtrar por plazas a la que pertenece la tarifa
     * activo para recornar las tarifas activas
     * con el id de la plaza que se desea conocer.
     * recibe el parametro tarifa con valor true para retornar las plazas que tangan
     * tarifas activas. 
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
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

                    $idPlaza = $request->get('pkidplaza', null); 

                    if($idPlaza != null){

                        if(in_array("PERM_GENERICOS", $permisosDeserializados)){
                            $em = $this->getDoctrine()->getManager();
                            $db = $em->getConnection();
                            
                            //Consulta de tarifa puestoeventual por plaza
                                                           
                            $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                        
                            if($plaza){
                                $tarifasPuestoEventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->createQueryBuilder('t')
                                                ->select('t.pkidtarifapuestoeventual,t.valortarifapuestoeventual')
                                                ->where('t.tarifapuestoeventualactivo = :tarifapuestoeventualactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->setParameter('tarifapuestoeventualactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->getQuery()
                                                ->getResult();
                                
                                $data = array(
                                    'status'                 => 'Exito',
                                    'tarifaspuestoeventual'  => $tarifasPuestoEventual,
                                );
                            }else{
                                $data = array(
                                    'status'=> 'Error',
                                    'msg'   => 'La plaza no existe!!'
                                );
                            }
                            return $helpers->json($data);
                        }
                    }

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $query = "SELECT 
                                    pkidtarifapuestoeventual,
                                    valortarifapuestoeventual,
                                    descripciontarifapuestoeventual,
                                    numeroresoluciontarifapuestoeventual,
                                    documentoresoluciontarifapuestoeventual,
                                    fechainicio,
                                    fechafin,
                                    craciontarifapuestoeventual,
                                    modificaciontarifapuestoeventual,
                                    fkidplaza,
                                    nombreplaza,
                                    tarifapuestoeventualactivo
                                FROM ttarifapuestoeventual 
                                    JOIN tplaza ON ttarifapuestoeventual.fkidplaza = tplaza.pkidplaza
                                ORDER BY valortarifapuestoeventual ASC;";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $tarifapuestoeventual = $stmt->fetchAll();

                        $array_all = array();
                        foreach ($tarifapuestoeventual as $tarifapuestoeventuales) {
                            $reg = array("pkidtarifapuestoeventual" => $tarifapuestoeventuales['pkidtarifapuestoeventual'],
                                "valortarifapuestoeventual" => $tarifapuestoeventuales['valortarifapuestoeventual'],
                                "descripciontarifapuestoeventual" => $tarifapuestoeventuales['descripciontarifapuestoeventual'],
                                "numeroresoluciontarifapuestoeventual" => $tarifapuestoeventuales['numeroresoluciontarifapuestoeventual'],
                                "documentoresoluciontarifapuestoeventual" => $tarifapuestoeventuales['documentoresoluciontarifapuestoeventual'],
                                "fechainicio"=> $tarifapuestoeventuales['fechainicio'],
                                "fechafin" => $tarifapuestoeventuales['fechafin'],
                                "pkidplaza" => $tarifapuestoeventuales['fkidplaza'],
                                "nombreplaza" => $tarifapuestoeventuales['nombreplaza'],
                                "tarifapuestoeventualactivo" => $tarifapuestoeventuales['tarifapuestoeventualactivo']
                            );
                            array_push($array_all, $reg);
                        }

                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valortarifapuestoeventual",
                                "nombreetiqueta" => "Tarifa Puesto Eventual"
                            ),
                            array(
                                "nombrecampo"    => "descripciontarifapuestoeventual",
                                "nombreetiqueta" => "Descripción"
                            ),
                            array(
                                "nombrecampo"    => "numeroresoluciontarifapuestoeventual",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresoluciontarifapuestoeventual",
                                "nombreetiqueta" => "Documento"
                            ),
                            array(
                                "nombrecampo"    => "fechainicio",
                                "nombreetiqueta" => "Fecha de Inicio"
                            ),
                            array(
                                "nombrecampo"    => "fechafin",
                                "nombreetiqueta" => "Fecha de Fin"
                            ),
                            array(
                                "nombrecampo"    => "nombreplaza",
                                "nombreetiqueta" => "Plaza"
                            ),
                            array(
                                "nombrecampo"    => "tarifapuestoeventualactivo",
                                "nombreetiqueta" => "Tarifa Puesto Eventual Activa/Inactiva"
                            ),
                        );
                        
                        $data = array(
                            'status'               => 'Exito',
                            'cabeceras'            => $cabeceras,
                            'tarifapuestoeventual' => $array_all,
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
                'modulo' => "Tarifa Puesto Eventual",
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

    /*Este funcion realiza la inserccion de un Tarifa Puesto Eventual nuevo
    como parametros se deben enviar los siguientes:
    un parametro con el nombre json, y como datos del json los siguientes:
    {"codigotarifapuestoeventual":"valor",
    "nombretarifapuestoeventual":"valor",
    "descripciontarifapuestoeventual":"valor",
    "tarifapuestoeventualactivo":"valor",
    "fechainicio":"valor",
    "fechafin":"valor"}
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

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {

                        $em = $this->getDoctrine()->getManager(); //entiti manager

                        //REcoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        if ($json != null) {

                            $creaciontarifapuestoeventual = new \Datetime("now");
                            $modificaciontarifapuestoeventual = new \Datetime("now");

                            $valortarifapuestoeventual = (isset($params->valortarifapuestoeventual)) ? $params->valortarifapuestoeventual : null;
                            $numeroresoluciontarifapuestoeventual = (isset($params->numeroresoluciontarifapuestoeventual)) ? $params->numeroresoluciontarifapuestoeventual : null;
                            $tarifapuestoeventualactivo = (isset($params->tarifapuestoeventualactivo)) ? $params->tarifapuestoeventualactivo : true;
                            
                            $fkidplaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null;
                            $descripciontarifapuestoeventual = (isset($params->descripciontarifapuestoeventual)) ? $params->descripciontarifapuestoeventual : null; 
                            $fechaInicio = (isset($params->fechainicio)) ? new \DateTime($params->fechainicio) : null;
                            $fechaFin = (isset($params->fechafin)) ? new \DateTime($params->fechafin) : null;

                            if ($valortarifapuestoeventual != null && $numeroresoluciontarifapuestoeventual != null && $fkidplaza != null) {

                                $ultimaTarifaPuestoEventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->findOneBy(array(
                                    "tarifapuestoeventualactivo" => true,
                                    "fkidplaza"                  => $fkidplaza
                                ));
                                
                                //si existe una tarifa activa, la desactiva para crear una nueva. 
                                if($ultimaTarifaPuestoEventual && $tarifapuestoeventualactivo == true){
                                    $ultimaTarifaPuestoEventual->setTarifapuestoeventualactivo(false);
                                }
                                
                                $tarifapuestoeventual = new Ttarifapuestoeventual();

                                if ($descripciontarifapuestoeventual != null) {
                                    $tarifapuestoeventual->setDescripciontarifapuestoeventual($descripciontarifapuestoeventual);
                                }

                                $tarifapuestoeventual->setValortarifapuestoeventual($valortarifapuestoeventual);
                                $tarifapuestoeventual->setNumeroresoluciontarifapuestoeventual($numeroresoluciontarifapuestoeventual);
                                $tarifapuestoeventual->setDocumentoresoluciontarifapuestoeventual("sin documento");
                                $tarifapuestoeventual->setTarifapuestoeventualactivo($tarifapuestoeventualactivo);
                                $tarifapuestoeventual->setDescripciontarifapuestoeventual($descripciontarifapuestoeventual);
                                $isset_plaza = $em->getRepository('ModeloBundle:Tplaza')->findOneBy(array("pkidplaza"=>$fkidplaza));
                                if (is_object($isset_plaza)) {
                                    $tarifapuestoeventual->setFkidplaza($isset_plaza);
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'El id de la plaza no existe !!',
                                    );
                                    return $helpers->json($data);
                                }
                                $tarifapuestoeventual->setCraciontarifapuestoeventual($creaciontarifapuestoeventual);
                                $tarifapuestoeventual->setModificaciontarifapuestoeventual($modificaciontarifapuestoeventual);
                                
                                $tarifapuestoeventual->setFechainicio($fechaInicio);
                                $tarifapuestoeventual->setFechafin($fechaFin);

                                $isset_tarifapuestoeventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->findOneBy(array(
                                    "numeroresoluciontarifapuestoeventual" => $numeroresoluciontarifapuestoeventual,
                                ));

                                if (!is_object($isset_tarifapuestoeventual)) {

                                    //documento
                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {

                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {

                                                $em->persist($tarifapuestoeventual);
                                                $em->flush();

                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($tarifapuestoeventual->getPkidtarifapuestoeventual() . "_documento_" .$creaciontarifapuestoeventual->format('Y-m-d_H-i-s') . "." . $extension);

                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $tarifapuestoeventual_doc = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->findOneBy(array(
                                                        "pkidtarifapuestoeventual" => $tarifapuestoeventual->getPkidtarifapuestoeventual(),
                                                    ));

                                                    $tarifapuestoeventual_doc->setDocumentoresoluciontarifapuestoeventual($fichero_subido);
                                                    $em->persist($tarifapuestoeventual_doc);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Exito',
                                                        'msg' => 'Tarifa Puesto Eventual creado !!',
                                                        'tarifapuestoeventual' => $tarifapuestoeventual_doc,
                                                    );

                                                    $datos = array(
                                                        "idusuario" => $identity->sub,
                                                        "nombreusuario" => $identity->name,
                                                        "identificacionusuario" => $identity->identificacion,
                                                        "accion" => "insertar",
                                                        "tabla" => "tarifapuestoeventual",
                                                        "valoresrelevantes" => "idtarifapuestoeventual" . ":" . $tarifapuestoeventual->getPkidtarifapuestoeventual(),
                                                        "idelemento" => $tarifapuestoeventual->getPkidtarifapuestoeventual(),
                                                        "origen" => "web",
                                                    );
                
                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));

                                                } else {
                                                    $em->remove($tarifapuestoeventual);
                                                    $em->flush();

                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg' => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                    );
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg' => 'Solo se aceptan archivos en formato PDF !!',
                                                );
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg' => 'El tamaño del documento debe ser MAX 5MB !!',
                                            );
                                        }
                                    } else {
                                        $em->persist($tarifapuestoeventual);
                                        $em->flush();

                                        $data = array(
                                            'status' => 'Exito',
                                            'msg' => 'Tarifa Puesto Eventual creado !!',
                                            'tarifapuestoeventual' => $tarifapuestoeventual,
                                        );

                                        $datos = array(
                                            "idusuario" => $identity->sub,
                                            "nombreusuario" => $identity->name,
                                            "identificacionusuario" => $identity->identificacion,
                                            "accion" => "insertar",
                                            "tabla" => "tarifapuestoeventual",
                                            "valoresrelevantes" => "idtarifapuestoeventual" . ":" . $tarifapuestoeventual->getPkidtarifapuestoeventual(),
                                            "idelemento" => $tarifapuestoeventual->getPkidtarifapuestoeventual(),
                                            "origen" => "web",
                                        );

                                        $auditoria = $this->get(Auditoria::class);
                                        $auditoria->auditoria(json_encode($datos));
                                    }

                                    //documento
                                } else {
                                    $data = array(
                                        'status' => 'error',
                                        'msg' => 'Tarifa Puesto Eventual no creado, numero resolucion Duplicado !!',
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
                'modulo' => "Tarifa Puesto Eventual",
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
    {"pkidtarifapuestoeventual":"valor",
    "codigotarifapuestoeventual":"valor",
    "nombretarifapuestoeventual":"valor",
    "descripciontarifapuestoeventual":"valor",
    "tarifapuestoeventualactivo":"valor"
    "fechainicio":"valor",
    "fechafin":"valor"}
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
        try {

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_TARIFAS", $permisosDeserializados)) {
                        
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Tarifa puesto eventual no actualizada!!',
                        );

                        if ($json != null) {
                            
                            $idTarifaPuestoEventual = (isset($params->pkidtarifapuestoeventual)) ? $params->pkidtarifapuestoeventual : null;

                            if($idTarifaPuestoEventual != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $tarifapuestoeventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->find($idTarifaPuestoEventual);
                    
                                if($tarifapuestoeventual){
                          
                                    if(isset($params->valortarifapuestoeventual)){

                                        if($params->valortarifapuestoeventual != $tarifapuestoeventual->getValortarifapuestoeventual()){
                                            $tarifapuestoeventual->setValortarifapuestoeventual($params->valortarifapuestoeventual);
                                            $tarifapuestoeventual->setValorincrementoporcentual(0);
                                        }
                                    }

                                    if(isset($params->descripciontarifapuestoeventual)){
                                        $tarifapuestoeventual->setDescripciontarifapuestoeventual($params->descripciontarifapuestoeventual);
                                    }

                                    if(isset($params->numeroresoluciontarifapuestoeventual)){
                                        
                                        $numeroresolucionTarifaPuestoEventual = $params->numeroresoluciontarifapuestoeventual;
                                        //revisa en la tabla Ttarifapuestoeventual si el valor que se desea asignar no existe en la misma plaza 
                                        $query = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->createQueryBuilder('t')
                                            ->where('t.numeroresoluciontarifapuestoeventual = :numeroresoluciontarifapuestoeventual and t.pkidtarifapuestoeventual != :pkidtarifapuestoeventual')
                                            ->setParameter('numeroresoluciontarifapuestoeventual', $numeroresolucionTarifaPuestoEventual)
                                            ->setParameter('pkidtarifapuestoeventual', $idTarifaPuestoEventual)
                                            ->getQuery();
                                     
                                        $resolucionDuplicated = $query->getResult();

                                        if(!$resolucionDuplicated){
                                            $tarifapuestoeventual->setNumeroresoluciontarifapuestoeventual($numeroresolucionTarifaPuestoEventual);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Resolucion duplicada!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }            
                                    
                                    if(isset($params->fkidplaza)){
                                        
                                        $idPlaza = $params->fkidplaza;
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                                                
                                        if($plaza){
                                            $tarifapuestoeventual->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $idPlaza = $tarifapuestoeventual->getFkidplaza();
                                    }

                                    if(isset($params->tarifapuestoeventualactivo)){

                                        if($params->tarifapuestoeventualactivo == true){
                                            
                                            $tarifapuestoeventualDuplicated = $em->createQueryBuilder()->select("t")
                                                ->from('ModeloBundle:Ttarifapuestoeventual', 't')
                                                ->where('t.tarifapuestoeventualactivo = :tarifapuestoeventualactivo')
                                                ->andwhere('t.fkidplaza = :fkidplaza')
                                                ->andwhere('t.pkidtarifapuestoeventual != :pkidtarifapuestoeventual')
                                                ->setParameter('tarifapuestoeventualactivo', true)
                                                ->setParameter('fkidplaza', $idPlaza)
                                                ->setParameter('pkidtarifapuestoeventual', $idTarifaPuestoEventual)
                                                ->getQuery()
                                                ->getResult();
                                            
                                            if($tarifapuestoeventualDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe una tarifa activa en la misma plaza y tipo de animal!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        
                                        $tarifapuestoeventual->setTarifapuestoeventualactivo($params->tarifapuestoeventualactivo);
                                    }     
                
                                    $tarifapuestoeventual->setModificaciontarifapuestoeventual($today);
                                    
                                    if(isset($params->fechainicio)){
                                        $tarifapuestoeventual->setFechainicio(new \DateTime($params->fechainicio));
                                    }

                                    if(isset($params->fechafin)){
                                        $tarifapuestoeventual->setFechafin(new \DateTime($params->fechafin));
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idTarifaPuestoEventual . "_tarifapuestoeventual_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $tarifapuestoeventual->getDocumentoresoluciontarifapuestoeventual();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                   $tarifapuestoeventual->setDocumentoresoluciontarifapuestoeventual($fichero_subido);
                                                } else {
                                                    $data = array(
                                                        'status' => 'Error',
                                                        'msg'    => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                    );
                                                    return $helpers->json($data);
                                                }
                                            } else {
                                                $data = array(
                                                    'status' => 'Error',
                                                    'msg'    => 'Solo se aceptan archivos en formato PDF !!',
                                                );
                                                return $helpers->json($data);
                                            }
                                        } else {
                                            $data = array(
                                                'status' => 'Error',
                                                'msg'    => 'El tamaño del documento debe ser MAX 5MB !!',
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $documento = $params->documentoresoluciontarifapuestoeventual;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $tarifapuestoeventual->getDocumentoresoluciontarifapuestoeventual();
                                            
                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $tarifapuestoeventual->setDocumentoresoluciontarifapuestoeventual("sin documento");
                                            }
                                        }
                                    }
                
                                    $em->persist($tarifapuestoeventual);
                                    $em->flush();
                
                                    $data = array(
                                        'status'       => 'Exito',
                                        'msg'          => 'Tarifa puesto eventual actualizada!!',
                                        'tarifapuestoeventual' => $tarifapuestoeventual,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Ttarifapuestoeventual',
                                        "valoresrelevantes"     => 'idTarifaPuestoEventual:'.$idTarifaPuestoEventual.',valorTarifaPuestoEventual:'.$tarifapuestoeventual->getValortarifapuestoeventual(),
                                        'idelemento'            => $idTarifaPuestoEventual,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La Tarifa puesto eventual no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la Tarifa puesto eventual a editar es nulo!!'
                                );
                            }

                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                }else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);


        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $exepcion = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => "Tarifa Puesto Eventual",
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
