<?php
class Validador {
    
    public static function validarCIF($cif) {
        // Limpiamos espacios y pasamos a mayúsculas
        $cif = strtoupper(trim($cif));
        
        // Comprobamos el patrón básico: 1 letra, 7 números, 1 carácter alfanumérico
        if (!preg_match('/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', $cif)) {
            return false;
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
        $letras_letra  = ['K', 'P', 'Q', 'S'];
        
        if (in_array($letra_inicial, $letras_numero)) {
            return ($control == $decena);
        } elseif (in_array($letra_inicial, $letras_letra)) {
            return ($control == $letra_esperada);
        } else {
            // Otras letras (como la B, C, D, F, G, etc.) pueden terminar en número o letra
            return ($control == $decena || $control == $letra_esperada);
        }
    }
}
?>