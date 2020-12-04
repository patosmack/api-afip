<?php
/**
 * Consulta a Padrón Alcance 4 (ws_sr_padron_a4)
 *
 * @link http://www.afip.gob.ar/ws/ws_sr_padron_a4/manual_ws_sr_padron_a4_v1.1.pdf WS Especificación
 *
 **/

namespace patosmack\Afip\WebService;

use patosmack\Afip\WebService\AfipWebService;

class PadronAlcanceCuatro extends AfipWebService
{
    public function __construct($afip)
    {
        parent::__construct($afip);

        $this->setConfig();
    }

    public function setConfig()
    {
        $this->setSoapVersion(SOAP_1_1);
        $this->setWSDL('ws_sr_padron_a4-production.wsdl');
        $this->setUrl('https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4');
        $this->setWSDLTest('ws_sr_padron_a4.wsdl');
        $this->setUrlTest('https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4');
    }

    /**
     *  Verifica el estado y la disponibilidad de los elementos principales del servicio
     *  (aplicación, autenticación y base de datos).
     *  {@see WS Especificación item 3.1}
     *
     * @return object { appserver => Web Service status,
     * dbserver => Database status, authserver => Autentication
     * server status}
    **/
    public function getEstadoServicio()
    {
        return $this->ejecutar('dummy');
    }

    /**
     * Obtiene del servicio web los detalles del contribuyente {@see WS
     * Especificación item 3.2}
     *
     * @throws Excepción si existe un error en respuesta
     *
     * @return object|null si el contribuyente no existe, return null,
     * si existe, returns persona propiedad de la respuesta {@see
     * WS Especificación item 3.2.2}
    **/
    public function getContribuyenteDetalle($id)
    {
        $afip = $this->getAfip();

        $ta = $afip->getServiceTA('ws_sr_padron_a4');
        
        $params = array(
            'token'             => $ta->getToken(),
            'sign'              => $ta->getSign(),
            'cuitRepresentada'  => $afip->getCuit(),
            'idPersona'         => $id
        );

        try {
            return $this->ejecutar('getPersona', $params)->persona;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'No existe') !== false) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Envia un request al servidor de AFIP
     *
     * @param string    $operation  Operación SOAP para hacer
     * @param array     $params     Parámetros para enviar
     *
     * @return mixed Resultados de la operación
     **/
    public function ejecutar($operation, $params = array())
    {
        $results = parent::ejecutar($operation, $params);

        return $results->{$operation == 'getPersona' ? 'personaReturn' : 'return'};
    }
}
