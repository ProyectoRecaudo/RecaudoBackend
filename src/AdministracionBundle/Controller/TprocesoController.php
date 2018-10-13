<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tproceso;
use ModeloBundle\Entity\Tasignacionpuesto;
use ModeloBundle\Entity\Tabogado;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TprocesoController extends Controller
{
    /**
     * @Route("/")
    */
    public function indexAction()
    {
        //return $this->render('AdministracionBundle:Default:index.html.twig');
    }

    /**
     * @Route("/new")
     * Funcion para registrar un proceso
     * recibe los datos en un json llamado json que contenga otros json o arreglos con los datos 
     * de cada proceso a guardar, se registran los procesos en conjunto.
     * cada json debe contener:
     * procesoactivo, si el proceso esta activa o no
     * fkidasignacionpuesto, el id de la asignacion de puestoa la que pertenece la asignacion
     * fkidabogado, el id del abogado al que sera asociada la asignacion
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
     */
    public function newProcesoAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        
        try{

            if (!empty($request->get('authorization')) && !empty($request->get('json'))) {

                $token = $request->get('authorization', null);
                $authCheck = $jwt_auth->checkToken($token);

                if ($authCheck) {

                    $identity = $jwt_auth->checkToken($token, true);

                    $permisosSerializados = $identity->permisos;
                    $permisosDeserializados = unserialize($permisosSerializados);

                    if (in_array("PERM_USUARIOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $parametros = json_decode($json);

                        /**
                         * array error por defecto
                         */
                        $error = array(
                            'status' => 'error',
                            'msg'    => 'Procesos no creados!!',
                        );

                        if ($json != null) {

                            $procesos = array();
                            $errores = array();

                            foreach ($parametros as $params) {
                                
                                $procesoActivo = (isset($params->procesoactivo)) ? $params->procesoactivo : true;
                                $idAsignacionPuesto = (isset($params->fkidasignacionpuesto)) ? $params->fkidasignacionpuesto : null; 
                                $idAbogado = (isset($params->fkidabogado)) ? $params->fkidabogado : null; 
                                
                                if($idAsignacionPuesto != null && $idAbogado !=null){

                                    $asignacionPuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->find($idAsignacionPuesto);
                                    $abogado = $em->getRepository('ModeloBundle:Tabogado')->find($idAbogado);
                                
                                    if($asignacionPuesto){
                                    
                                        if($abogado){
                                        
                                            //Valida si el asignacion de puesto tiene una proceso activo
                                            $procesoDuplicated = $em->getRepository('ModeloBundle:Tproceso')->findOneBy(array(
                                                "fkidasignacionpuesto" => $idAsignacionPuesto,
                                                "procesoactivo"        => true
                                            ));
                                            
                                            if(!$procesoDuplicated){

                                                $proceso = new Tproceso();
                                                $proceso->setProcesoactivo($procesoActivo);
                                                $proceso->setCreacionproceso($today);
                                                $proceso->setModificacionproceso($today);
                                                $proceso->setFkidabogado($abogado);
                                                $proceso->setFkidasignacionpuesto($asignacionPuesto);
                                            
                                                $em->persist($proceso);
                                                
                                                array_push($procesos, $proceso);
                                            }else{
                                                $error = array(
                                                    'status' => 'error',
                                                    'msg'    => 'La asignacion de puesto con id:'.$idAsignacionPuesto.', ya tiene un proceso asignado!!'
                                                );
                                                array_push($errores, $error);
                                            }
                                        }else{
                                            $error = array(
                                                'status'=> 'error',
                                                'msg'   => 'El abogado con id:'.$idAbogado.', no existe!!'
                                            );
                                            array_push($errores, $error);
                                        }
                                    }else{
                                        $error = array(
                                            'status'=> 'error',
                                            'msg'   => 'La asignacion de puesto con id:'.$idAsignacionPuesto.', no existe!!'
                                        );
                                        array_push($errores, $error);
                                    }
                                }else{
                                    $error = array(
                                        'status' => 'error',
                                        'msg'    => 'Algunos de los valores son nulos!!',
                                    );
                                    array_push($errores, $error);
                                }
                            }    
                            
                            if($errores){
                                $data = array(
                                    'status'   => 'Error',
                                    'msg'      => 'Procesos no creados!!',
                                    'errores'  => $errores
                                );
                            }else{
                                $em->flush();
                                $data = array(
                                    'status'   => 'Exito',
                                    'msg'      => 'Procesos creados!!',
                                    'procesos' => $procesos
                                );

                                foreach($procesos as $proceso){
                                    //una vez insertados los datos en la proceso se realiza el proceso de auditoria
                                    $datos = array(
                                        'idusuario'             => $identity->sub,
                                        'nombreusuario'         => $identity->name,
                                        'identificacionusuario' => $identity->identificacion,
                                        'accion'                => 'insertar',
                                        "tabla"                 => 'Tproceso',
                                        "valoresrelevantes"     => 'idProceso:'.$proceso->getPkidproceso().',fkidasignacionpuesto:'.$proceso->getFkidasignacionpuesto().",fkidabogado:".$proceso->getFkidabogado(),
                                        'idelemento'            => $proceso->getPkidproceso(),
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria($helpers->json($datos));
                                }
                            }

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'Parametro json es nulo!!',
                            );
                        }
                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'No tiene los permisos!!',
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Token no valido!!',
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor!!',
                );
            }
            return $helpers->json($data);

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => 'Proceso',
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => 'web'
            );

            try{
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/edit")
     * Funcion para modificar una proceso
     * recibe los datos en un json llamado json con los datos
     * pkidproceso=>obligatorio, id de la proceso a editar
     * procesoactivo, si el proceso esta activa o no
     * fkidasignacionpuesto, el id de la asignacion de puesto a la que pertenece la asignacion
     * fkidabogado, el id del abogado al que sera asociada la asignacion
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editProcesoAction(Request $request)
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
                        
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));

                        //entiti manager
                        $em = $this->getDoctrine()->getManager(); 

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);
                        $validation = true;

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Proceso no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idProceso = (isset($params->pkidproceso)) ? $params->pkidproceso : null;

                            if($idProceso != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $db = $em->getConnection();
                                $proceso = $em->getRepository('ModeloBundle:Tproceso')->find($idProceso);

                                if($proceso){

                                    $procesoActivo = (isset($params->procesoactivo)) ? $params->procesoactivo : false;

                                    $proceso->setModificacionproceso($today);
                                   
                                    //validacion de asignacion de puesto-- perndiente de confirmacion, en el momento no deja cambiar la asignacion de puesto
                                    /*if($asignacionPuesto){
                                      
                                        if($procesoActivo == true){
                                            //Valida si el puesto tiene una asignacion a abogado activa que sea diferente a la que se esta editando
                                            $procesoDuplicated  = $em->getRepository('ModeloBundle:Tproceso')->createQueryBuilder('p')
                                                ->where('p.fkidasignacionpuesto = :fkidasignacionpuesto')
                                                ->andwhere('p.procesoactivo = :procesoactivo')
                                                ->andwhere('p.pkidproceso != :pkidproceso')
                                                ->setParameter('fkidasignacionpuesto',$idAsignacionPuesto)
                                                ->setParameter('pkidproceso', $idProceso)
                                                ->setParameter('procesoactivo', true)
                                                ->getQuery()->getResult();

                                            if($procesoDuplicated){

                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El puesto ya tiene un proceso asignado!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                        $proceso->setFkidasignacionpuesto($asignacionPuesto);
                                    }else{
                                        $data = array(
                                            'status'=> 'Error',
                                            'msg'   => 'La puesto no existe!!'
                                        );
                                        return $helpers->json($data);
                                    }*/

                                    if(isset($params->procesoactivo)){

                                        if($params->procesoactivo == true && $proceso->getProcesoactivo() == false){
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'No es posible reactivar el proceso'
                                            );
                                            return $helpers->json($data);
                                        }
                                        else{
                                            /**
                                             * Al desactivar un proceso, si tiene un acuerdo asociado tambien se desactiva. 
                                             */
                                            if($params->procesoactivo == false){
                                                $acuerdo = $em->getRepository('ModeloBundle:Tacuerdo')->findOneBy(array(
                                                    "fkidproceso" => $idProceso
                                                ));

                                                $acuerdo->setAcuerdoactivo(false);
                                            }
                                            $proceso->setProcesoactivo($procesoActivo);
                                        }   
                                    }

                                    if(isset($params->fkidasignacionpuesto)){

                                        $asignacionPuesto = $em->getRepository('ModeloBundle:Tasignacionpuesto')->find($proceso->getFkidasignacionpuesto());
                                        $idAsignacionPuesto = $asignacionPuesto->getPkidasignacionpuesto();

                                        if($params->fkidasignacionpuesto != $idAsignacionPuesto){
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'No es posible cambiar la asignacion de puesto'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }
                                    
                                    /**
                                     * validacion de abogado
                                     */
                                    if(isset($params->fkidabogado)){

                                        $idAbogado = $params->fkidabogado;
                                        $abogado = $em->getRepository('ModeloBundle:Tabogado')->find($idAbogado);
                                        
                                        if($abogado){
                                            $proceso->setFkidabogado($abogado);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'El abogado no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }

                                    $em->persist($proceso);
                                    $em->flush();
                
                                    $data = array(
                                        'status'  => 'Exito',
                                        'msg'     => 'Proceso actualizado!!',
                                        'proceso' => $proceso,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tproceso',
                                        "valoresrelevantes"     => 'idProceso:'.$idProceso,
                                        'idelemento'            => $idProceso,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'el proceso no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de el proceso a editar es nulo!!'
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

        }catch(\Exception $e){

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Proceso",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try{
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));
               
            }catch (\Exception $a){}

            throw $e;
        }
    }

    /**
     * @Route("/query")
     * Funcion para mostrar todas las asignaciones de puesto y mostrar cuales tienen
     * un proceso y un acuerdo
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryProcesoAction(Request $request)
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
                        
                        $asignacion = $request->get('asignacion', null); 

                        if($asignacion != null && $asignacion == true){

                            /**
                             * Consulta los asignaciones a las que se les puede asignar un proceso
                             */
                            $query = "SELECT
                                        tasignacionpuesto.pkidasignacionpuesto,
                                        tasignacionpuesto.numeroresolucionasignacionpuesto,
                                        tbeneficiario.pkidbeneficiario,
                                        tbeneficiario.identificacionbeneficiario,
                                        tbeneficiario.nombrebeneficiario,
                                        tpuesto.pkidpuesto,
                                        tpuesto.numeropuesto,
                                        tsector.pkidsector,
                                        tsector.nombresector,
                                        tzona.pkidzona,
                                        tzona.nombrezona,
                                        tplaza.pkidplaza,
                                        tplaza.nombreplaza,
                                        (SELECT count(pkidfactura)
                                        FROM tfactura
                                        WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) AS mesessinpagar
                                    FROM tasignacionpuesto
                                        JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                        JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                        JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                        JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                        JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                    WHERE
                                        tasignacionpuesto.pkidasignacionpuesto not in (SELECT fkidasignacionpuesto
                                                                FROM tproceso
                                                                WHERE tproceso.procesoactivo = true)
                                        AND tasignacionpuesto.asignacionpuestoactivo = true
                                    ORDER BY tasignacionpuesto.creacionasignacionpuesto DESC";
                
                            $stmt = $db->prepare($query);
                            $params = array();
                            $stmt->execute($params);
                            $asignaciones = $stmt->fetchAll();

                            $array_all = array();

                            foreach ($asignaciones as $asignacion) {

                                /**
                                 * Solo muestra la lista de asitnaciones si los meses sin pagar es mayor a 2
                                 */
                                if($asignacion['mesessinpagar'] >= 2){
    
                                    $factura =  $em->getRepository('ModeloBundle:Tfactura')->findOneBy(array(
                                        "fkidasignacionpuesto" => $asignacion['pkidasignacionpuesto'],
                                        "facturaactivo" => true
                                    ));
         
                                    $saldoAsignacion = 0;
                                    $saldoMultas = 0;
                                    $saldoAcuerdo = 0;
                                    $saldoDeuda = 0;
                                    $saldoDeudaAcuerdo = 0;
                                    $saldoporpagar = 0;
        
                                    if($factura){
                                        $saldoAsignacion = $factura->getSaldoasignacion();
                                        $saldoMultas = $factura->getSaldomultas();
                                        $saldoAcuerdo =  $factura->getSaldoacuerdo();
                                        $saldoDeuda =  $factura->getSaldodeuda();
                                        $saldoDeudaAcuerdo =  $factura->getSaldodeudaacuerdo();
                                        $saldoporpagar =  $factura->getSaldoporpagar();
                                    }
                                    $factura = array(
                                        "mesessinpagar"     => $asignacion['mesessinpagar'],
                                        "saldoasignacion"   => $saldoAsignacion,
                                        "saldomultas"       => $saldoMultas,
                                        "saldoacuerdo"      => $saldoAcuerdo,
                                        "saldodeuda"        => $saldoDeuda,
                                        "saldodeudaacuerdo" => $saldoDeudaAcuerdo,
                                        "saldoporpagar"     => $saldoporpagar,
                                    );
                                    
                                    $asignacionList = array(
                                        "asignacionpuesto" => array(
                                            "pkidasignacionpuesto"             => $asignacion['pkidasignacionpuesto'],
                                            "numeroresolucionasignacionpuesto" => $asignacion['numeroresolucionasignacionpuesto'],
                                        ),
                                        "beneficiario" => array(
                                            "pkidbeneficiario"           => $asignacion['pkidbeneficiario'],
                                            "identificacionbeneficiario" => $asignacion['identificacionbeneficiario'],
                                            "nombrebeneficiario"         => $asignacion['nombrebeneficiario'],
                                        ),
                                        "puesto" => array(
                                            "pkidpuesto"   => $asignacion['pkidpuesto'],
                                            "numeropuesto" => $asignacion['numeropuesto'],
                                        ),
                                        "sector" => array(
                                            "pkidsector"   => $asignacion['pkidsector'],
                                            "nombresector" => $asignacion['nombresector'],
                                        ),
                                        "zona" => array(
                                            "pkidzona"   => $asignacion['pkidzona'],
                                            "nombrezona" => $asignacion['nombrezona'],
                                        ),
                                        "plaza" => array(
                                            "pkidplaza"   => $asignacion['pkidplaza'],
                                            "nombreplaza" => $asignacion['nombreplaza'],
                                        ),
                                        "factura" => $factura
                                    );
                                      
                                    array_push($array_all, $asignacionList);
                                }
                            }
                            
                            $cabeceras = array("Asignacion de Puesto","Beneficiario","Puesto","Sector","Zona","Plaza");

                            $data = array(
                                'status'     => 'Exito',
                                'cabeceras'  => $cabeceras,
                                'asignacion' => $array_all,
                            );
                            
                            return $helpers->json($data);
                        }
                                        
                        /**
                         * Consulta los procesos creados
                         */
                        $query = "SELECT
                                    tproceso.pkidproceso,
                                    tproceso.procesoactivo,
                                    tabogado.pkidabogado,
                                    tabogado.nombreabogado,
                                    tasignacionpuesto.pkidasignacionpuesto,
                                    tasignacionpuesto.numeroresolucionasignacionpuesto,
                                    tbeneficiario.pkidbeneficiario,
                                    tbeneficiario.identificacionbeneficiario,
                                    tbeneficiario.nombrebeneficiario,
                                    tpuesto.pkidpuesto,
                                    tpuesto.numeropuesto,
                                    tsector.pkidsector,
                                    tsector.nombresector,
                                    tzona.pkidzona,
                                    tzona.nombrezona,
                                    tplaza.pkidplaza,
                                    tplaza.nombreplaza,
                                    (SELECT count(pkidfactura)
                                        FROM tfactura
                                        WHERE
                                            tasignacionpuesto.pkidasignacionpuesto = tfactura.fkidasignacionpuesto
                                            AND tfactura.facturapagada = false
                                            AND tfactura.facturaactivo = true) AS mesessinpagar
                                FROM tproceso
                                    JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                    JOIN tabogado ON tabogado.pkidabogado = tproceso.fkidabogado
                                ORDER BY tproceso.creacionproceso DESC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $procesos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($procesos as $proceso) {
                            
                                $factura =  $em->getRepository('ModeloBundle:Tfactura')->findOneBy(array(
                                    "fkidasignacionpuesto" => $proceso['pkidasignacionpuesto'],
                                    "facturaactivo" => true
                                ));
    
                                $saldoAsignacion = 0;
                                $saldoMultas = 0;
                                $saldoAcuerdo = 0;
                                $saldoDeuda = 0;
                                $saldoDeudaAcuerdo = 0;
                                $saldoporpagar = 0;
    
                                if($factura){

                                    $saldoAsignacion = $factura->getSaldoasignacion();
                                    $saldoMultas = $factura->getSaldomultas();
                                    $saldoAcuerdo =  $factura->getSaldoacuerdo();
                                    $saldoDeuda =  $factura->getSaldodeuda();
                                    $saldoDeudaAcuerdo =  $factura->getSaldodeudaacuerdo();
                                    $saldoporpagar =  $factura->getSaldoporpagar();
                                }
                                $factura = array(
                                    "mesessinpagar"     => $proceso['mesessinpagar'],
                                    "saldoasignacion"   => $saldoAsignacion,
                                    "saldomultas"       => $saldoMultas,
                                    "saldoacuerdo"      => $saldoAcuerdo,
                                    "saldodeuda"        => $saldoDeuda,
                                    "saldodeudaacuerdo" => $saldoDeudaAcuerdo,
                                    "saldoporpagar"     => $saldoporpagar,
                                );
                                
                                $procesosList = array(
                                    "pkidproceso"   => $proceso['pkidproceso'],
                                    "abogado" => array(
                                        "pkidabogado"   => $proceso['pkidabogado'],
                                        "nombreabogado" => $proceso['nombreabogado'],
                                    ),
                                    "asignacionpuesto" => array(
                                        "pkidasignacionpuesto"             => $proceso['pkidasignacionpuesto'],
                                        "numeroresolucionasignacionpuesto" => $proceso['numeroresolucionasignacionpuesto'],
                                    ),
                                    "beneficiario" => array(
                                        "pkidbeneficiario"           => $proceso['pkidbeneficiario'],
                                        "identificacionbeneficiario" => $proceso['identificacionbeneficiario'],
                                        "nombrebeneficiario"         => $proceso['nombrebeneficiario'],
                                    ),
                                    "puesto" => array(
                                        "pkidpuesto"   => $proceso['pkidpuesto'],
                                        "numeropuesto" => $proceso['numeropuesto'],
                                    ),
                                    "sector" => array(
                                        "pkidsector"   => $proceso['pkidsector'],
                                        "nombresector" => $proceso['nombresector'],
                                    ),
                                    "zona" => array(
                                        "pkidzona"   => $proceso['pkidzona'],
                                        "nombrezona" => $proceso['nombrezona'],
                                    ),
                                    "plaza" => array(
                                        "pkidplaza"   => $proceso['pkidplaza'],
                                        "nombreplaza" => $proceso['nombreplaza'],
                                    ),
                                    "procesoactivo" => $proceso['procesoactivo'],
                                    "factura"       => $factura
                                );
                                  
                                array_push($array_all, $procesosList);
                        }

                        $cabeceras = array("Proceso","Abogado","Asignacion de Puesto","Beneficiario","Puesto","Sector","Zona","Plaza","Acuerdo","Proceso Activo/Inactivo");

                        $data = array(
                            'status'    => 'Exito',
                            'cabeceras' => $cabeceras,
                            'proceso'   => $array_all,
                        );

                    } else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos !!',
                        );
                    }

                } else {
                    $data = array(
                        'status' => 'error',
                        'msg'    => 'Acceso no autorizado !!',
                    );
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'msg'    => 'Envie los parametros, por favor !!',
                );
            }

            return $helpers->json($data);

        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $data = array(
                'idusuario'     => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo'        => "Proceso",
                'metodo'        => $trace[0]['function'],
                'mensaje'       => $e->getMessage(),
                'tipoExepcion'  => $trace[0]['class'],
                'pila'          => $e->getTraceAsString(),
                'origen'        => "Web",
            );

            try {

                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            } catch (\Exception $a) {

            }

            throw $e;
        }

    }
}