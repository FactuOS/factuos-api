---
name: laravel_architecture
description: Directrices y estándares avanzados de Arquitectura Limpia y Diseño Guiado por el Dominio (DDD) en Laravel. Prioriza la separación de conceptos, Actions transaccionales, DTOs, tipado estricto y controladores delgados.
---

# Estándar de Arquitectura y Diseño para Laravel (FactuOS API)

Este documento define las reglas de diseño de software y patrones arquitectónicos que el asistente de IA y los desarrolladores deben seguir obligatoriamente al escribir código para el backend de FactuOS.

---

## 🏗️ 1. Arquitectura Basada en Dominios (DDD Adaptado)

Para evitar el desorden de carpetas y mantener las responsabilidades separadas por contexto de negocio, estructuraremos la aplicación utilizando **Dominios** en lugar de tener estructuras planas en `app/Actions`, `app/Models`, etc.

### Estructura de Directorios

```text
app/
├── Http/                          # Capa de Entrada (HTTP/API)
│   ├── Controllers/               # Controladores ultra-delgados
│   ├── Requests/                  # Form Requests para validación de entrada
│   └── Resources/                 # API Resources para formatear la salida JSON
│
├── Domain/                        # Capa del Núcleo del Negocio (Domain-Driven)
│   ├── Clientes/                  # Contexto de Clientes
│   │   ├── Models/                # Clientes Eloquent Models
│   │   ├── DTOs/                  # Data Transfer Objects para Clientes
│   │   ├── Actions/               # Acciones (casos de uso de Clientes)
│   │   └── Exceptions/            # Excepciones específicas del dominio
│   ├── Comprobantes/              # Contexto de Facturación
│   │   ├── Models/
│   │   ├── DTOs/
│   │   ├── Actions/
│   │   └── Services/              # Servicios de integración externa (ej: Greenter/SUNAT)
│   └── Inventario/                # Contexto de Stock / Kárdex
│       ├── Models/
│       ├── DTOs/
│       └── Actions/
│
└── Shared/                        # Lógica compartida o utilidades del sistema
```

---

## 🛡️ 2. Reglas de Programación y Buenas Prácticas

### A. Tipado Estricto (Strict Types)
Todos los archivos de clases de PHP (`.php`) deben iniciar con la declaración de tipos estrictos:
```php
<?php

declare(strict_types=1);
```

### B. Controladores Delgados (Thin Controllers)
Los controladores solo deben coordinar la petición:
1. Recibir la petición validada mediante un **Form Request**.
2. Instanciar y convertir los datos de entrada a un **DTO**.
3. Invocar una **Clase de Acción (Action Class)** pasándole el DTO.
4. Devolver la respuesta formateada a través de un **API Resource**.
*Queda terminantemente prohibido incluir consultas Eloquent complejas, lógica tributaria o llamadas directas a SUNAT dentro de los controladores.*

### C. Acciones Transaccionales (Atomicidad)
Cualquier acción de negocio que realice escrituras en múltiples tablas (por ejemplo: emitir un comprobante, lo cual implica registrar la cabecera, los detalles, descontar el stock del producto e insertar en el kárdex) **debe ejecutarse obligatoriamente dentro de una transacción de base de datos** para asegurar la integridad de la información:

```php
use Illuminate\Support\Facades\DB;

public function execute(...): Comprobante
{
    return DB::transaction(function () use ($data) {
        // 1. Guardar cabecera
        // 2. Guardar detalles
        // 3. Descontar stock
        // 4. Registrar Kárdex
    });
}
```

### D. Control de Excepciones del Dominio
No silencies excepciones con bloques `catch` vacíos. Si ocurre un fallo de negocio (ej. "Stock insuficiente para venta" o "Credenciales SOL inválidas"), lanza una excepción personalizada de dominio que extienda de `Exception` y deja que el manejador global de Laravel la capture y la devuelva como una respuesta JSON formateada.

### E. Importaciones Limpias (Sin FQCN Inline)
Queda prohibido utilizar nombres de clases completamente cualificados (FQCN) en línea dentro del código (ej. `\App\Domain\...\Modelo::class` o `\Illuminate\...\HasMany`).
Todas las clases, modelos, interfaces y tipos de retorno deben **importarse explícitamente mediante sentencias `use` al inicio del archivo** para mantener un código limpio, legible y elegante.

