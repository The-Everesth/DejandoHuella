<?php
// app/Helpers/ServiceNameHelper.php

if (!function_exists('friendly_service_name')) {
    function friendly_service_name($service)
    {
        return match ($service) {
            'srv_consulta' => 'Consulta',
            'srv_esterilizacion' => 'Esterilización',
            // Agrega más servicios aquí si es necesario
            default => ucfirst(str_replace(['srv_', '_'], ['', ' '], $service)),
        };
    }
}
