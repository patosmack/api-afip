<?php

namespace patosmack\Afip;

use patosmack\Afip\TokenAutorization;
use patosmack\Afip\Error\WebServiceNotImplementedException;
use patosmack\Afip\Error\ConfigurationErrorException;
use patosmack\Afip\Error\RuntimeErrorException;
use patosmack\Afip\Error\ApplicationNotFoundException;

class Afip
{
    private $wsaa_wsdl;
    private $wsaa_url;
    private $cert;
    private $privatekey;
    private $passphrase;
    private $wsdl_folder;
    private $xml_folder;
    private $cuit;
    private $options;


    public function __construct($config)
    {
        ini_set("soap.wsdl_cache_enabled", "0");

        $this->setOptions($config);
    }

    public function setOptions($config)
    {
        if (!isset($config['CUIT'])) {
            throw new ConfigurationErrorException("AFIP", "No se configuró el CUIT en la configuración de Web Service");
        } else {
            $this->setCuit($config['CUIT']);
        }

        if (!isset($config['token_dir'])) {
            throw new ConfigurationErrorException("AFIP", "No se configuró el dir del token en la configuración de Web Service");
        } else {
            $this->setXMLFolder($config['token_dir']);
        }

        if (!isset($config['production'])) {
            $config['production'] = false;
        } else {
            $config['production'] = boolval($config['production']);
        }

        if (!isset($config['passphrase'])) {
            $config['passphrase'] = 'xxxxx';
        }

        $this->passphrase = $config['passphrase'];
        
        $this->options = $config;

        $dir_name = dirname(__FILE__);

        $this->setWSDLFolder(realpath($dir_name.'/../../../wsdl').'/');

        $this->cert         = $config['cert'];
        $this->privatekey   = $config['key'];
        $this->wsaa_wsdl    = $this->wsdl_folder.'wsaa.wsdl';

        if ($config['production'] === true) {
            $this->wsaa_url     = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
        } else {
            $this->wsaa_url     = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
        }

        if (!file_exists($this->cert)) {
            throw new ConfigurationErrorException("cert", "Error al abrir el archivo ".$this->cert."\n", 1);
        }
        if (!file_exists($this->privatekey)) {
            throw new ConfigurationErrorException("privatekey", "Error al abrir el archivo ".$this->privatekey."\n", 2);
        }
        if (!file_exists($this->wsaa_wsdl)) {
            throw new ConfigurationErrorException("wsaa_wsdl", "Error al abrir el archivo ".$this->wsaa_wsdl."\n", 3);
        }
    }

    public function setWSDLFolder($wsdl_folder)
    {
        $this->wsdl_folder = $wsdl_folder;
    }

    public function setXMLFolder($xml_folder)
    {
        if (!is_dir($xml_folder)) {
            throw new ConfigurationErrorException("token_dir", "Error, El directorio del token_dir no es válido. ".$xml_folder."\n", 1);
        }

        if (!is_writable($xml_folder)) {
            throw new ConfigurationErrorException("token_dir", "Error, El directorio del token_dir no tiene permisos de escritura. ".$xml_folder."\n", 1);
        }

        $this->xml_folder = realpath($xml_folder).'/';
    }

    public function setCuit($cuit)
    {
        $this->cuit = $cuit;
    }

    public function getCuit()
    {
        return $this->cuit;
    }

    public function getXMLFolder()
    {
        return $this->xml_folder;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getWSDLFolder()
    {
        return $this->wsdl_folder;
    }

    /**
     * Obtiene el token de autorización para un servicio web de AFIP
     *
     * @param string $service Service para el token autorización
     *
     * @throws RuntimeErrorException Si ocurre un error
     *
     * @return TokenAutorization Token de autorización para un servicio web de AFIP
    **/
    public function getServiceTA($service, $continue = true)
    {
        if (file_exists($this->xml_folder.'TA-'.$this->options['CUIT'].'-'.$service.'.xml')) {
            $ta = new \SimpleXMLElement(file_get_contents($this->xml_folder.'TA-'.$this->options['CUIT'].'-'.$service.'.xml'));

            $actual_time        = new \DateTime(date('c', date('U')+600));
            $expiration_time    = new \DateTime($ta->header->expirationTime);

            if ($actual_time < $expiration_time) {
                return new TokenAutorization($ta->credentials->token, $ta->credentials->sign);
            } elseif ($continue === false) {
                throw new RuntimeErrorException("TokenAutorization", "Error obteniendo el TA", 5);
            }
        }

        if ($this->createServiceTA($service)) {
            return $this->getServiceTA($service, false);
        }
    }

    /**
     * Crea un token de autorización desde WSAA
     *
     * Solicita a WSAA un tokent de autorización para el servicio y lo
     * guarda en un archivo xml
     *
     * @param string $service Service para el token autorización
     *
     * @throws RuntimeErrorException Si ocurre un error
     *
     * @return true si el Token de autorización fue creado exitosamente
    **/
    private function createServiceTA($service)
    {
        //Creating TRA
        $tra = new \SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<loginTicketRequest version="1.0">'.
        '</loginTicketRequest>'
        );
        $tra->addChild('header');
        $tra->header->addChild('uniqueId', date('U'));
        $tra->header->addChild('generationTime', date('c', date('U')-600));
        $tra->header->addChild('expirationTime', date('c', date('U')+600));
        $tra->addChild('service', $service);
        $tra->asXML($this->xml_folder.'TRA-'.$this->options['CUIT'].'-'.$service.'.xml');

        //Signing TRA
        $status = openssl_pkcs7_sign(
            $this->xml_folder."TRA-".$this->options['CUIT'].'-'.$service.".xml",
            $this->xml_folder."TRA-".$this->options['CUIT'].'-'.$service.".tmp",
            "file://".$this->cert,
            array("file://".$this->privatekey, $this->passphrase),
            array(),
            !PKCS7_DETACHED
        );
        if (!$status) {
            return false;
        }
        $inf = fopen($this->xml_folder."TRA-".$this->options['CUIT'].'-'.$service.".tmp", "r");
        $i = 0;
        $cms="";
        while (!feof($inf)) {
            $buffer=fgets($inf);
            if ($i++ >= 4) {
                $cms.=$buffer;
            }
        }
        fclose($inf);
        unlink($this->xml_folder."TRA-".$this->options['CUIT'].'-'.$service.".xml");
        unlink($this->xml_folder."TRA-".$this->options['CUIT'].'-'.$service.".tmp");

        if (!class_exists(\SoapClient::class)) {
            throw new ApplicationNotFoundException('SOAP', 'Falta incluir la extension de SOAP para PHP');
        }
        
        //Request TA to WSAA
        $client = new \SoapClient($this->wsaa_wsdl, array(
        'soap_version'   => SOAP_1_2,
        'location'       => $this->wsaa_url,
        'trace'          => 1,
        'exceptions'     => 0
        ));
        $results=$client->loginCms(array('in0'=>$cms));
        if (is_soap_fault($results)) {
            throw new RuntimeErrorException("SOAP", " Error: ".$results->faultcode."\n".$results->faultstring."\n", 4);
        }

        $ta = $results->loginCmsReturn;

        if (file_put_contents($this->xml_folder.'TA-'.$this->options['CUIT'].'-'.$service.'.xml', $ta)) {
            return true;
        } else {
            throw new RuntimeErrorException("TokenAutorization", 'Error escribiendo "TA-'.$this->options['CUIT'].'-'.$service.'.xml"', 5);
        }
    }
}
