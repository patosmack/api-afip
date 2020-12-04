### Consulta al padrón de AFIP alcance 10

Servicio de Consulta de Padrón Alcance 10. El servicio de Consulta de Padrón Alcance 10 permite acceder a los datos de un contribuyente registrado en el Padrón de AFIP, en su versión mínima. Este WS se puede utilizar para acceder a datos resumidos de un contribuyente.

La documentación de AFIP de este Web Service se encuentra disponible en [Manual para el desarrollador](http://www.afip.gob.ar/ws/ws_sr_padron_a10/manual_ws_sr_padron_a10_v1.1.pdf)

#### Obtener datos de un contribuyente
```php
<?php
    $cuit = "20221064233";
        
    $padron_diez = new \patosmack\Afip\WebService\PadronAlcanceDiez($this->afip);
        
    $result = $padron_diez->getContribuyenteDetalle($cuit);
```

#### Obtener estado del servidor
```php
<?php
    $padron_diez = new \patosmack\Afip\WebService\PadronAlcanceDiez($this->afip);
        
    $result = $padron_diez->getEstadoServicio();
```