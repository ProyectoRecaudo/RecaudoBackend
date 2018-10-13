<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tincremento;
use ModeloBundle\Entity\Ttarifaanimal;
use ModeloBundle\Entity\Ttarifainteres;
use ModeloBundle\Entity\Ttarifapesaje;
use ModeloBundle\Entity\Ttarifapuestoeventual;
use ModeloBundle\Entity\Ttarifavehiculo;
use ModeloBundle\Entity\Ttarifaparqueadero;
use ModeloBundle\Entity\Tasignacionpuesto;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TincrementoController extends Controller
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
     * Funcion para registrar un incremento
     * recibe los datos en un json llamado json con los datos
     * valorincremento, valor de el incremento que se va a asignar
     * resolucionincremento, el numero de la resolucion que oficializa el incremento
     * incrementoactivo, si la incremento esta activo o no
     * fkidplaza, el id de la plaza a la que pertenece
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function newIncrementoAction(Request $request)
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

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                    //if (in_array("PERM_INCREMENTO_PORCENTUALES", $permisosDeserializados)) {
                       
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
                            'msg'    => 'Incremento Porcentual no creado!!',
                        );

                        if ($json != null) {

                            $valorIncrementoInt = (isset($params->valorincremento)) ? $params->valorincremento : null;
                            $resolucionIncremento = (isset($params->resolucionincremento)) ? $params->resolucionincremento : null;
                            $documentoresolucionIncremento = (isset($params->documentoresolucionincremento)) ? $params->documentoresolucionincremento : null;
                            $incrementoActivo = (isset($params->incrementoactivo)) ? $params->incrementoactivo : true;
                            $idPlaza = (isset($params->fkidplaza)) ? $params->fkidplaza : null; 
                            
                            if($idPlaza != null && $valorIncrementoInt !=null && $resolucionIncremento !=null){
                                $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);

                                if($plaza){
                                    $incrementoDuplicated = $em->getRepository('ModeloBundle:Tincremento')->findOneBy(array(
                                        "resolucionincremento" => $resolucionIncremento
                                    ));

                                    if(!$incrementoDuplicated){

                                        //verifica que no exista un incremento porcentual activo en la misma plaza
                                        $incrementActivoDuplicated = $em->getRepository('ModeloBundle:Tincremento')->findOneBy(array(
                                            "incrementoactivo" => true,
                                            "fkidplaza"        => $idPlaza
                                        ));

                                        if(!$incrementActivoDuplicated || $incrementoActivo == false){
                                        
                                            if($valorIncrementoInt >= 0 && $valorIncrementoInt <= 100){
                                                
                                                $valorIncremento = $valorIncrementoInt/100;

                                                $incremento = new Tincremento();
                                                $incremento->setValorincremento($valorIncrementoInt); 
                                                $incremento->setResolucionincremento($resolucionIncremento); 
                                                $incremento->setDocumentoresolucionincremento("sin documento");
                                                $incremento->setIncrementoactivo($incrementoActivo);
                                                $incremento->setCreacionincremento($today);
                                                $incremento->setModificacionincremento($today);
                                                $incremento->setFkidplaza($plaza);
    
                                                if (isset($_FILES['fichero_usuario'])) {
    
                                                    if ($_FILES['fichero_usuario']['size'] <= 5242880) {
                                                                                                        
                                                        if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
                
                                                            $em->persist($incremento);
                                                            $em->flush();
                                                            
                                                            $idIncremento = $incremento->getPkidincremento();
                
                                                            $dir_subida = '../web/documentos/';
                                                            $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                            $fichero_subido = $dir_subida . basename($idIncremento . "_incremento_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
                
                                                            if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                                $incremento_doc = $em->getRepository('ModeloBundle:Tincremento')->findOneBy(array(
                                                                    "pkidincremento" => $incremento->getPkidincremento(),
                                                                ));
                                                                
                                                                $incremento_doc->setDocumentoresolucionincremento($fichero_subido);
                                                                $em->persist($incremento_doc);
                                                                $em->flush();
                
                                                                $data = array(
                                                                    'status'     => 'Exito',
                                                                    'msg'        => 'Incremento Porcentual creado !!',
                                                                    'incremento' => $incremento_doc,
                                                                );
                
                                                                $datos = array(
                                                                    'idusuario'             => $identity->sub,
                                                                    'nombreusuario'         => $identity->name,
                                                                    'identificacionusuario' => $identity->identificacion,
                                                                    'accion'                => 'insertar',
                                                                    "tabla"                 => 'Tincremento',
                                                                    "valoresrelevantes"     => 'idIncremento:'.$idIncremento.',valorIncremento:'.$valorIncremento,
                                                                    'idelemento'            => $idIncremento,
                                                                    'origen'                => 'web'
                                                                );
                            
                                                                $auditoria = $this->get(Auditoria::class);
                                                                $auditoria->auditoria(json_encode($datos));
                
                                                            } else {
                                                                $em->remove($incremento);
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
                                                    $em->persist($incremento);
                                                    $em->flush();
                            
                                                    $data = array(
                                                        'status'     => 'Exito',
                                                        'msg'        => 'Incremento Porcentual creado!!',
                                                        'incremento' => $incremento,
                                                    );
            
                                                    $idIncremento = $incremento->getPkidincremento();
                                                
                                                    //una vez insertados los datos en la incremento se realiza el proceso de auditoria
                                                    $datos = array(
                                                        'idusuario'             => $identity->sub,
                                                        'nombreusuario'         => $identity->name,
                                                        'identificacionusuario' => $identity->identificacion,
                                                        'accion'                => 'insertar',
                                                        "tabla"                 => 'Tincremento',
                                                        "valoresrelevantes"     => 'idIncremento:'.$idIncremento.',valorIncremento:'.$valorIncremento,
                                                        'idelemento'            => $idIncremento,
                                                        'origen'                => 'web'
                                                    );
                    
                                                    $auditoria = $this->get(Auditoria::class);
                                                    $auditoria->auditoria(json_encode($datos));
                                                }
                                                
                                                //aumentar el incremento porcentual en cada una de las tarifas de la plaza
                                                if($incrementoActivo == true){
                                                
                                                    //Modificacion de tarifas activas en Ttarifaanimal
                                                    $tarifasAnimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->findBy(array(
                                                        "tarifaanimalactivo" => true,
                                                        "fkidplaza"          => $idPlaza
                                                    ));
                                                    
                                                    if($tarifasAnimal){
                                                        
                                                        foreach ($tarifasAnimal as $tarifaAnimal) {
                                       
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaAnimal = $tarifaAnimal->getValortarifaanimal() + ($tarifaAnimal->getValortarifaanimal()*$valorIncremento);
            
                                                            $tarifaAnimal->setValortarifaanimal($valorTarifaAnimal);
                                                            $tarifaAnimal->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaAnimal->setModificaciontarifaanimal($today);
    
                                                            $em->persist($tarifaAnimal);
                                                            $em->flush();                 
                                                        }
                                                    }
                                                    
                                                    
                                                    //Modificacion de tarifas activas en Ttarifainteres
                                                    $tarifasInteres = $em->getRepository('ModeloBundle:Ttarifainteres')->findBy(array(
                                                        "tarifainteresactivo" => true,
                                                        "fkidplaza"           => $idPlaza
                                                    ));
                                                    
                                                    if($tarifasInteres){
                                                       
                                                        foreach ($tarifasInteres as $tarifaInteres) {
            
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaInteres = $tarifaInteres->getValortarifainteres() + ($tarifaInteres->getValortarifainteres()*$valorIncremento);
            
                                                            $tarifaInteres->setValortarifainteres($valorTarifaInteres);
                                                            $tarifaInteres->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaInteres->setModificaciontarifainteres($today);

                                                            $em->persist($tarifaInteres);
                                                            $em->flush();
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifapesaje
                                                    $tarifasPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->findBy(array(
                                                        "tarifapesajeactivo" => true,
                                                        "fkidplaza"          => $idPlaza
                                                    ));
                                                    
                                                    if($tarifasPesaje){
                                                        
                                                        foreach ($tarifasPesaje as $tarifaPesaje) {
            
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaPesaje = $tarifaPesaje->getValortarifapesaje() + ($tarifaPesaje->getValortarifapesaje()*$valorIncremento);
            
                                                            $tarifaPesaje->setValortarifapesaje($valorTarifaPesaje);
                                                            $tarifaPesaje->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaPesaje->setModificaciontarifapesaje($today);

                                                            $em->persist($tarifaPesaje);
                                                            $em->flush();
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifapuestoeventual
                                                    $tarifasPuestoEventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->findBy(array(
                                                        "tarifapuestoeventualactivo" => true,
                                                        "fkidplaza"                 => $idPlaza
                                                    ));

                                                    if($tarifasPuestoEventual){
                                                        
                                                        foreach ($tarifasPuestoEventual as $tarifaPuestoEventual) {
            
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaPuestoEventual = $tarifaPuestoEventual->getValortarifapuestoeventual() + ($tarifaPuestoEventual->getValortarifapuestoeventual()*$valorIncremento);
            
                                                            $tarifaPuestoEventual->setValortarifapuestoeventual($valorTarifaPuestoEventual);
                                                            $tarifaPuestoEventual->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaPuestoEventual->setModificaciontarifapuestoeventual($today);

                                                            $em->persist($tarifaPuestoEventual);
                                                            $em->flush();
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifavehiculo
                                                    $tarifasVehiculo = $em->getRepository('ModeloBundle:Ttarifavehiculo')->findBy(array(
                                                        "tarifavehiculoactivo" => true,
                                                        "fkidplaza"            => $idPlaza
                                                    ));

                                                    if($tarifasVehiculo){
                                                       
                                                        foreach ($tarifasVehiculo as $tarifaVehiculo) {
        
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaVehiculo = $tarifaVehiculo->getValortarifavehiculo() + ($tarifaVehiculo->getValortarifavehiculo()*$valorIncremento);
            
                                                            $tarifaVehiculo->setValortarifavehiculo($valorTarifaVehiculo);
                                                            $tarifaVehiculo->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaVehiculo->setModificaciontarifavehiculo($today);
    
                                                            $em->persist($tarifaVehiculo);
                                                            $em->flush();
                                                        }
                                                    }

                                                    //Modificacion de tarifas activas en Ttarifaparqueadero
                                                    $tarifasParqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findBy(array(
                                                        "tarifaparqueaderoactivo" => true,
                                                        "fkidplaza"               => $idPlaza
                                                    ));
                                                    
                                                    if($tarifasParqueadero){
                                                        
                                                        foreach ($tarifasParqueadero as $tarifaParqueadero) {
            
                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaParqueadero = $tarifaParqueadero->getValortarifaparqueadero() + ($tarifaParqueadero->getValortarifaparqueadero() * $valorIncremento);

                                                            $tarifaParqueadero->setValortarifaparqueadero($valorTarifaParqueadero);
                                                            $tarifaParqueadero->setValorincrementoporcentual($valorIncremento);
                                                            $tarifaParqueadero->setModificaciontarifaparqueadero($today);

                                                            $em->persist($tarifaParqueadero);
                                                            $em->flush();
                                                        }
                                                    }

                                                    //Modificacion de tarifas activas en Ttarifapuesto
                                                    $asignaciones = $em->getRepository('ModeloBundle:Tasignacionpuesto')->createQueryBuilder('a')
                                                        ->join('a.fkidpuesto','p')
                                                        ->join('p.fkidsector','s')
                                                        ->join('s.fkidzona','z')
                                                        ->where('a.asignacionpuestoactivo = :asignacionpuestoactivo')
                                                        ->andwhere('z.fkidplaza = :fkidplaza')
                                                        ->setParameter('asignacionpuestoactivo', true)
                                                        ->setParameter('fkidplaza', $idPlaza)
                                                        ->getQuery()
                                                        ->getResult();
                                                    
                                                    if($asignaciones){
                                                       
                                                        foreach ($asignaciones as $asignacion) {

                                                            //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                            $valorTarifaPuesto = $asignacion->getValortarifapuesto() + ($asignacion->getValortarifapuesto() * $valorIncremento);

                                                            //al cambiar la tarifa se crea una nueva asignacion desactivada con los datos anteriores
                                                            $asignacionNew = new Tasignacionpuesto();
                                                            $asignacionNew->setNumeroresolucionasignacionpuesto($asignacion->getNumeroresolucionasignacionpuesto());
                                                            $asignacionNew->setRutaresolucionasignacionpuesto($asignacion->getRutaresolucionasignacionpuesto());
                                                            $asignacionNew->setEstadoasignacionpuesto($asignacion->getEstadoasignacionpuesto()); 
                                                            $asignacionNew->setAsignacionPuestoactivo(false);
                                                            $asignacionNew->setValortarifapuesto($asignacion->getValortarifapuesto());
                                                            $asignacionNew->setValorincrementoporcentual($asignacion->getValorincrementoporcentual());
                                                            $asignacionNew->setSaldodeuda($asignacion->getSaldodeuda()); 
                                                            $asignacionNew->setSaldo($asignacion->getSaldo()); 
                                                            $asignacionNew->setCreacionasignacionpuesto($asignacion->getCreacionasignacionpuesto());
                                                            $asignacionNew->setModificacionasignacionpuesto($today);
                                                            $asignacionNew->setFkidbeneficiario($asignacion->getFkidbeneficiario());
                                                            $asignacionNew->setFkidpuesto($asignacion->getFkidpuesto());
                
                                                            $asignacion->setValortarifapuesto($valorTarifaPuesto);
                                                            $asignacion->setValorincrementoporcentual($valorIncremento);

                                                            $em->persist($asignacionNew);
                                                            $em->flush();
                                                        }
                                                    } 
                                                }                             
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'El valor de incremento debe estar entre 0 y 100!!'
                                                );
                                            }   
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Ya existe un incremento porcentual activo en la plaza!!'
                                            );
                                        }
                                    }else{
                                        $data = array(
                                            'status' => 'error',
                                            'msg'    => 'resolucion duplicada!!'
                                        );
                                    }
                                }else{
                                    $data = array(
                                        'status'=> 'error',
                                        'msg'   => 'La Plaza no existe!!'
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
                'modulo'        => 'Incremento',
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
     * Funcion para modificar una incremento
     * recibe los datos en un json llamado json con los datos
     * pkidincremento=>obligatorio, id de la incremento a editar
     * valorincremento, valor de el incremento que se va a asignar
     * resolucionincremento, el numero de la resolucion que oficializa el incremento
     * incrementoactivo, si el incremento esta activo o no,
     * Al desactivar un incremento se debe preguntar si tambien se 
     * deben anular los incrementos en las tarifas, al ser asi se recibe 
     * un paramentro adicional llamado tarifa, que debe ser true 
     * fkidplaza, el id de la plaza a la que pertenece
     * recibe un documento con el nombre fichero_usuario, se puede omitir. 
     * tambien recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function editincrementoAction(Request $request)
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

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                    //if (in_array("PERM_INCREMENTO_PORCENTUALES", $permisosDeserializados)) {
                        
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
                            'msg'    => 'Incremento Porcentual no actualizado!!',
                        );

                        if ($json != null) {
                            
                            $idIncremento = (isset($params->pkidincremento)) ? $params->pkidincremento : null;

                            if($idIncremento != null){
                            
                                $em = $this->getDoctrine()->getManager();
                                $incremento = $em->getRepository('ModeloBundle:Tincremento')->find($idIncremento);
                    
                                if($incremento){
                                    
                                    $valorIncrementoOld = $incremento->getValorincremento()/100;
                                    $valorIncremento = (isset($params->valorincremento)) ? $params->valorincremento/100 : $incremento->getValorincremento()/100;
                                    
                                    if(isset($params->fkidplaza)){
                                        $idPlaza = $params->fkidplaza;
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($idPlaza);
                                                                                
                                        if($plaza){
                                            $incremento->setFkidplaza($plaza);
                                        }else{
                                            $data = array(
                                                'status'=> 'Error',
                                                'msg'   => 'La plaza no existe!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }else{
                                        $plaza = $em->getRepository('ModeloBundle:Tplaza')->find($incremento->getFkidplaza());
                                        $idPlaza = $plaza->getPkidplaza();
                                    }

                                    //Consulta para modificacion de tarifas activas en Ttarifaanimal
                                    $tarifasAnimal = $em->getRepository('ModeloBundle:Ttarifaanimal')->findBy(array(
                                        "tarifaanimalactivo" => true,
                                        "fkidplaza"          => $idPlaza
                                    ));

                                    //Consulta para modificacion de tarifas activas en Ttarifainteres
                                    $tarifasInteres = $em->getRepository('ModeloBundle:Ttarifainteres')->findBy(array(
                                        "tarifainteresactivo" => true,
                                        "fkidplaza"           => $idPlaza
                                    ));

                                    //Consulta para modificacion de tarifas activas en Ttarifapesaje
                                    $tarifasPesaje = $em->getRepository('ModeloBundle:Ttarifapesaje')->findBy(array(
                                        "tarifapesajeactivo" => true,
                                        "fkidplaza"          => $idPlaza
                                    ));

                                    //Consulta para modificacion de tarifas activas en Ttarifapuestoeventual
                                    $tarifasPuestoEventual = $em->getRepository('ModeloBundle:Ttarifapuestoeventual')->findBy(array(
                                        "tarifapuestoeventualactivo" => true,
                                        "fkidplaza"                 => $idPlaza
                                    ));

                                    //consulta para modificacion de tarifas activas en Ttarifavehiculo
                                    $tarifasVehiculo = $em->getRepository('ModeloBundle:Ttarifavehiculo')->findBy(array(
                                        "tarifavehiculoactivo" => true,
                                        "fkidplaza"            => $idPlaza
                                    ));

                                    //Consulta para modificacion de tarifas activas en Ttarifaparqueadero                                    
                                    $tarifasParqueadero = $em->getRepository('ModeloBundle:Ttarifaparqueadero')->findBy(array(
                                        "tarifaparqueaderoactivo" => true,
                                        "fkidplaza"               => $idPlaza
                                    ));
                                    
                                    //Consulta para modificacion de tarifas activas en Tasignacionpuesto
                                    $asignaciones = $em->getRepository('ModeloBundle:Tasignacionpuesto')->createQueryBuilder('a')
                                                        ->join('a.fkidpuesto','p')
                                                        ->join('p.fkidsector','s')
                                                        ->join('s.fkidzona','z')
                                                        ->where('a.asignacionpuestoactivo = :asignacionpuestoactivo')
                                                        ->andwhere('z.fkidplaza = :fkidplaza')
                                                        ->setParameter('asignacionpuestoactivo', true)
                                                        ->setParameter('fkidplaza', $idPlaza)
                                                        ->getQuery()
                                                        ->getResult();
            

                                    if(isset($params->incrementoactivo)){

                                        /**
                                         * Al desactivar un incremento se debe preguntar si tambien se deben anular los incrementos en las
                                         * tarifas, al ser asi se recibe un paramentro adicional en json llamado tarifa, que debe ser true 
                                         */
                                        if($incremento->getIncrementoactivo() == true && $params->incrementoactivo == false){
                                            
                                            $tarifa = $request->get('tarifa', null); 

                                            if($tarifa != null && $tarifa == true){
                                                                                            
                                                //Modificacion de tarifas activas en Ttarifaanimal
                                                if($tarifasAnimal){
                                                    foreach ($tarifasAnimal as $tarifaAnimal) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaAnimal->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaAnimal = $tarifaAnimal->getValortarifaanimal()/($valorIncrementoOld+1);

                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaAnimal->setValortarifaanimal($valorTarifaAnimal);
                                                            $tarifaAnimal->setValorincrementoporcentual(0);
                                                            $tarifaAnimal->setModificaciontarifaanimal($today);

                                                            $em->persist($tarifaAnimal);
                                                            $em->flush();
                                                        }
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifainteres
                                                if($tarifasInteres){
                                                    foreach ($tarifasInteres as $tarifaInteres) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaInteres->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaInteres = $tarifaInteres->getValortarifainteres()/($valorIncrementoOld+1);
                                                    
                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaInteres->setValortarifainteres($valorTarifaInteres);
                                                            $tarifaInteres->setValorincrementoporcentual(0);
                                                            $tarifaInteres->setModificaciontarifainteres($today);

                                                            $em->persist($tarifaInteres);
                                                            $em->flush();
                                                        }
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifapesaje
                                                if($tarifasPesaje){
                                                    foreach ($tarifasPesaje as $tarifaPesaje) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaPesaje->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaPesaje = $tarifaPesaje->getValortarifapesaje()/($valorIncrementoOld+1);
                                                            
                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaPesaje->setValortarifapesaje($valorTarifaPesaje);
                                                            $tarifaPesaje->setValorincrementoporcentual(0);
                                                            $tarifaPesaje->setModificaciontarifapesaje($today);

                                                            $em->persist($tarifaPesaje);
                                                            $em->flush();
                                                        }
                                                    }
                                                }
                                            
                                                //Modificacion de tarifas activas en Ttarifapuestoeventual
                                                if($tarifasPuestoEventual){
                                                    foreach ($tarifasPuestoEventual as $tarifaPuestoEventual) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaPuestoEventual->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaPuestoEventual = $tarifaPuestoEventual->getValortarifapuestoeventual()/($valorIncrementoOld+1);
                                                            
                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaPuestoEventual->setValortarifapuestoeventual($valorTarifaPuestoEventual);
                                                            $tarifaPuestoEventual->setValorincrementoporcentual(0);
                                                            $tarifaPuestoEventual->setModificaciontarifapuestoeventual($today);

                                                            $em->persist($tarifaPuestoEventual);
                                                            $em->flush();
                                                        }
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifavehiculo
                                                if($tarifasVehiculo){
                                                    foreach ($tarifasVehiculo as $tarifaVehiculo) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaVehiculo->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaVehiculo = $tarifaVehiculo->getValortarifavehiculo()/($valorIncrementoOld+1);
                                                            
                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaVehiculo->setValortarifavehiculo($valorTarifaVehiculo);
                                                            $tarifaVehiculo->setValorincrementoporcentual(0);
                                                            $tarifaVehiculo->setModificaciontarifavehiculo($today);

                                                            $em->persist($tarifaVehiculo);
                                                            $em->flush();
                                                        }
                                                    }
                                                }

                                                //Modificacion de tarifas activas en Ttarifaparqueadero 
                                                if($tarifasParqueadero){
                                                    foreach ($tarifasParqueadero as $tarifaParqueadero) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($tarifaParqueadero->getValorincrementoporcentual() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaParqueadero = $tarifaParqueadero->getValortarifaparqueadero()/($valorIncrementoOld+1);
                                                            
                                                            //guarda el valor sin incremento y coloca el incremento en 0
                                                            $tarifaParqueadero->setValortarifaparqueadero($valorTarifaParqueadero);
                                                            $tarifaParqueadero->setValorincrementoporcentual(0);
                                                            $tarifaParqueadero->setModificaciontarifaparqueadero($today);

                                                            $em->persist($tarifaParqueadero);
                                                            $em->flush();
                                                        }
                                                    }
                                                }

                                                //Modificacion de tarifas activas en Tasignacionpuesto
                                                if($asignaciones){
                                                    foreach ($asignaciones as $asignacion) {

                                                        /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                         * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                         */
                                                        if($asignacion->getValortarifapuesto() != 0){
                                                            //obtiene el valor de la tarifa anterior a la suma del incremento
                                                            $valorTarifaPuesto = $asignacion->getValortarifapuesto()/($valorIncrementoOld+1);
                                                        
                                                            /**
                                                             * guarda el valor sin incremento y coloca el incremento en 0
                                                             * al cambiar la tarifa se crea una nueva asignacion desactivada con los datos anteriores
                                                             */
                                                            $asignacionNew = new Tasignacionpuesto();
                                                            $asignacionNew->setNumeroresolucionasignacionpuesto($asignacion->getNumeroresolucionasignacionpuesto());
                                                            $asignacionNew->setRutaresolucionasignacionpuesto($asignacion->getRutaresolucionasignacionpuesto());
                                                            $asignacionNew->setEstadoasignacionpuesto($asignacion->getEstadoasignacionpuesto()); 
                                                            $asignacionNew->setAsignacionPuestoactivo(false);
                                                            $asignacionNew->setValortarifapuesto($asignacion->getValortarifapuesto());
                                                            $asignacionNew->setValorincrementoporcentual($asignacion->getValorincrementoporcentual());
                                                            $asignacionNew->setSaldodeuda($asignacion->getSaldodeuda()); 
                                                            $asignacionNew->setSaldo($asignacion->getSaldo()); 
                                                            $asignacionNew->setCreacionasignacionpuesto($asignacion->getCreacionasignacionpuesto());
                                                            $asignacionNew->setModificacionasignacionpuesto($today);
                                                            $asignacionNew->setFkidbeneficiario($asignacion->getFkidbeneficiario());
                                                            $asignacionNew->setFkidpuesto($asignacion->getFkidpuesto());
                
                                                            $asignacion->setValortarifapuesto($valorTarifaPuesto);
                                                            $asignacion->setValorincrementoporcentual(0);

                                                            $em->persist($asignacionNew);
                                                            $em->flush();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        /**
                                         * Al activar un incremento se revisa si no existe otro incremento activo para continuar y se realiza
                                         * el incremento en cada una de las tarifas activas como si se creara de nuevo el incremento.
                                         */
                                        elseif($incremento->getIncrementoactivo() == false && $params->incrementoactivo == true){
                                            
                                            //verifica que no exista un incremento porcentual activo en la misma plaza
                                            $incrementActivoDuplicated = $em->getRepository('ModeloBundle:Tincremento')->findOneBy(array(
                                                "incrementoactivo" => true,
                                                "fkidplaza"        => $idPlaza
                                            ));

                                            if(!$incrementActivoDuplicated){
                                                
                                                //Modificacion de tarifas activas en Ttarifaanimal
                                                if($tarifasAnimal){
                                                    foreach ($tarifasAnimal as $tarifaAnimal) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaAnimal = $tarifaAnimal->getValortarifaanimal() + ($tarifaAnimal->getValortarifaanimal()*$valorIncremento);

                                                        $tarifaAnimal->setValortarifaanimal($valorTarifaAnimal);
                                                        $tarifaAnimal->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaAnimal->setModificaciontarifaanimal($today);

                                                        $em->persist($tarifaAnimal);
                                                        $em->flush();
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifainteres
                                                if($tarifasInteres){
                                                    foreach ($tarifasInteres as $tarifaInteres) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaInteres = $tarifaInteres->getValortarifainteres() + ($tarifaInteres->getValortarifainteres()*$valorIncremento);

                                                        $tarifaInteres->setValortarifainteres($valorTarifaInteres);
                                                        $tarifaInteres->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaInteres->setModificaciontarifainteres($today);

                                                        $em->persist($tarifaInteres);
                                                        $em->flush();
                                                    }
                                                }

                                                //Modificacion de tarifas activas en Ttarifapesaje
                                                if($tarifasPesaje){
                                                    foreach ($tarifasPesaje as $tarifaPesaje) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaPesaje = $tarifaPesaje->getValortarifapesaje() + ($tarifaPesaje->getValortarifapesaje()*$valorIncremento);

                                                        $tarifaPesaje->setValortarifapesaje($valorTarifaPesaje);
                                                        $tarifaPesaje->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaPesaje->setModificaciontarifapesaje($today);

                                                        $em->persist($tarifaPesaje);
                                                        $em->flush();
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifapuestoeventual
                                                if($tarifasPuestoEventual){
                                                    foreach ($tarifasPuestoEventual as $tarifaPuestoEventual) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaPuestoEventual = $tarifaPuestoEventual->getValortarifapuestoeventual() + ($tarifaPuestoEventual->getValortarifapuestoeventual()*$valorIncremento);

                                                        $tarifaPuestoEventual->setValortarifapuestoeventual($valorTarifaPuestoEventual);
                                                        $tarifaPuestoEventual->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaPuestoEventual->setModificaciontarifapuestoeventual($today);

                                                        $em->persist($tarifaPuestoEventual);
                                                        $em->flush();
                                                    }
                                                }
                                                
                                                //Modificacion de tarifas activas en Ttarifavehiculo
                                                if($tarifasVehiculo){
                                                    foreach ($tarifasVehiculo as $tarifaVehiculo) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaVehiculo = $tarifaVehiculo->getValortarifavehiculo() + ($tarifaVehiculo->getValortarifavehiculo()*$valorIncremento);

                                                        $tarifaVehiculo->setValortarifavehiculo($valorTarifaVehiculo);
                                                        $tarifaVehiculo->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaVehiculo->setModificaciontarifavehiculo($today);

                                                        $em->persist($tarifaVehiculo);
                                                        $em->flush();
                                                    }
                                                }

                                                //Modificacion de tarifas activas en Ttarifaparqueadero
                                                if($tarifasParqueadero){
                                                    foreach ($tarifasParqueadero as $tarifaParqueadero) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaParqueadero = $tarifaParqueadero->getValortarifaparqueadero() + ($tarifaParqueadero->getValortarifaparqueadero() * $valorIncremento);

                                                        $tarifaParqueadero->setValortarifaparqueadero($valorTarifaParqueadero);
                                                        $tarifaParqueadero->setValorincrementoporcentual($valorIncremento);
                                                        $tarifaParqueadero->setModificaciontarifaparqueadero($today);

                                                        $em->persist($tarifaParqueadero);
                                                        $em->flush();
                                                    }
                                                }

                                                //Modificacion de tarifas activas en Ttarifapuesto
                                                if($asignaciones){
                                                    foreach ($asignaciones as $asignacion) {

                                                        //Obtiene el valor de la tarifa sumando el porcentaje calculado con el incremento porcentual
                                                        $valorTarifaPuesto = $asignacion->getValortarifapuesto() + ($asignacion->getValortarifapuesto() * $valorIncremento);

                                                        //al cambiar la tarifa se crea una nueva asignacion desactivada con los datos anteriores
                                                        $asignacionNew = new Tasignacionpuesto();
                                                        $asignacionNew->setNumeroresolucionasignacionpuesto($asignacion->getNumeroresolucionasignacionpuesto());
                                                        $asignacionNew->setRutaresolucionasignacionpuesto($asignacion->getRutaresolucionasignacionpuesto());
                                                        $asignacionNew->setEstadoasignacionpuesto($asignacion->getEstadoasignacionpuesto()); 
                                                        $asignacionNew->setAsignacionPuestoactivo(false);
                                                        $asignacionNew->setValortarifapuesto($asignacion->getValortarifapuesto());
                                                        $asignacionNew->setValorincrementoporcentual($asignacion->getValorincrementoporcentual());
                                                        $asignacionNew->setSaldodeuda($asignacion->getSaldodeuda()); 
                                                        $asignacionNew->setSaldo($asignacion->getSaldo()); 
                                                        $asignacionNew->setCreacionasignacionpuesto($asignacion->getCreacionasignacionpuesto());
                                                        $asignacionNew->setModificacionasignacionpuesto($today);
                                                        $asignacionNew->setFkidbeneficiario($asignacion->getFkidbeneficiario());
                                                        $asignacionNew->setFkidpuesto($asignacion->getFkidpuesto());
            
                                                        $asignacion->setValortarifapuesto($valorTarifaPuesto);
                                                        $asignacion->setValorincrementoporcentual($valorIncremento);

                                                        $em->persist($asignacionNew);
                                                        $em->flush();
                                                    }   
                                                }
                                            }else{
                                                $data = array(
                                                    'status' => 'error',
                                                    'msg'    => 'Ya existe un incremento porcentual activo en la plaza1!!'
                                                );
                                                return $helpers->json($data);
                                            }
                                        }

                                        $incremento->setIncrementoactivo($params->incrementoactivo);
                                    }
                          
                                    if(isset($params->valorincremento)){
                                        /*para cambiar el incremento porcentual en cada una de las tarifas de la plaza
                                         *antes de editar trae el valor actual del incremento y con este obtiene la 
                                         *tarifa anterior, a ella se suma el nuevo valor de incremento para obtener la nueva tarifa
                                         */
                                        
                                        if($params->valorincremento >= 0 && $params->valorincremento <= 100){

                                            if(isset($params->incrementoactivo) && $incremento->getIncrementoactivo() == true && $params->incrementoactivo == true){
                                                
                                                if($valorIncrementoOld != $valorIncremento){
                                                    //Modificacion de tarifas activas en Ttarifaanimal
                                                    if($tarifasAnimal){
                                                        foreach ($tarifasAnimal as $tarifaAnimal) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaAnimal->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaAnimal = $tarifaAnimal->getValortarifaanimal()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaAnimalNew = $valorTarifaAnimal  + ($valorTarifaAnimal*$valorIncremento);

                                                                $tarifaAnimal->setValortarifaanimal($valorTarifaAnimalNew);
                                                                $tarifaAnimal->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaAnimal->setModificaciontarifaanimal($today);

                                                                $em->persist($tarifaAnimal);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifainteres
                                                    if($tarifasInteres){
                                                        foreach ($tarifasInteres as $tarifaInteres) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaInteres->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaInteres = $tarifaInteres->getValortarifainteres()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaInteresNew = $valorTarifaInteres + ($valorTarifaInteres*$valorIncremento);

                                                                $tarifaInteres->setValortarifainteres($valorTarifaInteresNew);
                                                                $tarifaInteres->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaInteres->setModificaciontarifainteres($today);

                                                                $em->persist($tarifaInteres);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifapesaje
                                                    if($tarifasPesaje){
                                                        foreach ($tarifasPesaje as $tarifaPesaje) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaPesaje->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaPesaje = $tarifaPesaje->getValortarifapesaje()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaPesajeNew = $valorTarifaPesaje  + ($valorTarifaPesaje*$valorIncremento);

                                                                $tarifaPesaje->setValortarifapesaje($valorTarifaPesajeNew);
                                                                $tarifaPesaje->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaPesaje->setModificaciontarifapesaje($today);

                                                                $em->persist($tarifaPesaje);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                
                                                    //Modificacion de tarifas activas en Ttarifapuestoeventual
                                                    if($tarifasPuestoEventual){
                                                        foreach ($tarifasPuestoEventual as $tarifaPuestoEventual) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaPuestoEventual->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaPuestoEventual = $tarifaPuestoEventual->getValortarifapuestoeventual()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaPuestoEventualNew = $valorTarifaPuestoEventual  + ($valorTarifaPuestoEventual*$valorIncremento);

                                                                $tarifaPuestoEventual->setValortarifapuestoeventual($valorTarifaPuestoEventualNew);
                                                                $tarifaPuestoEventual->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaPuestoEventual->setModificaciontarifapuestoeventual($today);

                                                                $em->persist($tarifaPuestoEventual);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                    
                                                    //Modificacion de tarifas activas en Ttarifavehiculo
                                                    if($tarifasVehiculo){
                                                        foreach ($tarifasVehiculo as $tarifaVehiculo) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaVehiculo->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaVehiculo = $tarifaVehiculo->getValortarifavehiculo()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaVehiculoNew = $valorTarifaVehiculo  + ($valorTarifaVehiculo*$valorIncremento);

                                                                $tarifaVehiculo->setValortarifavehiculo($valorTarifaVehiculoNew);
                                                                $tarifaVehiculo->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaVehiculo->setModificaciontarifavehiculo($today);

                                                                $em->persist($tarifaVehiculo);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }

                                                    //Modificacion de tarifas activas en Ttarifaparqueadero 
                                                    if($tarifasParqueadero){
                                                        foreach ($tarifasParqueadero as $tarifaParqueadero) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($tarifaParqueadero->getValorincrementoporcentual() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaParqueadero = $tarifaParqueadero->getValortarifaparqueadero()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaParqueaderoNew = $valorTarifaParqueadero  + ($valorTarifaParqueadero*$valorIncremento);

                                                                $tarifaParqueadero->setValortarifaparqueadero($valorTarifaParqueaderoNew);
                                                                $tarifaParqueadero->setValorincrementoporcentual($valorIncremento);
                                                                $tarifaParqueadero->setModificaciontarifaparqueadero($today);

                                                                $em->persist($tarifaParqueadero);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }

                                                    //Modificacion de tarifas activas en Ttarifapuesto
                                                    if($asignaciones){
                                                        foreach ($asignaciones as $asignacion) {

                                                            /*verifica que el valor del incremento en cada una de las tarifas no sea igual a 0
                                                            * este valor es 0 cuando se crea una nueva o se modifica directamente el valor de la tarifa
                                                            */
                                                            if($asignacion->getValortarifapuesto() != 0){
                                                                //obtiene el valor de la tarifa anterior a la suma del incremento
                                                                $valorTarifaPuesto = $asignacion->getValortarifapuesto()/($valorIncrementoOld+1);
                                                                //suma el nuevo incremento a la tarifa obtenida
                                                                $valorTarifaPuestoNew = $valorTarifaPuesto  + ($valorTarifaPuesto*$valorIncremento);

                                                                //al cambiar la tarifa se crea una nueva asignacion desactivada con los datos anteriores
                                                                $asignacionNew = new Tasignacionpuesto();
                                                                $asignacionNew->setNumeroresolucionasignacionpuesto($asignacion->getNumeroresolucionasignacionpuesto());
                                                                $asignacionNew->setRutaresolucionasignacionpuesto($asignacion->getRutaresolucionasignacionpuesto());
                                                                $asignacionNew->setEstadoasignacionpuesto($asignacion->getEstadoasignacionpuesto()); 
                                                                $asignacionNew->setAsignacionPuestoactivo(false);
                                                                $asignacionNew->setValortarifapuesto($asignacion->getValortarifapuesto());
                                                                $asignacionNew->setValorincrementoporcentual($asignacion->getValorincrementoporcentual());
                                                                $asignacionNew->setSaldodeuda($asignacion->getSaldodeuda()); 
                                                                $asignacionNew->setSaldo($asignacion->getSaldo()); 
                                                                $asignacionNew->setCreacionasignacionpuesto($asignacion->getCreacionasignacionpuesto());
                                                                $asignacionNew->setModificacionasignacionpuesto($today);
                                                                $asignacionNew->setFkidbeneficiario($asignacion->getFkidbeneficiario());
                                                                $asignacionNew->setFkidpuesto($asignacion->getFkidpuesto());
                    
                                                                $asignacion->setValortarifapuesto($valorTarifaPuesto);
                                                                $asignacion->setValorincrementoporcentual($valorIncremento);

                                                                $em->persist($asignacionNew);
                                                                $em->flush();
                                                            }
                                                        }
                                                    }
                                                    $incremento->setValorincremento($params->valorincremento);
                                                }
                                            }
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'El valor de incremento debe estar entre 0 y 100!!'
                                            );
                                            return $helpers->json($data);
                                        }    
                                    }

                                    if(isset($params->resolucionincremento)){
                                        $resolucionIncremento = $params->resolucionincremento;

                                        $incrementoDuplicated = $em->getRepository('ModeloBundle:Tincremento')->findOneBy(array(
                                            "resolucionincremento" => $resolucionIncremento
                                        ));

                                         //revisa en la tabla Tincremento si el valor que se desea asignar no existe
                                         $query = $em->getRepository('ModeloBundle:Tincremento')->createQueryBuilder('t')
                                         ->where('t.resolucionincremento = :resolucionincremento and t.pkidincremento != :pkidincremento')
                                         ->setParameter('resolucionincremento', $resolucionIncremento)
                                         ->setParameter('pkidincremento', $idIncremento)
                                         ->getQuery();
                                     
                                        $incrementoDuplicated = $query->getResult();

                                        if(!$incrementoDuplicated){
                                            $incremento->setResolucionincremento($resolucionIncremento);
                                        }else{
                                            $data = array(
                                                'status' => 'error',
                                                'msg'    => 'Resolucion duplicada!!'
                                            );
                                            return $helpers->json($data);
                                        }
                                    }                 

                                    $incremento->setModificacionincremento($today);

                                    if (isset($_FILES['fichero_usuario'])) {

                                        if ($_FILES['fichero_usuario']['size'] <= 5242880) {
    
                                            if ($_FILES['fichero_usuario']['type'] == "application/pdf") {
    
                                                $dir_subida = '../web/documentos/';
                                                $extension = explode("/", $_FILES['fichero_usuario']['type'])[1];
                                                $fichero_subido = $dir_subida . basename($idIncremento . "_incremento_" .$today->format('Y-m-d_H-i-s') . "." . $extension);
    
                                                if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $fichero_subido)) {
                                                    $doc_old = $incremento->getDocumentoresolucionincremento();
                                                    if ($doc_old != "sin documento") {
                                                        unlink($doc_old);
                                                    }
                                                    $incremento->setDocumentoresolucionincremento($fichero_subido);
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
                                        $documento = $params->documentoresolucionincremento;
                                        /**
                                         * Si no se envia un documento y la ruta es falsa, se elimina el documento
                                         */
                                        if(!isset($documento) || $documento == false){
                                            
                                            $documento_old = $incremento->getDocumentoresolucionincremento();
                                            if($documento_old != 'sin documento'){
                                                unlink($documento_old);
                                                $incremento->setDocumentoresolucionincremento("sin documento");
                                            }
                                           
                                        }
                                    }
                
                                    $em->persist($incremento);
                                    $em->flush();
                
                                    $data = array(
                                        'status'     => 'Exito',
                                        'msg'        => 'Incremento Porcentual actualizada!!',
                                        'incremento' => $incremento,
                                    );
    
                                    $datos = array(
                                        "idusuario"             => $identity->sub,
                                        "nombreusuario"         => $identity->name,
                                        "identificacionusuario" => $identity->identificacion,
                                        'accion'                => 'editar',
                                        "tabla"                 => 'Tincremento',
                                        "valoresrelevantes"     => 'idIncremento:'.$idIncremento.',valorIncremento:'.$incremento->getValorincremento(),
                                        'idelemento'            => $idIncremento,
                                        'origen'                => 'web'
                                    );
    
                                    $auditoria = $this->get(Auditoria::class);
                                    $auditoria->auditoria(json_encode($datos));
                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'msg'    => 'el incremento no existe!!'
                                    );
                                }
                            }else{
                                $data = array(
                                    'status' => 'error',
                                    'msg'    => 'El id de el incremento a editar es nulo!!'
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
                'modulo'        => "Incremento",
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
     * Funcion para mostrar los incrementos registrados
     * Recibe un json llamado json con los parametros para filtrar
     * pkidplaza, filtro por plazas a la que pertenece el incremento
     * al enviar un json filtra tambien por incrementos activos
     * con el id de la plaza que se desea conocer.
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la 
     * autenticacion y permisos
    */
    public function queryIncrementoAction(Request $request)
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
                                    pkidincremento,
                                    valorincremento,
                                    resolucionincremento,
                                    documentoresolucionincremento,
                                    fkidplaza,
                                    nombreplaza,
                                    incrementoactivo 
                                  FROM tincremento
                                    JOIN tplaza ON tincremento.fkidplaza = tplaza.pkidplaza 
                                  WHERE incrementoactivo = true
                                  ORDER BY resolucionincremento ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $incrementos = $stmt->fetchAll();

                        $data = array(
                            'status'    => 'Exito',
                            'incremento' => $incrementos,
                        );

                    }else {
                        $data = array(
                            'status' => 'error',
                            'msg'    => 'El usuario no tiene permisos genericos !!',
                        );
                    }
                    return $helpers->json($data);
                    }


                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {
                    //if (in_array("PERM_INCREMENTO_PORCENTUALES", $permisosDeserializados)) {
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                                                              
                        //Consulta para traer los datos de la incremento, la plaza y el usuario a los que se encuentra asignada.
                        $query = "SELECT 
                                    pkidincremento,
                                    valorincremento,
                                    resolucionincremento,
                                    documentoresolucionincremento,
                                    fkidplaza,
                                    nombreplaza,
                                    incrementoactivo 
                                  FROM tincremento
                                    JOIN tplaza ON tincremento.fkidplaza = tplaza.pkidplaza
                                  ORDER BY resolucionincremento ASC;";
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $incrementos = $stmt->fetchAll();
                        
                        $array_all = array();

                        foreach ($incrementos as $incremento) {
                            $incrementosList = array(
                                "pkidincremento"                => $incremento['pkidincremento'],
                                "valorincremento"               => $incremento['valorincremento'],
                                "resolucionincremento"          => $incremento['resolucionincremento'],
                                "documentoresolucionincremento" => $incremento['documentoresolucionincremento'],
                                "pkidplaza"                     => $incremento['fkidplaza'],
                                "nombreplaza"                   => $incremento['nombreplaza'],
                                "incrementoactivo"              => $incremento['incrementoactivo'],
                            );
                            array_push($array_all, $incrementosList);
                        }

                        
                        $cabeceras = array(
                            array(
                                "nombrecampo"    => "valorincremento",
                                "nombreetiqueta" => "Valor Incremento Porcentual"
                            ),
                            array(
                                "nombrecampo"    => "resolucionincremento",
                                "nombreetiqueta" => "Numero de Resolucion"
                            ),
                            array(
                                "nombrecampo"    => "documentoresolucionincremento",
                                "nombreetiqueta" => "Documento"
                            ),
                            array(
                                "nombrecampo"    => "nombreplaza",
                                "nombreetiqueta" => "Plaza"
                            ),
                            array(
                                "nombrecampo"    => "incrementoactivo",
                                "nombreetiqueta" => "Incremento Porcentual Activo/Inactivo"
                            )
                        );

                        $data = array(
                            'status'     => 'Exito',
                            'cabeceras'  => $cabeceras,
                            'incremento' => $array_all,
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
                'modulo'        => "Incremento",
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