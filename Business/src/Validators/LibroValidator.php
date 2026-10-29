<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class LibroValidator {
    private function reglas() : array {
        return [
            'isbn' => [
                'regla' => v::stringType()->notBlank()->length(v::between(1, 30)),
                'mensaje' => 'El ISBN es obligatorio y debe tener entre 1 y 30 caracteres.',
            ],
        ];
    }

    public function validate(array $data): array {
        $errores = [];

        foreach ($this->reglas() as $campo => $config) {
            $valor = $data[$campo] ?? null;
            $regla = $config['regla'];
            $mensaje = $config['mensaje'];
            if ($regla->validate($valor)->hasFailed()) {
                $errores[$campo] = $mensaje;
            }
        }

        return $errores;
    }
}
