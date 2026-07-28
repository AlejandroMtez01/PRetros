<?php
class Validador
{

    public static function validarCIF($cif)
    {
        // Limpiamos espacios y pasamos a mayúsculas
        $cif = strtoupper(trim($cif));

        // 1. COMPROBAR DNI / NIE
        // Patrón: Empieza por X, Y, Z o un número, seguido de 7 números y una letra final
        if (preg_match('/^[XYZ0-9][0-9]{7}[A-Z]$/', $cif)) {
            $letras_dni = 'TRWAGMYFPDXBNJZSQVHLCKE';

            // Si es NIE (X, Y, Z), sustituimos la letra inicial por 0, 1 o 2 para el cálculo
            $cif_numerico = str_replace(['X', 'Y', 'Z'], ['0', '1', '2'], substr($cif, 0, 8));

            $letra_esperada = $letras_dni[(int)$cif_numerico % 23];
            $letra_proporcionada = $cif[8];

            return $letra_esperada === $letra_proporcionada;
        }

        // 2. COMPROBAR CIF (Empresas)
        // Patrón: 1 letra específica, 7 números, 1 carácter alfanumérico
        if (!preg_match('/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', $cif)) {
            return false; // Si no es DNI, NIE ni tiene formato de CIF, es inválido directamente
        }

        $letra_inicial = $cif[0];
        $digitos = substr($cif, 1, 7);
        $control = $cif[8];

        // Sumamos los dígitos de las posiciones pares
        $suma_pares = $digitos[1] + $digitos[3] + $digitos[5];

        // Multiplicamos por 2 los impares y sumamos sus cifras
        $suma_impares = 0;
        for ($i = 0; $i <= 6; $i += 2) {
            $multiplicacion = (int)$digitos[$i] * 2;
            $suma_impares += floor($multiplicacion / 10) + ($multiplicacion % 10);
        }

        $suma_total = $suma_pares + $suma_impares;
        $decena = (10 - ($suma_total % 10)) % 10;

        $letras_control = 'JABCDEFGHI';
        $letra_esperada = $letras_control[$decena];

        // Dependiendo de la letra inicial, el control es un número o una letra
        $letras_numero = ['A', 'B', 'E', 'H'];
        $letras_letra  = ['K', 'P', 'Q', 'S', 'W'];

        if (in_array($letra_inicial, $letras_numero)) {
            return ($control == $decena);
        } elseif (in_array($letra_inicial, $letras_letra)) {
            return ($control === $letra_esperada);
        } else {
            // Otras letras pueden terminar en número o letra indistintamente
            return ($control == $decena || $control === $letra_esperada);
        }
    }
}
