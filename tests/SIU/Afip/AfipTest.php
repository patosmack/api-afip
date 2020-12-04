<?php
namespace patosmack\Afip\Test;

use PHPUnit\Framework\TestCase;

class AfipTest extends TestCase
{
    protected $afip;
    
    protected function setUp()
    {
        $config = [
            'CUIT' => 20200083394,
            'production' => false,
            'cert' => '/user/local/cert',
            'key' => '/user/local/key',
            'token_dir' => '/user/local/xml/',
        ];

        $this->afip = new \patosmack\Afip\Afip($config);
    }

    public function testGetEstadoServicio()
    {
        $padron_a4 = new \patosmack\Afip\WebService\PadronAlcanceCuatro($this->afip);
        
        $result = $padron_a4->getEstadoServicio();

        $this->assertEquals($result->appserver, 'OK');
        $this->assertEquals($result->authserver, 'OK');
        $this->assertEquals($result->dbserver, 'OK');
    }

    public function testGetPadronAlcanceCuatro()
    {
        $cuit = "20002307554";
        
        $padron_a4 = new \patosmack\Afip\WebService\PadronAlcanceCuatro($this->afip);
        
        $result = $padron_a4->getContribuyenteDetalle($cuit);

        $this->assertNotNull($result->apellido);
    }
}
