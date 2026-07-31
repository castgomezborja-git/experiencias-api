# Experiencias API

API para la gestión de experiencias, sesiones y reservas, desarrollada con PHP 8.5 y Symfony, siguiendo **Domain-Driven Design** y **arquitectura hexagonal**.

## Requisitos técnicos

- PHP 8.2+
- Composer
- Docker (MySQL 8.0)
- Symfony 8

## Puesta en marcha

```bash
composer install

# Base de datos (Docker)
docker run --name experiencias-mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=experiencias -p 3306:3306 -d mysql:8.0

# .env.local
DATABASE_URL="mysql://root:root@127.0.0.1:3306/experiencias?serverVersion=8.0"

php bin/console doctrine:migrations:migrate

# Arrancar servidor
symfony server:start
# o
php -S 127.0.0.1:8000 -t public
```

### Tests

```bash
# Base de datos de test (aislada de la de desarrollo)
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction

php bin/phpunit
```

## Arquitectura

El proyecto está organizado por **subdominios** (bounded contexts), no por capa técnica, siguiendo arquitectura hexagonal en cada uno:

src/
    Experience/ → gestión de experiencias y sesiones
        Domain/ → entidades, value objects, interfaces de repositorio (puertos)
        Application/ → casos de uso, DTOs
        Infrastructure/ → implementaciones Doctrine, tipos custom
        Presentation/ → controladores HTTP
    Reservation/ → gestión de reservas
        Domain/ 
        Application/ 
        Infrastructure/ 
        Presentation/
    Shared/
        Domain/
        ValueObject/ → value objects genéricos reutilizables entre contextos

El dominio (`Domain/`) no depende de Symfony ni de Doctrine en ningún punto — las interfaces de repositorio son puertos definidos en el dominio, implementados en `Infrastructure/`.

### Agregados

- **Experience**: agregado independiente. Solo contiene sus propios datos (título, descripción, referencia al proveedor).
- **Session**: agregado independiente, **no** anidado dentro de `Experience`. Referencia a su experiencia solo por `ExperienceId` (no por objeto), siguiendo la regla de que los agregados se referencian entre sí por identidad. Esto evita cargar el grafo completo de una experiencia con todas sus sesiones para operar sobre una sola.
- **Reservation**: vive dentro del agregado `Session` conceptualmente (la invariante de aforo depende de todas las reservas activas de una sesión), pero está implementada como su propio módulo (`src/Reservation`) por separación de responsabilidades y volumen de código. El aforo se calcula como valor derivado (suma de plazas de reservas `confirmed`), no como contador mutable en `Session` — evita estado duplicado que se pueda desincronizar.

### Decisión de diseño: `SessionId` compartido entre módulos

`Reservation` reutiliza directamente `App\Experience\Domain\Model\ValueObject\SessionId` en vez de duplicar el value object en su propio contexto. Es una decisión pragmática dado el alcance de la prueba y al no haber equipos trabajando en paralelo sobre cada módulo. En un sistema real con bounded contexts gestionados por equipos distintos, optaría por duplicar el VO en cada contexto para evitar acoplamiento entre módulos.

### Control de concurrencia (aforo)

La regla de negocio más sensible del enunciado ("sesiones que agotan sus plazas en minutos, mucha gente reservando a la vez") se resuelve con **bloqueo pesimista a nivel de fila** (`SELECT ... FOR UPDATE`) sobre la `Session`, dentro de una transacción explícita, en `CreateReservationUseCase`:

1. Se abre una transacción.
2. Se carga la `Session` con lock pesimista — cualquier otra petición concurrente sobre la misma sesión espera aquí.
3. Se calcula el aforo ya ocupado (suma de plazas de reservas `confirmed`).
4. Se valida que la nueva reserva no supere el aforo.
5. Se guarda la reserva y se hace commit (liberando el lock).

Si cualquier paso falla, se hace `rollback()` explícito para no dejar la fila bloqueada. Este mecanismo garantiza que dos peticiones simultáneas para la misma sesión se procesan de forma secuencial, evitando overbooking. Sesiones distintas no se bloquean entre sí.

La cancelación de reservas **no** usa lock, porque no hay contención real sobre un recurso compartido: la propia entidad `Reservation` protege el invariante de "no cancelar dos veces" a nivel de objeto.

### Value Objects como tipos Doctrine custom

Los identificadores (`ExperienceId`, `SessionId`, `ReservationId`, `ProviderId`, `UserId`) son Value Objects, no strings planos, para evitar *primitive obsession* y detectar errores de tipo en desarrollo. Se persisten mediante tipos custom de Doctrine DBAL (`Infrastructure/Persistence/Type/`), manteniendo el mapping ORM en XML externo (no atributos en las entidades), para que el dominio no tenga ninguna dependencia de Doctrine.

### Notificaciones

Se define un puerto `NotificationSender` en el dominio de `Reservation`, implementado en `Infrastructure` mediante un logger (no se envía email real, tal como indica el enunciado). Se invoca al confirmar y al cancelar una reserva.

## Reglas de negocio implementadas

- Aforo máximo por sesión, protegido con bloqueo pesimista.
- No se puede crear una sesión duplicada (misma experiencia, mismo día).
- No se puede crear una sesión en fecha pasada.
- No se puede reservar una sesión ya iniciada.
- No se puede cancelar una reserva menos de 24h antes del inicio de la sesión.
- No se puede cancelar una reserva ya cancelada.
- El precio total de la reserva se calcula en servidor (plazas × precio de sesión), nunca se confía en un valor recibido del cliente.

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/experiences` | Registrar una experiencia |
| POST | `/sessions` | Crear una sesión para una experiencia |
| POST | `/reservations` | Reservar plazas para una sesión |
| PATCH | `/reservations/{reservationId}/cancel` | Cancelar una reserva |

## Limitaciones conocidas / decisiones fuera de alcance

- No hay autenticación, tal como especifica el enunciado — los IDs de proveedor y usuario se aceptan tal cual llegan en la petición, sin validarse contra ningún sistema externo.
- El email de notificación usa el `userId` como placeholder de destinatario, ya que no se modela un Usuario con email real (fuera del alcance de la prueba).
- No hay test funcional de "reservar sesión ya iniciada", porque `Session::schedule()` prohíbe crear sesiones en el pasado por diseño (regla de negocio correcta) — no se ha forzado el escenario con datos artificiales para no comprometer la validez del test. La regla está cubierta a nivel unitario (`SessionTest::test_has_started_returns_true_for_past_session`).
- La comprobación de "sesión duplicada el mismo día" no usa lock (a diferencia del aforo), ya que el enunciado solo exige robustez ante concurrencia en las reservas. Con más tiempo, se reforzaría con un índice único `(experience_id, date)` en base de datos.