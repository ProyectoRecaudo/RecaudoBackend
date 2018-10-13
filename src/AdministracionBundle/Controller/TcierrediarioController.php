<?php

namespace AdministracionBundle\Controller;

use ModeloBundle\Entity\Tcierrediarioplaza;
use ModeloBundle\Entity\Tcierrediariosector;
use SeguridadBundle\Services\Auditoria;
use SeguridadBundle\Services\Helpers;
use SeguridadBundle\Services\JwtAuth;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class TcierrediarioController extends Controller
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
     * Funcion para registrar una cierre de diario
     * recibe un token en una variable llamada authorization
     * generado cuando el usuario ingresa al sistema para la
     * autenticacion y permisos
     */
    public function newcierrediarioAction(Request $request)
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

                    if (in_array("PERM_GENERICOS", $permisosDeserializados)) {

                        //entiti manager
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();

                        $now = new \Datetime("now", new \DateTimeZone('America/Bogota'));
                        $pkidusuariorecaudador = $request->get('pkidusuariorecaudador');

                        if ($pkidusuariorecaudador == null) {
                            if (!is_object($factura)) {
                                $data = array(
                                    'status' => 'error',
                                    'msg' => 'Envie el parametro con el id del usuario recaudador',
                                );
                                return $helpers->json($data);
                            }
                        }

                        $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                            "pkidusuario" => $pkidusuariorecaudador,
                        ));

                        if (!is_object($usuario)) {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'El id del usuario recaudador no existe !!',
                            );
                            return $helpers->json($data);
                        }

                        //por sector
                        $query = "                  select pkidsector,pkidplaza from trecibopuesto
                                                    join tsector on trecibopuesto.fkidsector=tsector.pkidsector join tzona on tsector.fkidzona=tzona.pkidzona join tplaza on tplaza.pkidplaza=tzona.fkidplaza
                                                    where to_char(creacionrecibo,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibopuestoactivo=true and sectoractivo=true
                                                    and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidsector,pkidplaza from treciboanimal
                                                    join tsector on treciboanimal.fkidsector=tsector.pkidsector join tzona on tsector.fkidzona=tzona.pkidzona join tplaza on tplaza.pkidplaza=tzona.fkidplaza
                                                    where
                                                    to_char(creacionreciboanimal,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and reciboanimalactivo=true
                                                    and sectoractivo=true and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidsector,pkidplaza from trecibopuestoeventual
                                                    join tsector on trecibopuestoeventual.fkidsector=tsector.pkidsector join tzona on tsector.fkidzona=tzona.pkidzona join tplaza on tplaza.pkidplaza=tzona.fkidplaza
                                                    where
                                                    to_char(creacionrecibopuestoeventual,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibopuestoeventualactivo=true
                                                    and sectoractivo=true and fkidusuariorecaudador=$pkidusuariorecaudador";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $sectores = $stmt->fetchAll();

                        if (!empty($sectores)) {

                            $cierresector_arr = array();

                            $cierres = array(
                                "recaudototalacuerdo" => "",
                                "recaudocuotaacuerdo" => "",
                                "recaudodeudaacuerdo" => "",
                                "recaudodeuda" => "",
                                "recaudomultas" => "",
                                "recaudocuotames" => "",
                                "recaudoanimales" => "",
                                "recaudoeventuales" => "",
                                "identificacionrecaudador" => "",
                                "nombrerecaudador" => "",
                                "apellidorecaudador" => "",
                                "creacioncierrediariosector" => "",
                                "modificacioncierrediariosector" => "",
                                "fkidusuariorecaudador" => "",
                                "fkidplaza" => "",
                                "fkidsector" => "",
                            );

                            foreach ($sectores as $sector) {
                                $cierres['fkidplaza'] = $sector['pkidplaza'];
                                $cierres['fkidsector'] = $sector['pkidsector'];
                                $cierres['identificacionrecaudador'] = $usuario->getIdentificacion();
                                $cierres['nombrerecaudador'] = $usuario->getNombreusuario();
                                $cierres['apellidorecaudador'] = $usuario->getApellido();
                                $cierres['creacioncierrediariosector'] = new \Datetime("now");
                                $cierres['modificacioncierrediariosector'] = new \Datetime("now");
                                $cierres['fkidusuariorecaudador'] = $usuario->getPkidusuario();

                                $query = "
                                select sum(abonototalacuerdo) as abonototalacuerdo,
                                sum(abonocuotaacuerdo) as abonocuotaacuerdo,
                                sum(abonodeudaacuerdo) as abonodeudaacuerdo,
                                sum(abonodeuda) as abonodeuda,
                                sum(abonomultas) as abonomultas,
                                sum(abonocuotames) as abonocuotames from trecibopuesto
                                where to_char(creacionrecibo,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibopuestoactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador and fkidsector=" . $sector['pkidsector'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibopuestos = $stmt->fetchAll();

                                if ($recibopuestos[0]['abonototalacuerdo'] == null) {
                                    $recibopuestos[0]['abonototalacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonocuotaacuerdo'] == null) {
                                    $recibopuestos[0]['abonocuotaacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonodeudaacuerdo'] == null) {
                                    $recibopuestos[0]['abonodeudaacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonodeuda'] == null) {
                                    $recibopuestos[0]['abonodeuda'] = 0;

                                }
                                if ($recibopuestos[0]['abonomultas'] == null) {
                                    $recibopuestos[0]['abonomultas'] = 0;

                                }
                                if ($recibopuestos[0]['abonocuotames'] == null) {
                                    $recibopuestos[0]['abonocuotames'] = 0;

                                }

                                $cierres['recaudototalacuerdo'] = $recibopuestos[0]['abonototalacuerdo'];
                                $cierres['recaudocuotaacuerdo'] = $recibopuestos[0]['abonocuotaacuerdo'];
                                $cierres['recaudodeudaacuerdo'] = $recibopuestos[0]['abonodeudaacuerdo'];
                                $cierres['recaudodeuda'] = $recibopuestos[0]['abonodeuda'];
                                $cierres['recaudomultas'] = $recibopuestos[0]['abonomultas'];
                                $cierres['recaudocuotames'] = $recibopuestos[0]['abonocuotames'];

                                $query = "
                                select sum(valoreciboanimal) as valoreciboanimal from treciboanimal
                                                                                where to_char(creacionreciboanimal,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and reciboanimalactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador and fkidsector=" . $sector['pkidsector'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $reciboanimales = $stmt->fetchAll();

                                if ($reciboanimales[0]['valoreciboanimal'] == null) {
                                    $reciboanimales = array("0" => array(
                                        "valoreciboanimal" => 0,
                                    ),
                                    );
                                }

                                $cierres['recaudoanimales'] = $reciboanimales[0]['valoreciboanimal'];

                                $query = "
                                select sum(valorecibopuestoeventual) as valorecibopuestoeventual from trecibopuestoeventual
                                                        where to_char(creacionrecibopuestoeventual,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibopuestoeventualactivo=true  and fkidusuariorecaudador=$pkidusuariorecaudador and fkidsector=" . $sector['pkidsector'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibopuestoeventuales = $stmt->fetchAll();

                                if ($recibopuestoeventuales[0]['valorecibopuestoeventual'] == null) {
                                    $recibopuestoeventuales = array("0" => array(
                                        "valorecibopuestoeventual" => 0,
                                    ),
                                    );
                                }

                                $cierres['recaudoeventuales'] = $recibopuestoeventuales[0]['valorecibopuestoeventual'];

                                array_push($cierresector_arr, $cierres);
                            }

                            foreach ($cierresector_arr as $cierresector_arrs) {
                                $cierrediariosector = new Tcierrediariosector();

                                $cierrediariosector->setrecaudototalacuerdo($cierresector_arrs['recaudototalacuerdo']);
                                $cierrediariosector->setrecaudocuotaacuerdo($cierresector_arrs['recaudocuotaacuerdo']);
                                $cierrediariosector->setrecaudodeudaacuerdo($cierresector_arrs['recaudodeudaacuerdo']);
                                $cierrediariosector->setrecaudodeuda($cierresector_arrs['recaudodeuda']);
                                $cierrediariosector->setrecaudomultas($cierresector_arrs['recaudomultas']);
                                $cierrediariosector->setrecaudocuotames($cierresector_arrs['recaudocuotames']);
                                $cierrediariosector->setrecaudoanimales($cierresector_arrs['recaudoanimales']);
                                $cierrediariosector->setrecaudoeventuales($cierresector_arrs['recaudoeventuales']);
                                $cierrediariosector->setidentificacionrecaudador($cierresector_arrs['identificacionrecaudador']);
                                $cierrediariosector->setnombrerecaudador($cierresector_arrs['nombrerecaudador']);
                                $cierrediariosector->setapellidorecaudador($cierresector_arrs['apellidorecaudador']);
                                $cierrediariosector->setcreacioncierrediariosector($cierresector_arrs['creacioncierrediariosector']);
                                $cierrediariosector->setmodificacioncierrediariosector($cierresector_arrs['modificacioncierrediariosector']);
                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $cierresector_arrs['fkidplaza'],
                                ));
                                $cierrediariosector->setfkidplaza($plaza);
                                $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $cierresector_arrs['fkidusuariorecaudador'],
                                ));
                                $cierrediariosector->setfkidusuariorecaudador($usuario);
                                $sector = $this->getDoctrine()->getRepository("ModeloBundle:Tsector")->findOneBy(array(
                                    "pkidsector" => $cierresector_arrs['fkidsector'],
                                ));
                                $cierrediariosector->setfkidsector($sector);

                                $em->persist($cierrediariosector);
                            }
                        }

                        //" . $now->format("y-m-d") . "

                        //por plaza
                        $query = "                  select pkidplaza from trecibopuesto
                                                    join tplaza on trecibopuesto.fkidplaza=tplaza.pkidplaza
                                                    where to_char(creacionrecibo,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibopuestoactivo=true and plazaactivo=true
                                                    and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidplaza from treciboanimal
                                                    join tplaza on treciboanimal.fkidplaza=tplaza.pkidplaza
                                                    where
                                                    to_char(creacionreciboanimal,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and reciboanimalactivo=true
                                                    and plazaactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidplaza from trecibopuestoeventual
                                                    join tplaza on trecibopuestoeventual.fkidplaza=tplaza.pkidplaza
                                                    where
                                                    to_char(creacionrecibopuestoeventual,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibopuestoeventualactivo=true
                                                    and plazaactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidplaza from treciboparqueadero
                                                    join tplaza on treciboparqueadero.fkidplaza=tplaza.pkidplaza
                                                    where
                                                    to_char(creacionreciboparqueadero,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and reciboparqueaderoactivo=true
                                                    and plazaactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidplaza from trecibovehiculo
                                                    join tplaza on trecibovehiculo.fkidplaza=tplaza.pkidplaza
                                                    where
                                                    to_char(creacionrecibovehiculo,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibovehiculoactivo=true
                                                    and plazaactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador
                                                    UNION
                                                    select pkidplaza from trecibopesaje
                                                    join tplaza on trecibopesaje.fkidplaza=tplaza.pkidplaza
                                                    where
                                                    to_char(creacionrecibopesaje,'yy-mm-dd')='" . $now->format("y-m-d") . "'
                                                    and recibopesajeactivo=true
                                                    and plazaactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador ";

                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $plazas = $stmt->fetchAll();

                        if (!empty($plazas) || !empty($sectores)) {

                            $cierreplaza_arr = array();

                            $cierres = array(
                                "recaudototalacuerdo" => "",
                                "recaudocuotaacuerdo" => "",
                                "recaudodeudaacuerdo" => "",
                                "recaudodeuda" => "",
                                "recaudomultas" => "",
                                "recaudocuotames" => "",
                                "recaudoanimales" => "",
                                "recaudopesaje" => "",
                                "recaudovehiculos" => "",
                                "recaudoparqueaderos" => "",
                                "recaudoeventuales" => "",
                                "identificacionrecaudador" => "",
                                "nombrerecaudador" => "",
                                "apellidorecaudador" => "",
                                "creacioncierrediariosector" => "",
                                "modificacioncierrediariosector" => "",
                                "fkidusuariorecaudador" => "",
                                "fkidplaza" => "",
                            );

                            foreach ($plazas as $plaza) {
                                $cierres['fkidplaza'] = $plaza['pkidplaza'];
                                $cierres['identificacionrecaudador'] = $usuario->getIdentificacion();
                                $cierres['nombrerecaudador'] = $usuario->getNombreusuario();
                                $cierres['apellidorecaudador'] = $usuario->getApellido();
                                $cierres['creacioncierrediarioplaza'] = new \Datetime("now");
                                $cierres['modificacioncierrediarioplaza'] = new \Datetime("now");
                                $cierres['fkidusuariorecaudador'] = $usuario->getPkidusuario();

                                $query = "select sum(valoreciboparqueadero) as valoreciboparqueadero from treciboparqueadero
                        where to_char(creacionreciboparqueadero,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and reciboparqueaderoactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $reciboparqueaderos = $stmt->fetchAll();

                                if ($reciboparqueaderos[0]['valoreciboparqueadero'] == null) {
                                    $reciboparqueaderos = array("0" => array(
                                        "valoreciboparqueadero" => 0,
                                    ),
                                    );
                                }
                                $cierres['recaudoparqueaderos'] = $reciboparqueaderos[0]['valoreciboparqueadero'];

                                $query = "select sum(valorecibopesaje) as valorecibopesaje from trecibopesaje
                        where to_char(creacionrecibopesaje,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibopesajeactivo=true  and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibopesajes = $stmt->fetchAll();

                                if ($recibopesajes[0]['valorecibopesaje'] == null) {
                                    $recibopesajes = array("0" => array(
                                        "valorecibopesaje" => 0,
                                    ),
                                    );
                                }
                                $cierres['recaudopesaje'] = $recibopesajes[0]['valorecibopesaje'];

                                $query = "
                        select sum(valorecibovehiculo) as valorecibovehiculo from trecibovehiculo
                                                where to_char(creacionrecibovehiculo,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibovehiculoactivo=true  and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibovehiculos = $stmt->fetchAll();

                                if ($recibovehiculos[0]['valorecibovehiculo'] == null) {
                                    $recibovehiculos = array("0" => array(
                                        "valorecibovehiculo" => 0,
                                    ),
                                    );
                                }
                                $cierres['recaudovehiculos'] = $recibovehiculos[0]['valorecibovehiculo'];

                                $query = "
                        select sum(abonototalacuerdo) as abonototalacuerdo,
                        sum(abonocuotaacuerdo) as abonocuotaacuerdo,
                        sum(abonodeudaacuerdo) as abonodeudaacuerdo,
                        sum(abonodeuda) as abonodeuda,
                        sum(abonomultas) as abonomultas,
                        sum(abonocuotames) as abonocuotames from trecibopuesto
                        where to_char(creacionrecibo,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibopuestoactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibopuestos = $stmt->fetchAll();

                                if ($recibopuestos[0]['abonototalacuerdo'] == null) {
                                    $recibopuestos[0]['abonototalacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonocuotaacuerdo'] == null) {
                                    $recibopuestos[0]['abonocuotaacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonodeudaacuerdo'] == null) {
                                    $recibopuestos[0]['abonodeudaacuerdo'] = 0;

                                }
                                if ($recibopuestos[0]['abonodeuda'] == null) {
                                    $recibopuestos[0]['abonodeuda'] = 0;

                                }
                                if ($recibopuestos[0]['abonomultas'] == null) {
                                    $recibopuestos[0]['abonomultas'] = 0;

                                }
                                if ($recibopuestos[0]['abonocuotames'] == null) {
                                    $recibopuestos[0]['abonocuotames'] = 0;

                                }

                                $cierres['recaudototalacuerdo'] = $recibopuestos[0]['abonototalacuerdo'];
                                $cierres['recaudocuotaacuerdo'] = $recibopuestos[0]['abonocuotaacuerdo'];
                                $cierres['recaudodeudaacuerdo'] = $recibopuestos[0]['abonodeudaacuerdo'];
                                $cierres['recaudodeuda'] = $recibopuestos[0]['abonodeuda'];
                                $cierres['recaudomultas'] = $recibopuestos[0]['abonomultas'];
                                $cierres['recaudocuotames'] = $recibopuestos[0]['abonocuotames'];

                                $query = "
                        select sum(valoreciboanimal) as valoreciboanimal from treciboanimal
                                                                        where to_char(creacionreciboanimal,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and reciboanimalactivo=true and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $reciboanimales = $stmt->fetchAll();

                                if ($reciboanimales[0]['valoreciboanimal'] == null) {
                                    $reciboanimales = array("0" => array(
                                        "valoreciboanimal" => 0,
                                    ),
                                    );
                                }

                                $cierres['recaudoanimales'] = $reciboanimales[0]['valoreciboanimal'];

                                $query = "
                        select sum(valorecibopuestoeventual) as valorecibopuestoeventual from trecibopuestoeventual
                                                where to_char(creacionrecibopuestoeventual,'yy-mm-dd') ='" . $now->format("y-m-d") . "' and recibopuestoeventualactivo=true  and fkidusuariorecaudador=$pkidusuariorecaudador and fkidplaza=" . $plaza['pkidplaza'] . "";

                                $stmt = $db->prepare($query);
                                $params = array();
                                $stmt->execute($params);
                                $recibopuestoeventuales = $stmt->fetchAll();

                                if ($recibopuestoeventuales[0]['valorecibopuestoeventual'] == null) {
                                    $recibopuestoeventuales = array("0" => array(
                                        "valorecibopuestoeventual" => 0,
                                    ),
                                    );
                                }

                                $cierres['recaudoeventuales'] = $recibopuestoeventuales[0]['valorecibopuestoeventual'];

                                array_push($cierreplaza_arr, $cierres);
                            }

                            foreach ($cierreplaza_arr as $cierreplaza_arrs) {
                                $cierrediarioplaza = new Tcierrediarioplaza();

                                $cierrediarioplaza->setrecaudototalacuerdo($cierreplaza_arrs['recaudototalacuerdo']);
                                $cierrediarioplaza->setrecaudocuotaacuerdo($cierreplaza_arrs['recaudocuotaacuerdo']);
                                $cierrediarioplaza->setrecaudodeudaacuerdo($cierreplaza_arrs['recaudodeudaacuerdo']);
                                $cierrediarioplaza->setrecaudodeuda($cierreplaza_arrs['recaudodeuda']);
                                $cierrediarioplaza->setrecaudomultas($cierreplaza_arrs['recaudomultas']);
                                $cierrediarioplaza->setrecaudocuotames($cierreplaza_arrs['recaudocuotames']);
                                $cierrediarioplaza->setrecaudoanimales($cierreplaza_arrs['recaudoanimales']);
                                $cierrediarioplaza->setrecaudopesaje($cierreplaza_arrs['recaudopesaje']);
                                $cierrediarioplaza->setrecaudovehiculos($cierreplaza_arrs['recaudovehiculos']);
                                $cierrediarioplaza->setrecaudoparqueaderos($cierreplaza_arrs['recaudoparqueaderos']);
                                $cierrediarioplaza->setrecaudoeventuales($cierreplaza_arrs['recaudoeventuales']);
                                $cierrediarioplaza->setidentificacionrecaudador($cierreplaza_arrs['identificacionrecaudador']);
                                $cierrediarioplaza->setnombrerecaudador($cierreplaza_arrs['nombrerecaudador']);
                                $cierrediarioplaza->setapellidorecaudador($cierreplaza_arrs['apellidorecaudador']);
                                $cierrediarioplaza->setcreacioncierrediarioplaza($cierreplaza_arrs['creacioncierrediarioplaza']);
                                $cierrediarioplaza->setmodificacioncierrediarioplaza($cierreplaza_arrs['modificacioncierrediarioplaza']);
                                $plaza = $this->getDoctrine()->getRepository("ModeloBundle:Tplaza")->findOneBy(array(
                                    "pkidplaza" => $cierreplaza_arrs['fkidplaza'],
                                ));
                                $cierrediarioplaza->setfkidplaza($plaza);
                                $usuario = $this->getDoctrine()->getRepository("ModeloBundle:Tusuario")->findOneBy(array(
                                    "pkidusuario" => $cierreplaza_arrs['fkidusuariorecaudador'],
                                ));
                                $cierrediarioplaza->setfkidusuariorecaudador($usuario);

                                $em->persist($cierrediarioplaza);
                            }

                            $em->flush();

                            $data = array(
                                'status' => 'Exito',
                                'msg' => 'Se realizo correctamente el cierre diario para el recaudador: ' . $usuario->getNombreusuario() . ', para la fecha del dia de hoy (' . $now->format("y-m-d") . ')',
                            );

                        } else {
                            $data = array(
                                'status' => 'error',
                                'msg' => 'El recaudador '. $usuario->getNombreusuario() . ', no ha hecho ningun recaudo para el dia de hoy (' . $now->format("y-m-d").')',
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
                        'msg' => 'Token no valido!!',
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

            $data = array(
                'idusuario' => $identity->sub,
                'nombreusuario' => $identity->name,
                'modulo' => 'cierrediario',
                'metodo' => $trace[0]['function'],
                'mensaje' => $e->getMessage(),
                'tipoExepcion' => $trace[0]['class'],
                'pila' => $e->getTraceAsString(),
                'origen' => 'web',
            );

            try {
                $excepcion = $this->get(Auditoria::class);
                $excepcion->exepcion(json_encode($data));

            } catch (\Exception $a) {}

            throw $e;
        }
    }

}
