<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tacuerdo;
use ModeloBundle\Entity\Tproceso;
use ModeloBundle\Entity\Tbeneficiario;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TacuerdoController extends Controller
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
     * Funcion para registrar una acuerdo 
     * recibe los datos en un json llamado json con los datos
     * acuerdoactivo, si la acuerdo  esta activa o no
     * numerocuotas, numero de cuotas en las que se va a dividir la deuda
     * valoracuerdo, valor por el que se hace el acuerdo de pago
     * valorcuotainicial, valor con la que se inicia el pago de la deuda
     * valorcuotamensual, valor que el beneficiario pagara cada mes junto con el pago del proceso
     * numero acuerdo, numero o codigo para identificar el acuerdo
     * fkidproceso, el id del proceso al que pertenece el acuerdo
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newAcuerdoAction(Request $request)
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

                    if (in_array("PERM_ACUERDOS", $permisosDeserializados)) {
                       
                        //fecha y hora actuales en la zona horaria de Colombia
                        $today = new \DateTime('now',new \DateTimeZone('America/Bogota'));
                        
                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        //Recoger datos post
                        $json = $request->get("json", null);
                        $params = json_decode($json);

                        //array error por defecto
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'Acuerdo  no creado!!',
                        );

                        if ($json != null) {
                            //return $helpers->json($json);
                            
                            
                            $acuerdoActivo = (isset($params->acuerdoactivo)) ? $params->acuerdoactivo : true;
                            $numeroCuotas = (isset($params->numerocuotas)) ? $params->numerocuotas : null;
                            $valorAcuerdo = (isset($params->valoracuerdo)) ? $params->valoracuerdo : null;
                            $valorCuotaInicial = (isset($params->valorcuotainicial)) ? $params->valorcuotainicial : null;
                            $valorCuotaMensual = (isset($params->valorcuotamensual)) ? $params->valorcuotamensual : null;
                            $numeroAcuerdo = (isset($params->numeroacuerdo)) ? $params->numeroacuerdo : null;
                            $idProceso = (isset($params->fkidproceso)) ? $params->fkidproceso : null; 
                            
                            if($numeroCuotas != null && $valorAcuerdo !=null && $valorCuotaInicial !=null && $valorCuotaMensual !=null  && $numeroAcuerdo !=null  && $idProceso !=null){
                                $proceso = $em->getRepository('ModeloBundle:Tproceso')->find($idProceso);

                                if($proceso){
                                        
                                        //valida si el numero de acuerdo no se repite.
                                        $numeroAcuerdoDuplicated = $em->getRepository('ModeloBundle:Tacuerdo')->findOneBy(array(
                                            "numeroacuerdo" => $numeroAcuerdo
                                        ));
                                        
                                        if(!$numeroAcuerdoDuplicated){

                                            //Valida si el proceso ya tiene un acuerdo activo
                                            $procesoDuplicated = $em->getRepository('ModeloBundle:Tacuerdo')->findOneBy(array(
                                                "fkidproceso"   => $idProceso,
                                                "acuerdoactivo" => "true"
                                            ));
                                            
                                            if(!$procesoDuplicated){
                                                
                                                $acuerdo = new Tacuerdo();
                                                $acuerdo->setDocumentoacuerdo("sin documento");
                                                $acuerdo->setAcuerdoactivo($acuerdoActivo);
                                                $acuerdo->setNumerocuotas($numeroCuotas);
                                                $acuerdo->setValoracuerdo($valorAcuerdo);
                                                $acuerdo->setValorcuotainicial($valorCuotaInicial);
                                                $acuerdo->setValorcuotamensual($valorCuotaMensual);
                                                $acuerdo->setSaldoacuerdo(0);
                                                $acuerdo->setSaldodeudaacuerdo(0);
                                                $acuerdo->setCuotasincumplidas(0);
                                                $acuerdo->setCreacionacuerdo($today);
                                                $acuerdo->setModificacionacuerdo($today);
                                                $acuerdo->setNumeroacuerdo($numeroAcuerdo);
                                                $acuerdo->setCuotaspagadas(0);
                                                $acuerdo->setFkidproceso($proceso);                           
    
                                                if (isset($_FILES['fichero_usuario'])) {
    
                                                    if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                        
                                                        if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
                
                                                            $em->persist($acuerdo);
                                                            $em->flush();
                                                            
                                                            $idAcuerdo = $acuerdo->getPkidacuerdo();
                
                                                            $dir_subida = '../web/documentos/';
                                                            $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                            $fichero_subido = $dir_subida . basename($idAcuerdo . "_acuerdo_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
                
                                                            if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                                $acuerdo_doc = $em->getRepository('ModeloBundle:Tacuerdo')->findOneBy(array(
                                                                    "pkidacuerdo" => $acuerdo->getPkidacuerdo(),
                                                                ));
                                                                
                                                                $acuerdo_doc->setDocumentoacuerdo($fichero_subido);
                                                                $em->persist($acuerdo_doc);
                                                                $em->flush();
                
                                                                $data = array(
                                                                    'status'  => 'Exito',
                                                                    'msg'     => 'Acuerdo  creado!!',
                                                                    'acuerdo' => $acuerdo_doc,
                                                                );
                
                                                                $datos = array(
                                                                    'idusuario'             => $identity->sub,
                                                                    'nombreusuario'         => $identity->name,
                                                                    'identificacionusuario' => $identity->identificacion,
                                                                    'accion'                => 'insertar',
                                                                    "tabla"                 => 'Tacuerdo',
                                                                    "valoresrelevantes"     => 'idAcuerdo:'.$idAcuerdo,
                                                                    'idelemento'            => $idAcuerdo,
                                                                    'origen'                => 'web'
                                                                );
                            
                                                                $auditoria = $this->get(Auditoria::class);
                                                                $auditoria->auditoria(json_encode($datos));
                
                                                            } else {
                                                                $em->remove($acuerdo);
                                                                $em->flush();
                
                                                                $data = array(
                                                                    'status' => 'Error',
                                                                    'msg'    => 'No se ha podido ingresar el documento pdf, intente nuevamente !!',
                                                                );
                                                            }
                                                        } else {
                                                            $data = array(
                                                                'status' => 'Error',
                                                                'msg'    => 'Solo se aceptan archivos en formato PDF !!',
                                                            );
                                                        }
                                                    } else {
                                                        $data = array(
                                                            'status' => 'Error',
                                                            'msg'    => 'El tamaño del documento debe ser MAX 5MB !!',
                                                        );
                                                    }
                                                }else{
                                                    $em->persist($acuerdo);
                                                    $em->flush();
                            
                                                    $data = array(
                                                        'status'  => 'Exito',
                                                        'msg'     => 'Acuerdo  creado!!',
                                                        'acuerdo' => $acuerdo,
                                                    );
            
                                                    $idAcuerdo = $acuerdo->getPkidacuerdo();
                                                
                                                    //una vez insertados los datos en la acuerdo se realiza el proceso de auditoria
                                                    $datos = array(
                                                        'idusuario'             => $identity->sub,
                                                        'nombreusuario'         => $identity->name,
                                                        'identificacionusuario' => $identity->identificacion,
                                                        'accion'                => 'insertar',
                                                        "tabla"                 => 'Tacuerdo',
                                                        "valoresrelevantes"     => 'idAcuerdo:'.$idAcuerdo,
                                                        'idelemento'            => $idAcuerdo,
                                                        'origen'                => 'web'
                                                    );
                    
                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El proceso ya tiene un acuerdo activo!!'
                                                );
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Numero de acuerdo duplicado!!'
                                            );
                                        }
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'El proceso no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'Algunos de los valores son nulos!!',
                                );
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
                'modulo'        => 'Acuerdo',
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
     * Funcion para modificar una acuerdo
     * recibe los datos en un json llamado json con los datos
     * pkidacuerdo=>obligatorio, id de la acuerdo a editar
     * acuerdoactivo, si la acuerdo  esta activa o no
     * numerocuotas, numero de cuotas en las que se va a dividir la deuda
     * valoracuerdo, valor por el que se hace el acuerdo de pago
     * valorcuotainicial, valor con la que se inicia el pago de la deuda
     * valorcuotamensual, valor que el beneficiario pagara cada mes junto con el pago del proceso
     * numero acuerdo, numero o codigo para identificar el acuerdo
     * fkidproceso, el id del proceso al que pertenece el acuerdo
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editAcuerdoAction(Request $request)
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

                    if (in_array("PERM_ACUERDOS", $permisosDeserializados)) {
                        
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
                            'msg'    => 'Acuerdo no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idAcuerdo = (isset($params->pkidacuerdo)) ? $params->pkidacuerdo : null;

                            if($idAcuerdo != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $db = $em->getConnection();

                                $acuerdo = $em->getRepository('ModeloBundle:Tacuerdo')->find($idAcuerdo);

                                if($acuerdo){

                                    $tarifa = false;

                                    $idProceso = (isset($params->fkidproceso)) ? $params->fkidproceso : $acuerdo->getFkidproceso();
                                    $proceso = $em->getRepository('ModeloBundle:Tproceso')->find($idProceso);

                                    $acuerdoActivo = (isset($params->acuerdoactivo)) ? $params->acuerdoactivo : false;

                                    if(isset($params->numerocuotas)){
                                        $acuerdo->setNumerocuotas($params->numerocuotas);
                                    }

                                    if(isset($params->valoracuerdo)){
                                        $acuerdo->setValoracuerdo($params->valoracuerdo);
                                    }

                                    if(isset($params->valorcuotainicial)){
                                        $acuerdo->setValorcuotainicial($params->valorcuotainicial);
                                    }

                                    if(isset($params->valorcuotamensual)){
                                       $acuerdo->setValorcuotamensual($params->valorcuotamensual);
                                    }    

                                    if(isset($params->numeroacuerdo)){

                                        if($acuerdo->getNumeroacuerdo() != $params->numeroacuerdo){
                                            $numeroAcuerdo = $params->numeroacuerdo;

                                            //Valida si existe un registro con el mismo numero de acuerdo que sea diferente a la que se esta editando
                                            $numeroAcuerdoDuplicated = $em->getRepository('ModeloBundle:Tacuerdo')->createQueryBuilder('a')
                                                ->where('a.numeroacuerdo = :numeroacuerdo')
                                                ->andwhere('a.pkidacuerdo != :pkidacuerdo')
                                                ->setParameter('numeroacuerdo', $numeroAcuerdo)
                                                ->setParameter('pkidacuerdo', $idAcuerdo)
                                                ->getQuery()
                                                ->getResult();
    
                                            if(!$numeroAcuerdoDuplicated){
                                                $acuerdo->setNumeroacuerdo($numeroAcuerdo);
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Numero de acuerdo duplicado!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }
                                   
                                    //validacion de proceso 
                                    if($proceso){
                                      
                                        if($acuerdoActivo == true){
                                            //Valida si el proceso tiene una acuerdo activo que sea diferente a la que se esta editando
                                            $query = $em->getRepository('ModeloBundle:Tacuerdo')->createQueryBuilder('a')
                                                ->where('a.fkidproceso = :fkidproceso ')
                                                ->andwhere('a.pkidacuerdo != :pkidacuerdo')
                                                ->andwhere('a.acuerdoactivo = :acuerdoactivo')
                                                ->setParameter('fkidproceso',$idProceso)
                                                ->setParameter('pkidacuerdo', $idAcuerdo)
                                                ->setParameter('acuerdoactivo', true)
                                                ->getQuery();
                                        
                                            $procesoDuplicated = $query->getResult();

                                            if($procesoDuplicated){
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El proceso ya tiene un acuerdo activo!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }
                                    }else{
                                        $data = array(
                                            'status'=> 'Error',
                                            'msg'   => 'La proceso no existe!!'
                                        );
                                        return $helpers->json($data);
                                    }
                                
                                    if(isset($params->fkidproceso)){ 
                                        $acuerdo->setFkidproceso($proceso);
                                    }

                                    if(isset($params->acuerdoactivo)){
                                        $acuerdo->setAcuerdoactivo($acuerdoActivo);
                                    }

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idAcuerdo . "_acuerdo_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                   $acuerdo->setDocumentoacuerdo($fichero_subido);
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
                                    }
                
                                    $em->persist($acuerdo);
                                    $em->flush();

                                    $data = array(
                                        'status'           => 'Exito',
                                        'msg'              => 'Acuerdo  actualizada!!',
                                        'acuerdo' => $acuerdo,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tacuerdo',
                                        "valoresrelevantes"     => 'idAcuerdo:'.$idAcuerdo.',saldoDeuda:'.$acuerdo->getSaldodeudaacuerdo(),
                                        'idelemento'            => $idAcuerdo,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos)); 
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'La Acuerdo  no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de la Acuerdo  a editar es nulo!!'
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
                'modulo'        => "Acuerdo",
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
     * Funcion para mostrar todos los procesos con asignaciones asociadas
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryAcuerdoAction(Request $request)
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
                            $em = $this->getDoctrine()->getManager();
                            $db = $em->getConnection();
                            
                            $query = "SELECT 
                                    tacuerdo.pkidacuerdo, 
                                    tacuerdo.documentoacuerdo, 
                                    tacuerdo.acuerdoactivo, 
                                    tacuerdo.numerocuotas, 
                                    tacuerdo.valoracuerdo, 
                                    tacuerdo.valorcuotainicial, 
                                    tacuerdo.valorcuotamensual, 
                                    tacuerdo.numeroacuerdo, 
                                    tacuerdo.saldoacuerdo, 
                                    tacuerdo.saldodeudaacuerdo, 
                                    tacuerdo.cuotasincumplidas, 
                                    tacuerdo.cuotaspagadas, 
                                    tacuerdo.fechapagototal, 
                                    tproceso.pkidproceso, 
                                    tproceso.procesoactivo, 
                                    tabogado.pkidabogado, 
                                    tabogado.nombreabogado, 
                                    tasignacionpuesto.pkidasignacionpuesto,
                                    tasignacionpuesto.numeroresolucionasignacionpuesto,
                                    tasignacionpuesto.asignacionpuestoactivo,
                                    tbeneficiario.pkidbeneficiario, 
                                    tbeneficiario.nombrebeneficiario, 
                                    tbeneficiario.identificacionbeneficiario,
                                    tbeneficiario.beneficiarioactivo,
                                    tpuesto.pkidpuesto, 
                                    tpuesto.numeropuesto, 
                                    tpuesto.puestoactivo,
                                    tsector.pkidsector, 
                                    tsector.nombresector, 
                                    tzona.pkidzona, 
                                    tzona.nombrezona, 
                                    tplaza.pkidplaza, 
                                    tplaza.nombreplaza
                                FROM 
                                    tacuerdo 
                                    RIGHT JOIN tproceso ON tproceso.pkidproceso = tacuerdo.fkidproceso
                                    JOIN tabogado ON tabogado.pkidabogado = tproceso.fkidabogado
                                    JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto 
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza where acuerdoactivo=true
                                ORDER BY tacuerdo.numeroacuerdo ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $acuerdos = $stmt->fetchAll();
    
                            $data = array(
                                'status'    => 'Exito',
                                'acuerdo' => $acuerdos,
                            );
    
                        }else {
                            $data = array(
                                'status' => 'error',
                                'msg'    => 'El usuario no tiene permisos genericos !!',
                            );
                        }
                        return $helpers->json($data);
                        }

                    if (in_array("PERM_ACUERDOS", $permisosDeserializados)) {                
                                            
                        //Consulta para traer los datos de la acuerdo, la proceso y el usuario a los que se encuentra asignada.
                        $query = "SELECT 
                                    tacuerdo.pkidacuerdo, 
                                    tacuerdo.documentoacuerdo, 
                                    tacuerdo.acuerdoactivo, 
                                    tacuerdo.numerocuotas, 
                                    tacuerdo.valoracuerdo, 
                                    tacuerdo.valorcuotainicial, 
                                    tacuerdo.valorcuotamensual, 
                                    tacuerdo.numeroacuerdo, 
                                    tacuerdo.saldoacuerdo, 
                                    tacuerdo.saldodeudaacuerdo, 
                                    tacuerdo.cuotasincumplidas, 
                                    tacuerdo.cuotaspagadas, 
                                    tacuerdo.fechapagototal, 
                                    tproceso.pkidproceso, 
                                    tproceso.procesoactivo, 
                                    tabogado.pkidabogado, 
                                    tabogado.nombreabogado, 
                                    tasignacionpuesto.pkidasignacionpuesto,
                                    tasignacionpuesto.numeroresolucionasignacionpuesto,
                                    tasignacionpuesto.asignacionpuestoactivo,
                                    tbeneficiario.pkidbeneficiario, 
                                    tbeneficiario.nombrebeneficiario, 
                                    tbeneficiario.identificacionbeneficiario,
                                    tbeneficiario.beneficiarioactivo,
                                    tpuesto.pkidpuesto, 
                                    tpuesto.numeropuesto, 
                                    tpuesto.puestoactivo,
                                    tsector.pkidsector, 
                                    tsector.nombresector, 
                                    tzona.pkidzona, 
                                    tzona.nombrezona, 
                                    tplaza.pkidplaza, 
                                    tplaza.nombreplaza
                                FROM 
                                    tacuerdo 
                                    RIGHT JOIN tproceso ON tproceso.pkidproceso = tacuerdo.fkidproceso
                                    JOIN tabogado ON tabogado.pkidabogado = tproceso.fkidabogado
                                    JOIN tasignacionpuesto ON tasignacionpuesto.pkidasignacionpuesto = tproceso.fkidasignacionpuesto
                                    JOIN tpuesto ON tpuesto.pkidpuesto = tasignacionpuesto.fkidpuesto 
                                    JOIN tbeneficiario ON tbeneficiario.pkidbeneficiario = tasignacionpuesto.fkidbeneficiario
                                    JOIN tsector ON tsector.pkidsector = tpuesto.fkidsector
                                    JOIN tzona ON tzona.pkidzona = tsector.fkidzona
                                    JOIN tplaza ON tplaza.pkidplaza = tzona.fkidplaza
                                ORDER BY tacuerdo.numeroacuerdo ASC";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $acuerdos = $stmt->fetchAll();

                        $array_all = array();

                        foreach ($acuerdos as $acuerdo) {
                            $acuerdosList = array(
                                "pkidacuerdo"       => $acuerdo['pkidacuerdo'],
                                "documentoacuerdo"  => $acuerdo['documentoacuerdo'], 
                                "acuerdoactivo"     => $acuerdo['acuerdoactivo'], 
                                "numerocuotas"      => $acuerdo['numerocuotas'], 
                                "valoracuerdo"      => $acuerdo['valoracuerdo'], 
                                "valorcuotainicial" => $acuerdo['valorcuotainicial'], 
                                "valorcuotamensual" => $acuerdo['valorcuotamensual'], 
                                "numeroacuerdo"     => $acuerdo['numeroacuerdo'], 
                                "saldoacuerdo"      => $acuerdo['saldoacuerdo'], 
                                "saldodeudaacuerdo" => $acuerdo['saldodeudaacuerdo'], 
                                "cuotasincumplidas" => $acuerdo['cuotasincumplidas'], 
                                "cuotaspagadas"     => $acuerdo['cuotaspagadas'], 
                                "fechapagototal"    => $acuerdo['fechapagototal'], 
                                "proceso"           => array(
                                                        "pkidproceso"   => $acuerdo['pkidproceso'],
                                                        "procesoactivo" => $acuerdo['procesoactivo'],
                                                    ),
                                "abogado"           => array(
                                                        "pkidabogado"   => $acuerdo['pkidabogado'],
                                                        "nombreabogado" => $acuerdo['nombreabogado'],
                                                    ),
                                "asignacionpuesto"  => array(
                                                        "pkidasignacionpuesto"             => $acuerdo['pkidasignacionpuesto'],
                                                        "numeroresolucionasignacionpuesto" => $acuerdo['numeroresolucionasignacionpuesto'],
                                                        "asignacionpuestoactivo"           => $acuerdo['asignacionpuestoactivo'],
                                                    ),
                                "beneficiario"      => array(
                                                        "pkidbeneficiario"           => $acuerdo['pkidbeneficiario'],
                                                        "identificacionbeneficiario" => $acuerdo['identificacionbeneficiario'],
                                                        "nombrebeneficiario"         => $acuerdo['nombrebeneficiario'],
                                                        "beneficiarioactivo"         => $acuerdo['beneficiarioactivo'],
                                                    ),
                                "puesto"            => array(
                                                        "pkidpuesto"   => $acuerdo['pkidpuesto'],
                                                        "numeropuesto" => $acuerdo['numeropuesto'],
                                                        "puestoactivo" => $acuerdo['puestoactivo'],
                                                    ),
                                "sector"            => array(
                                                        "pkidsector"   => $acuerdo['pkidsector'],
                                                        "nombresector" => $acuerdo['nombresector'],
                                                    ),
                                "zona"              => array(
                                                        "pkidzona"   => $acuerdo['pkidzona'],
                                                        "nombrezona" => $acuerdo['nombrezona'],
                                                    ),
                                "plaza"             => array(
                                                        "pkidplaza"   => $acuerdo['pkidplaza'],
                                                        "nombreplaza" => $acuerdo['nombreplaza'],
                                                    ),
                                "acuerdoactivo" => $acuerdo['procesoactivo']
                            );
                            array_push($array_all, $acuerdosList);
                        }

                        $cabeceras = array("Acuerdo","Proceso","Abogado","Asignacion de Puesto","Beneficiario","Puesto","Sector","Zona","Plaza","Acuerdo Activo/Inactivo");

                        $data = array(
                            'status'           => 'Success',
                            'cabeceras'        => $cabeceras,
                            'acuerdo' => $array_all,
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
                'modulo'        => "Acuerdo",
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