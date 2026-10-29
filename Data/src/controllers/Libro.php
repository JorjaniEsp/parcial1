<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Models\Libro as LibroModel;
use Illuminate\Database\QueryException;

class Libro {

    public function __construct(private ContainerInterface $container){ }

    public function create(Request $request, Response $response, array $args){
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];

        $isbn = trim( (string) ($data['isbn'] ?? '') );
        $titulo = trim((string) ($data['titulo'] ?? ''));
        $autor = trim((string) ($data['autor'] ?? ''));
        $anioPublicacion = trim( (string) ($data['anio_publicacion'] ?? 0) );
        $ejemplaresDisponibles = trim((string) ($data['ejemplares_disponibles'] ?? 0));

        try {
            $eloquent = $this->container->get('eloquent');
            $libro = LibroModel::create([
                    'isbn'  => $isbn,
                    'titulo' => $titulo,
                    'autor' => $autor,
                    'anio_publicacion' => $anioPublicacion,
                    'ejemplares_disponibles' => $ejemplaresDisponibles
                ]);
            return $this->json($response,[ 'data' => $libro->toArray()], 201);

        } catch (QueryException $exception){
            if ((int) ($exception->errorInfo[1] ?? 0 ) === 1062) {
                return $this->json($response, ['error' => 'El isbn ya está registrado.'], 409);
            }
            throw $exception;
        }
    }

    public function read(Request $request, Response $response, array $args){
        $this->container->get('eloquent');

        if(isset($args['id'])){
            $libro = LibroModel::query()
                ->select('id', 'isbn', 'titulo', 'autor', 'anio_publicacion', 'ejemplares_disponibles', 'fecha_registro')
                ->find($args['id']);

            if($libro === null){
                return $this->json($response, ['error' => 'El libro no esta registrado'], 404);
            }

            return $this->json($response, ['data' => $libro], 200);
        }

        $libros = LibroModel::query()
            ->select('id', 'isbn', 'titulo', 'autor', 'anio_publicacion', 'ejemplares_disponibles', 'fecha_registro')
            ->get()
            ->toArray();
        return $this->json($response, ['data' => $libros], 200);
    }

    public function update(Request $request, Response $response, array $args): Response {
        $eloquent = $this->container->get('eloquent');
        
        if (isset($args['id'])) {
            $libro = LibroModel::find($args['id']);

            if ($libro === null) {
                return $this->json($response, ['error' => 'Libro no encontrado.'], 404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            if (isset($data['titulo'])) {
                $titulo = trim((string) $data['titulo']);

                if ($titulo === '' || mb_strlen($titulo) > 150) {
                    return $this->json($response, ['error' => 'Titulo invalido.'], 422);
                }
                $libro->titulo = $titulo;
            }

            if (isset($data['autor'])) {
                $autor = trim((string) $data['autor']);
                if ($autor === '' || mb_strlen($autor) > 100) {
                    return $this->json($response, ['error' => 'Autor inválido.'], 422);
                }
                $libro->autor = $autor;
            }

            if (isset($data['ejemplares_disponibles'])) {
                $ejemplares_disponibles = filter_var($data['ejemplares_disponibles'], FILTER_VALIDATE_INT);
                if ($ejemplares_disponibles === false || $ejemplares_disponibles < 0) {
                    return $this->json($response, ['error' => 'La cantidad de ejemplares debe ser mayor o igual a 0.'], 422);
                }
                $libro->ejemplares_disponibles = $ejemplares_disponibles;
            }

            if (isset($data['anio_publicacion'])) {
                $anio_publicacion = filter_var($data['anio_publicacion'], FILTER_VALIDATE_INT);
                if ($anio_publicacion === false || $anio_publicacion < 0) {
                    return $this->json($response, ['error' => 'Año invalido.'], 422);
                }
                $libro->anio_publicacion = $anio_publicacion;
            }

            $libro->save();

            return $this->json($response, ['data' => $libro->toArray()], 200);
        }
        return $this->json($response, ['error' => 'Datos incompletos'], 400);
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $eloquent = $this->container->get('eloquent');

        if (isset($args['id'])) {
            $libro = LibroModel::find($args['id']);

            if ($libro === null) {
                return $this->json($response, ['error' => 'Libro no encontrado.'], 404);
            }

            $libro->delete();
                
            return $this->json($response, ['message' => 'Libro eliminado exitosamente'], 200);
        }
    }

    private function json(Response $response, array $payload, int $status) : Response {
        $response->getBody()->write(json_encode($payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json ; charset=utf-8')
            ->withStatus($status);
    }
}