---

## 💻 3. Plantillas de Referencia

### A. Form Request (`app/Http/Requests/ComprobanteStoreRequest.php`)
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComprobanteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_comprobante' => ['required', 'string', 'in:01,03'],
            'serie' => ['required', 'string', 'size:4'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_servicio_id' => ['required', 'integer', 'exists:productos_servicios,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
        ];
    }
}
```

### B. Data Transfer Object (`app/Domain/Comprobantes/DTOs/ComprobanteData.php`)
```php
<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\DTOs;

use App\Http\Requests\ComprobanteStoreRequest;

class ComprobanteData
{
    /**
     * @param DetalleData[] $detalles
     */
    public function __construct(
        public readonly int $clienteId,
        public readonly string $tipoComprobante,
        public readonly string $serie,
        public readonly array $detalles,
    ) {}

    public static function fromRequest(ComprobanteStoreRequest $request): self
    {
        $detalles = collect($request->validated('detalles'))
            ->map(fn (array $item) => new DetalleData(
                productoServicioId: $item['producto_servicio_id'],
                cantidad: (float) $item['cantidad']
            ))
            ->toArray();

        return new self(
            clienteId: (int) $request->validated('cliente_id'),
            tipoComprobante: $request->validated('tipo_comprobante'),
            serie: $request->validated('serie'),
            detalles: $detalles
        );
    }
}
```

### C. Action Class con Transacción (`app/Domain/Comprobantes/Actions/EmitirComprobanteAction.php`)
```php
<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Actions;

use App\Domain\Comprobantes\Models\Comprobante;
use App\Domain\Comprobantes\DTOs\ComprobanteData;
use App\Domain\Inventario\Actions\RegistrarSalidaKardexAction;
use App\Domain\Comprobantes\Exceptions\EmissionFailedException;
use Illuminate\Support\Facades\DB;

class EmitirComprobanteAction
{
    public function __construct(
        private RegistrarSalidaKardexAction $registrarSalidaKardex
    ) {}

    public function execute(ComprobanteData $data, int $empresaId): Comprobante
    {
        return DB::transaction(function () use ($data, $empresaId) {
            
            // 1. Obtener y actualizar numeración correlativa
            $numero = $this->obtenerSiguienteCorrelativo($empresaId, $data->tipoComprobante, $data->serie);

            // 2. Crear cabecera de comprobante
            $comprobante = Comprobante::create([
                'empresa_id' => $empresaId,
                'cliente_id' => $data->clienteId,
                'tipo_comprobante' => $data->tipoComprobante,
                'serie' => $data->serie,
                'numero' => $numero,
                'fecha_emision' => now()->toDateString(),
                'estado_sunat' => 'pendiente',
                'total_venta' => 0.00, // Se recalcula con los detalles
            ]);

            // 3. Crear detalles y actualizar inventario
            foreach ($data->detalles as $detalle) {
                // Agregar detalle
                $comprobanteDetalle = $comprobante->detalles()->create([
                    'producto_servicio_id' => $detalle->productoServicioId,
                    'cantidad' => $detalle->cantidad,
                    // Cálculos de precios...
                ]);

                // Registrar salida de kárdex si corresponde
                $this->registrarSalidaKardex->execute(
                    productoId: $detalle->productoServicioId,
                    cantidad: $detalle->cantidad,
                    comprobanteId: $comprobante->id
                );
            }

            return $comprobante;
        });
    }

    private function obtenerSiguienteCorrelativo(int $empresaId, string $tipo, string $serie): int
    {
        // Lógica de bloqueo de fila para evitar duplicidad de numeración
        $serieComprobante = DB::table('series_comprobantes')
            ->where('empresa_id', $empresaId)
            ->where('tipo_comprobante', $tipo)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->first();

        if (!$serieComprobante) {
            throw new EmissionFailedException("La serie {$serie} no está configurada.");
        }

        DB::table('series_comprobantes')
            ->where('id', $serieComprobante->id)
            ->increment('numero_siguiente');

        return $serieComprobante->numero_siguiente;
    }
}
```
