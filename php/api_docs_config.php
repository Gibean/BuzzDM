<?php
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "BuzzDM API",
    version: "1.0.0",
    description: "API documentation for BuzzDM System"
)]
#[OA\Server(url: "http://localhost/BuzzDM", description: "Local Development Server")]
#[OA\SecurityScheme(
    securityScheme: "cookieAuth",
    type: "apiKey",
    in: "cookie",
    name: "PHPSESSID"
)]
class OpenApiConfig {}
