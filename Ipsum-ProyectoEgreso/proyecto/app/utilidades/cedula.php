<?php

declare(strict_types=1);

function normalizarCedula(string $cedula): string
{
    return preg_replace('/\D+/', '', $cedula) ?? '';
}

function cedulaTieneFormatoValido(string $cedula): bool
{
    $cedulaNormalizada = normalizarCedula($cedula);

    return preg_match('/^\d{7,8}$/', $cedulaNormalizada) === 1;
}

function formatearCedula(string $cedula): string
{
    $cedulaNormalizada = normalizarCedula($cedula);

    if (strlen($cedulaNormalizada) === 8) {
        return sprintf(
            '%s.%s.%s-%s',
            substr($cedulaNormalizada, 0, 1),
            substr($cedulaNormalizada, 1, 3),
            substr($cedulaNormalizada, 4, 3),
            substr($cedulaNormalizada, 7, 1)
        );
    }

    if (strlen($cedulaNormalizada) === 7) {
        return sprintf(
            '%s.%s-%s',
            substr($cedulaNormalizada, 0, 3),
            substr($cedulaNormalizada, 3, 3),
            substr($cedulaNormalizada, 6, 1)
        );
    }

    return $cedula;
}

function nombreVisibleRol(string $rol): string
{
    return match ($rol) {
        'administrador' => 'Administrador',
        'coordinador' => 'Coordinador',
        default => 'Profesor',
    };
}
