<?php

namespace SIU\Afip\WebService;

use SIU\Afip\Error\ConfigurationErrorException;
use SIU\Afip\Error\RuntimeErrorException;

abstract class AfipWebService
{
    private $soap_version;
    private $wsdl;
    private $url;
    private $wsdl_test;
    private $wsdl_production;
    private $url_test;
    private $afip;
    
    public function __construct($afip)
    {
        $this->afip = $afip;
    }

    abstract public function setConfig();

    public function setSoapVersion($soap_version)
    {
        $this->soap_version = $soap_version;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setWSDLTest($wsdl_test)
    {
        $this->wsdl_test = $wsdl_test;
    }

    public function setUrlTest($url_test)
    {
        $this->url_test = $url_test;
    }

    public function setWSDL($wsdl)
    {
        $this->wsdl_production = $wsdl;
    }

    public function getAfip()
    {
        return $this->afip;
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
        $options = $this->afip->getOptions();
        $wsdl_folder = $this->afip->getWSDLFolder();

        if ($options['production'] === true) {
            $this->wsdl = $wsdl_folder.$this->wsdl_production;
        } else {
            $this->wsdl = $wsdl_folder.$this->wsdl_test;
            $this->url  = $this->url_test;
        }

        if (!file_exists($this->wsdl)) {
            throw new ConfigurationErrorException("WSDL", "Error al abrir el archivo WSDL ".$this->wsdl."\n", 3);
        }

        if (!isset($this->soap_client)) {
            $this->soap_client = new \SoapClient($this->wsdl, array(
                'soap_version'  => $this->soap_version,
                'location'      => $this->url,
                'trace'         => 1,
                'exceptions'    => 0
            ));
        }

        $results = $this->soap_client->{$operation}($params);

        $this->_CheckErrors($operation, $results);

        return $results;
    }


    /**
     * Cheque si ocurre algun error en el request del Web Service
     *
     * @param string    $operation  Operación SOAP para hacer
     * @param mixed     $results    Respuesta de AFIP
     *
     * @throws RuntimeErrorException Si ocurre un error
     *
     * @return void
     **/
    private function _CheckErrors($operation, $results)
    {
        if (is_soap_fault($results)) {
            throw new RuntimeErrorException("SOAP", " Error: ".$results->faultcode."\n".$results->faultstring."\n", 4);
        }
    }
}
