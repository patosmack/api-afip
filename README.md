# Librería cliente para los Web Services de AFIP

Clonado de : https://gitlab.siu.edu.ar/siu/api-afip

Este proyecto no es de mi auditoria

Para el desarrollo de esta librería se toma como referencia el código fuente del [Web Services](https://github.com/ivanalemunioz/afip.php) desarrollado por Ivan Muñoz.

## Instalación
La instalación se hace a través de Composer
```
composer require siu/api-afip
```
## Uso

En primer lugar para poder utilizar el servicio es necesario generar un certificado y una clave desde los servicios de AFIP.

Para obtener información de como obtener el certificado y la key para utilizar el servicio ir a [Documentación Técnica de los WS de AFIP](http://www.afip.gob.ar/ws/documentacion/default.asp)

#### Configuración
```php
<?php
    $config = [
        'CUIT' => 20200083394,
        'production' => true,
        'cert' => '/user/local/cert',
        'key' => '/user/local/key',
        'token_dir' => '/user/local/token_dir/'
    ];

    $this->afip = new \SIU\Afip\Afip($config);
```

##### Parámetros de configuración disponibles: 

| Parametro     | Descripcion   |
| ------------- | ------------- | 
| CUIT          | `(int)` El CUIT a usar en los Web Services |
| production    | `(bool)` `(default FALSE)` (Opcional) TRUE para usar los Web Services en modo producción |
| cert          | `(string)`Ruta absoluta donde se encuentra el certificado |
| key           | `(string)`Ruta absoluta donde se encuentra el certificado |
| token_dir     | `(string)`Ruta absoluta donde la lib genera el token (requiere permisos de escritura) |
| passphrase    | `(string)` `(default 'xxxxx')` (Opcional) Frase de contraseña para usar en el Web Service de Autenticación |


## Web Services disponibles
- [Facturación Electrónica](doc/ws_factura_electronica.md)
- [Consulta al padrón de AFIP alcance 4](doc/ws_sr_padron_a4.md)
- [Consulta al padrón de AFIP alcance 5](doc/ws_sr_padron_a5.md)
- [Consulta al padrón de AFIP alcance 10](doc/ws_sr_padron_a10.md)
- [Envio de datos Para el calculo de Ganancias](doc/ws_wsddjj.md)


