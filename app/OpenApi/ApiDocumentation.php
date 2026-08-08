<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'GNS Billing API',
    description: 'REST API untuk GNS Billing System'
)]
#[OA\Server(
    url: 'http://gns_dev.test',
    description: 'Local Development'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
final class ApiDocumentation
{
}