<?php
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "BuzzDM API",
    version: "1.0.0",
    description: "API documentation for BuzzDM System"
)]
#[OA\Server(url: "http://localhost/BuzzDM", description: "Local Development Server")]
#[OA\Server(url: "https://buzzdatamanager.com", description: "Production Server (Hostinger)")]
#[OA\SecurityScheme(
    securityScheme: "cookieAuth",
    type: "apiKey",
    in: "cookie",
    name: "PHPSESSID"
)]
#[OA\SecurityScheme(
    securityScheme: "devTokenAuth",
    type: "apiKey",
    in: "header",
    name: "X-Dev-Token",
    description: "Development token for API testing (only works when APP_ENV=development). Value: ***REMOVED***"
)]
class OpenApiConfig {}
