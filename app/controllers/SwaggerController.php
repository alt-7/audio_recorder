<?php

declare(strict_types=1);

namespace app\controllers;

use yii\web\Controller;

class SwaggerController extends Controller
{
    public function actionDoc(): string
    {
        $this->layout = false;
        return $this->renderContent(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Docs</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" >
    <style>body { margin: 0; padding: 0; }</style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"> </script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"> </script>
    <script>
    window.onload = function() {
        SwaggerUIBundle({
            url: "/openapi.json", // <-- Читаем статический файл
            dom_id: '#swagger-ui',
            presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
            layout: "StandaloneLayout"
        });
    }
    </script>
</body>
</html>
HTML
        );
    }
}