# 🎓 MySQL Connector/NET con C# — Guía Completa de 5 Niveles

> **Driver:** MySql.Data 9.1.0 | **Plataforma:** .NET 6+ | **BD:** MySQL 8.x

---

## 📋 Tabla de Contenidos

- [🔰 Nivel 1 — Novato](#-nivel-1--novato)
- [🟡 Nivel 2 — Intermedio](#-nivel-2--intermedio)
- [🟠 Nivel 3 — Avanzado](#-nivel-3--avanzado)
- [🔵 Nivel 4 — Experto](#-nivel-4--experto)
- [🟣 Nivel 5 — Maestro](#-nivel-5--maestro)

---

## 🔰 Nivel 1 — Novato

### ¿Qué es MySQL Connector/NET?

Es el **driver oficial de Oracle** que permite a aplicaciones .NET comunicarse con bases de datos MySQL. Actúa como un puente entre tu código C# y el servidor MySQL.

```
[ Tu App C# ] ←→ [ MySQL Connector/NET ] ←→ [ Servidor MySQL ]
```

---

### 📦 Paso 1 — Instalación via NuGet

**Package Manager Console (Visual Studio):**
```powershell
Install-Package MySql.Data -Version 9.1.0
```

**.NET CLI:**
```bash
dotnet add package MySql.Data --version 9.1.0
```

**Archivo `.csproj` resultante:**
```xml
<ItemGroup>
  <PackageReference Include="MySql.Data" Version="9.1.0" />
</ItemGroup>
```

---

### 🔗 Paso 2 — La Cadena de Conexión

| Parámetro | Descripción | Ejemplo |
|-----------|-------------|---------|
| `Server` | IP o nombre del host | `localhost` |
| `Port` | Puerto de MySQL | `3306` |
| `Database` | Nombre de la base de datos | `tienda_db` |
| `Uid` | Usuario de MySQL | `root` |
| `Pwd` | Contraseña | `mi_password` |
| `Connect Timeout` | Segundos de espera | `30` |
| `CharSet` | Codificación | `utf8mb4` |

```
Server=localhost;Port=3306;Database=mi_base;Uid=root;Pwd=mi_password;
```

---

### 🗄️ Base de Datos de Ejemplo

```sql
CREATE DATABASE IF NOT EXISTS aprendizaje_db;
USE aprendizaje_db;

CREATE TABLE IF NOT EXISTS productos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    precio      DECIMAL(10,2) NOT NULL,
    stock       INT DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

### 💻 Paso 3 — Código Completo: Abrir y Cerrar Conexión

```csharp
using System;
using MySql.Data.MySqlClient;

namespace Nivel1_Novato
{
    class Program
    {
        // ✅ La cadena de conexión centralizada
        private const string ConnectionString =
            "Server=localhost;" +
            "Port=3306;" +
            "Database=aprendizaje_db;" +
            "Uid=root;" +
            "Pwd=tu_password;" +
            "CharSet=utf8mb4;" +
            "Connect Timeout=30;";

        static void Main(string[] args)
        {
            Console.WriteLine("=== Nivel 1: Conexión con MySQL ===\n");
            ProbarConexion();
            Console.WriteLine("\nPresiona una tecla para salir...");
            Console.ReadKey();
        }

        static void ProbarConexion()
        {
            // 'using' garantiza que la conexión se cierre SIEMPRE,
            // incluso si ocurre una excepción
            using (MySqlConnection conexion = new MySqlConnection(ConnectionString))
            {
                try
                {
                    Console.WriteLine("🔄 Intentando conectar...");

                    // Open() abre físicamente la conexión al servidor
                    conexion.Open();

                    Console.WriteLine($"✅ Conexión exitosa!");
                    Console.WriteLine($"   Estado    : {conexion.State}");
                    Console.WriteLine($"   Servidor  : {conexion.ServerVersion}");
                    Console.WriteLine($"   Base datos: {conexion.Database}");
                    Console.WriteLine($"   DataSource: {conexion.DataSource}");
                }
                catch (MySqlException ex)
                {
                    // MySqlException tiene códigos de error específicos de MySQL
                    // 0    = No se puede conectar al servidor
                    // 1045 = Acceso denegado (usuario/contraseña incorrectos)
                    // 1049 = Base de datos desconocida
                    Console.WriteLine($"❌ Error de MySQL [{ex.Number}]: {ex.Message}");
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"❌ Error inesperado: {ex.Message}");
                }
            }
            // Aquí la conexión ya está cerrada y liberada de memoria
        }
    }
}
```

**Salida esperada:**
```
=== Nivel 1: Conexión con MySQL ===

🔄 Intentando conectar...
✅ Conexión exitosa!
   Estado    : Open
   Servidor  : 8.0.33
   Base datos: aprendizaje_db
   DataSource: localhost
```

---

### 🔍 Explicación paso a paso

```
1. new MySqlConnection(connectionString)
   └─ Crea el objeto de conexión (NO conecta aún al servidor)

2. conexion.Open()
   └─ Establece la conexión física TCP con MySQL
   └─ Autentica usuario y contraseña
   └─ Selecciona la base de datos

3. using (...)  { }
   └─ Llama a conexion.Dispose() automáticamente al salir
   └─ Equivale a llamar conexion.Close() de forma garantizada

4. catch (MySqlException ex)
   └─ Captura errores específicos de MySQL
   └─ ex.Number contiene el código de error MySQL
```

---

### 🏗️ Buena práctica: Separar la configuración

**appsettings.json:**
```json
{
  "ConnectionStrings": {
    "MySQL": "Server=localhost;Port=3306;Database=aprendizaje_db;Uid=root;Pwd=tu_password;CharSet=utf8mb4;"
  }
}
```

```csharp
var connectionString = configuration.GetConnectionString("MySQL");
```

---

### 🎯 Reto del Nivel 1

1. Muestra el **tiempo que tardó en conectarse** usando `Stopwatch` de `System.Diagnostics`
2. Intenta conectarte con una **contraseña incorrecta** y muestra el código de error capturado
3. Crea un método `bool EstaConectado(string connectionString)` que retorne `true` si la conexión fue exitosa

```csharp
// Firma del método a implementar:
static bool EstaConectado(string connectionString)
{
    // Tu código aquí...
}
```

---

## 🟡 Nivel 2 — Intermedio

### Operaciones CRUD con MySQL Connector/NET

CRUD es el acrónimo de las 4 operaciones fundamentales sobre datos:

```
C → CREATE  → INSERT
R → READ    → SELECT
U → UPDATE  → UPDATE
D → DELETE  → DELETE
```

---

### 🗄️ Script SQL de Preparación

```sql
USE aprendizaje_db;

INSERT INTO productos (nombre, precio, stock) VALUES
('Laptop HP', 850.00, 10),
('Mouse Logitech', 25.50, 50),
('Teclado Mecánico', 75.00, 30),
('Monitor 24"', 320.00, 15);
```

---

### 📦 Estructura del Proyecto

```
Nivel2_CRUD/
├── Program.cs
├── Models/
│   └── Producto.cs
└── Repositories/
    └── ProductoRepository.cs
```

---

### 📄 Modelo: Producto.cs

```csharp
namespace Nivel2_CRUD.Models
{
    public class Producto
    {
        public int Id { get; set; }
        public string Nombre { get; set; }
        public decimal Precio { get; set; }
        public int Stock { get; set; }
        public DateTime CreatedAt { get; set; }

        public override string ToString() =>
            $"[{Id}] {Nombre} - ${Precio:F2} (Stock: {Stock})";
    }
}
```

---

### 📄 READ — Leer registros

```csharp
// ✅ C → CREATE (INSERT)
public int Crear(Producto producto)
{
    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();

        // ⚠️ NUNCA hagas esto (SQL Injection):
        // string sql = $"INSERT INTO productos (nombre) VALUES ('{producto.Nombre}')";

        // ✅ SIEMPRE usa parámetros:
        string sql = @"INSERT INTO productos (nombre, precio, stock)
                       VALUES (@nombre, @precio, @stock);
                       SELECT LAST_INSERT_ID();";

        using (var cmd = new MySqlCommand(sql, conexion))
        {
            // Parámetros tipados - protegen contra SQL Injection
            cmd.Parameters.AddWithValue("@nombre", producto.Nombre);
            cmd.Parameters.AddWithValue("@precio", producto.Precio);
            cmd.Parameters.AddWithValue("@stock", producto.Stock);

            // ExecuteScalar retorna el primer campo de la primera fila
            var resultado = cmd.ExecuteScalar();
            return Convert.ToInt32(resultado);
        }
    }
}

// ✅ R → READ (SELECT)
public List<Producto> ObtenerTodos()
{
    var lista = new List<Producto>();

    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();
        string sql = "SELECT id, nombre, precio, stock, created_at FROM productos ORDER BY id";

        using (var cmd = new MySqlCommand(sql, conexion))
        // MySqlDataReader lee fila por fila de forma eficiente (streaming)
        using (var reader = cmd.ExecuteReader())
        {
            while (reader.Read())
            {
                lista.Add(new Producto
                {
                    Id        = reader.GetInt32("id"),
                    Nombre    = reader.GetString("nombre"),
                    Precio    = reader.GetDecimal("precio"),
                    Stock     = reader.GetInt32("stock"),
                    CreatedAt = reader.GetDateTime("created_at")
                });
            }
        }
    }

    return lista;
}

// ✅ READ por ID con parámetro seguro
public Producto ObtenerPorId(int id)
{
    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();
        string sql = "SELECT id, nombre, precio, stock, created_at FROM productos WHERE id = @id";

        using (var cmd = new MySqlCommand(sql, conexion))
        {
            cmd.Parameters.AddWithValue("@id", id);

            using (var reader = cmd.ExecuteReader())
            {
                if (reader.Read())
                {
                    return new Producto
                    {
                        Id        = reader.GetInt32("id"),
                        Nombre    = reader.GetString("nombre"),
                        Precio    = reader.GetDecimal("precio"),
                        Stock     = reader.GetInt32("stock"),
                        CreatedAt = reader.GetDateTime("created_at")
                    };
                }
                return null; // No encontrado
            }
        }
    }
}

// ✅ U → UPDATE
public bool Actualizar(Producto producto)
{
    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();
        string sql = @"UPDATE productos
                       SET nombre = @nombre,
                           precio = @precio,
                           stock  = @stock
                       WHERE id = @id";

        using (var cmd = new MySqlCommand(sql, conexion))
        {
            cmd.Parameters.AddWithValue("@nombre", producto.Nombre);
            cmd.Parameters.AddWithValue("@precio", producto.Precio);
            cmd.Parameters.AddWithValue("@stock", producto.Stock);
            cmd.Parameters.AddWithValue("@id", producto.Id);

            // ExecuteNonQuery retorna el número de filas afectadas
            int filasAfectadas = cmd.ExecuteNonQuery();
            return filasAfectadas > 0;
        }
    }
}

// ✅ D → DELETE
public bool Eliminar(int id)
{
    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();
        string sql = "DELETE FROM productos WHERE id = @id";

        using (var cmd = new MySqlCommand(sql, conexion))
        {
            cmd.Parameters.AddWithValue("@id", id);
            int filasAfectadas = cmd.ExecuteNonQuery();
            return filasAfectadas > 0;
        }
    }
}
```

---

### 🛡️ SQL Injection — Por qué usar parámetros

```csharp
// ❌ VULNERABLE - NUNCA hagas esto
string nombre = "'; DROP TABLE productos; --";
string sqlMalo = $"SELECT * FROM productos WHERE nombre = '{nombre}'";
// Resultado: SELECT * FROM productos WHERE nombre = ''; DROP TABLE productos; --'

// ✅ SEGURO - El parámetro es tratado como dato, nunca como SQL
cmd.Parameters.AddWithValue("@nombre", nombre);
// MySQL lo escapa automáticamente: nombre = '\'; DROP TABLE...'
```

---

### 📄 Program.cs — Demo CRUD completo

```csharp
using System;
using Nivel2_CRUD.Models;
using Nivel2_CRUD.Repositories;

namespace Nivel2_CRUD
{
    class Program
    {
        static void Main(string[] args)
        {
            var repo = new ProductoRepository(
                "Server=localhost;Database=aprendizaje_db;Uid=root;Pwd=tu_password;"
            );

            Console.WriteLine("=== CRUD Completo ===\n");

            // CREATE
            var nuevo = new Producto { Nombre = "SSD Samsung 1TB", Precio = 120.00m, Stock = 25 };
            int nuevoId = repo.Crear(nuevo);
            Console.WriteLine($"✅ Creado con ID: {nuevoId}");

            // READ - todos
            Console.WriteLine("\n📋 Todos los productos:");
            foreach (var p in repo.ObtenerTodos())
                Console.WriteLine($"   {p}");

            // READ - por ID
            var encontrado = repo.ObtenerPorId(nuevoId);
            Console.WriteLine($"\n🔍 Encontrado: {encontrado}");

            // UPDATE
            encontrado.Precio = 99.99m;
            bool actualizado = repo.Actualizar(encontrado);
            Console.WriteLine($"\n✏️  Actualizado: {actualizado}");

            // DELETE
            bool eliminado = repo.Eliminar(nuevoId);
            Console.WriteLine($"\n🗑️  Eliminado: {eliminado}");
        }
    }
}
```

---

### 🔍 Métodos de ejecución de comandos

| Método | Uso | Retorna |
|--------|-----|---------|
| `ExecuteNonQuery()` | INSERT, UPDATE, DELETE | Filas afectadas (int) |
| `ExecuteScalar()` | SELECT de un solo valor | object (primer campo) |
| `ExecuteReader()` | SELECT de múltiples filas | MySqlDataReader |

---

### 🎯 Reto del Nivel 2

1. Agrega un método `BuscarPorNombre(string termino)` que use `LIKE @termino` de forma segura
2. Crea un método `ActualizarStock(int id, int cantidad)` que **sume** la cantidad al stock actual (no lo reemplace)
3. Implementa un método `ContarProductos()` que retorne el total de registros usando `COUNT(*)`

---

## 🟠 Nivel 3 — Avanzado

### Transacciones con MySqlTransaction

Una **transacción** agrupa varias operaciones SQL en una unidad atómica: o todas tienen éxito, o ninguna se aplica.

```
BEGIN TRANSACTION
  ├─ Operación 1 ✅
  ├─ Operación 2 ✅
  └─ Operación 3 ✅
COMMIT ← Solo si TODAS tuvieron éxito

BEGIN TRANSACTION
  ├─ Operación 1 ✅
  ├─ Operación 2 ❌ ERROR
ROLLBACK ← Se deshacen TODAS las operaciones anteriores
```

---

### 🗄️ Script SQL de Preparación

```sql
USE aprendizaje_db;

CREATE TABLE IF NOT EXISTS pedidos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    cliente      VARCHAR(100) NOT NULL,
    total        DECIMAL(10,2) NOT NULL,
    estado       ENUM('pendiente','completado','cancelado') DEFAULT 'pendiente',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS detalle_pedido (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id     INT NOT NULL,
    producto_id   INT NOT NULL,
    cantidad      INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)   REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);
```

---

### 💻 Código: Transacción completa (éxito y error)

```csharp
using System;
using System.Collections.Generic;
using MySql.Data.MySqlClient;

namespace Nivel3_Avanzado
{
    public class PedidoService
    {
        private readonly string _connectionString;

        public PedidoService(string connectionString)
        {
            _connectionString = connectionString;
        }

        // ✅ Ejemplo de transacción exitosa
        public int CrearPedido(string cliente, List<(int ProductoId, int Cantidad)> items)
        {
            using (var conexion = new MySqlConnection(_connectionString))
            {
                conexion.Open();

                // Iniciamos la transacción
                using (MySqlTransaction transaccion = conexion.BeginTransaction())
                {
                    try
                    {
                        decimal total = 0;

                        // Paso 1: Verificar stock y calcular total
                        foreach (var item in items)
                        {
                            string sqlStock = "SELECT precio, stock FROM productos WHERE id = @id FOR UPDATE";
                            using (var cmd = new MySqlCommand(sqlStock, conexion, transaccion))
                            {
                                cmd.Parameters.AddWithValue("@id", item.ProductoId);
                                using (var reader = cmd.ExecuteReader())
                                {
                                    if (!reader.Read())
                                        throw new Exception($"Producto {item.ProductoId} no encontrado.");

                                    int stockActual = reader.GetInt32("stock");
                                    if (stockActual < item.Cantidad)
                                        throw new Exception($"Stock insuficiente para producto {item.ProductoId}. Disponible: {stockActual}");

                                    total += reader.GetDecimal("precio") * item.Cantidad;
                                }
                            }
                        }

                        // Paso 2: Crear el pedido
                        int pedidoId;
                        string sqlPedido = @"INSERT INTO pedidos (cliente, total, estado)
                                             VALUES (@cliente, @total, 'pendiente');
                                             SELECT LAST_INSERT_ID();";

                        using (var cmd = new MySqlCommand(sqlPedido, conexion, transaccion))
                        {
                            cmd.Parameters.AddWithValue("@cliente", cliente);
                            cmd.Parameters.AddWithValue("@total", total);
                            pedidoId = Convert.ToInt32(cmd.ExecuteScalar());
                        }

                        // Paso 3: Insertar detalles y descontar stock
                        foreach (var item in items)
                        {
                            // Insertar detalle
                            string sqlDetalle = @"INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario)
                                                  SELECT @pedidoId, @prodId, @cantidad, precio
                                                  FROM productos WHERE id = @prodId";

                            using (var cmd = new MySqlCommand(sqlDetalle, conexion, transaccion))
                            {
                                cmd.Parameters.AddWithValue("@pedidoId", pedidoId);
                                cmd.Parameters.AddWithValue("@prodId", item.ProductoId);
                                cmd.Parameters.AddWithValue("@cantidad", item.Cantidad);
                                cmd.ExecuteNonQuery();
                            }

                            // Descontar stock
                            string sqlStock = "UPDATE productos SET stock = stock - @cantidad WHERE id = @id";
                            using (var cmd = new MySqlCommand(sqlStock, conexion, transaccion))
                            {
                                cmd.Parameters.AddWithValue("@cantidad", item.Cantidad);
                                cmd.Parameters.AddWithValue("@id", item.ProductoId);
                                cmd.ExecuteNonQuery();
                            }
                        }

                        // ✅ Todo salió bien: confirmamos la transacción
                        transaccion.Commit();
                        Console.WriteLine($"✅ Pedido #{pedidoId} creado correctamente. Total: ${total:F2}");
                        return pedidoId;
                    }
                    catch (Exception ex)
                    {
                        // ❌ Algo falló: deshacemos TODO
                        transaccion.Rollback();
                        Console.WriteLine($"❌ Transacción revertida: {ex.Message}");
                        throw; // Re-lanzamos para que el llamador sepa del error
                    }
                }
            }
        }
    }
}
```

---

### 💻 Código: Stored Procedures desde C#

**Primero, el stored procedure en MySQL:**

```sql
DELIMITER //

CREATE PROCEDURE ObtenerProductosPorPrecio(
    IN  precio_min DECIMAL(10,2),
    IN  precio_max DECIMAL(10,2),
    OUT total_encontrados INT
)
BEGIN
    SELECT id, nombre, precio, stock
    FROM productos
    WHERE precio BETWEEN precio_min AND precio_max
    ORDER BY precio ASC;

    -- Parámetro de salida
    SELECT COUNT(*) INTO total_encontrados
    FROM productos
    WHERE precio BETWEEN precio_min AND precio_max;
END //

DELIMITER ;
```

**Llamando al stored procedure desde C#:**

```csharp
public void EjecutarStoredProcedure(decimal precioMin, decimal precioMax)
{
    using (var conexion = new MySqlConnection(_connectionString))
    {
        conexion.Open();

        using (var cmd = new MySqlCommand("ObtenerProductosPorPrecio", conexion))
        {
            // ⚠️ CRÍTICO: indicar que es un stored procedure
            cmd.CommandType = System.Data.CommandType.StoredProcedure;

            // Parámetros de ENTRADA
            cmd.Parameters.AddWithValue("@precio_min", precioMin);
            cmd.Parameters["@precio_min"].Direction = System.Data.ParameterDirection.Input;

            cmd.Parameters.AddWithValue("@precio_max", precioMax);
            cmd.Parameters["@precio_max"].Direction = System.Data.ParameterDirection.Input;

            // Parámetro de SALIDA
            cmd.Parameters.Add(new MySqlParameter("@total_encontrados", MySqlDbType.Int32));
            cmd.Parameters["@total_encontrados"].Direction = System.Data.ParameterDirection.Output;

            // Ejecutar y leer resultados
            using (var reader = cmd.ExecuteReader())
            {
                Console.WriteLine("\n📋 Productos encontrados:");
                while (reader.Read())
                {
                    Console.WriteLine($"   [{reader.GetInt32("id")}] {reader.GetString("nombre")} - ${reader.GetDecimal("precio"):F2}");
                }
            }

            // Leer el parámetro de salida DESPUÉS de cerrar el reader
            int total = Convert.ToInt32(cmd.Parameters["@total_encontrados"].Value);
            Console.WriteLine($"\n📊 Total de productos encontrados: {total}");
        }
    }
}
```

---

### 📄 Program.cs — Demo Nivel 3

```csharp
class Program
{
    static void Main(string[] args)
    {
        string cs = "Server=localhost;Database=aprendizaje_db;Uid=root;Pwd=tu_password;";
        var service = new PedidoService(cs);

        Console.WriteLine("=== Nivel 3: Transacciones y Stored Procedures ===\n");

        // Caso 1: Transacción exitosa
        try
        {
            var items = new List<(int, int)> { (1, 2), (2, 3) }; // ProductoId, Cantidad
            service.CrearPedido("Juan Pérez", items);
        }
        catch { }

        // Caso 2: Transacción con error (stock insuficiente)
        try
        {
            var items = new List<(int, int)> { (1, 9999) }; // Stock insuficiente
            service.CrearPedido("Ana García", items);
        }
        catch { }

        // Caso 3: Stored Procedure
        service.EjecutarStoredProcedure(20.00m, 200.00m);
    }
}
```

---

### 🎯 Reto del Nivel 3

1. Crea un stored procedure `ActualizarEstadoPedido(IN pedido_id INT, IN nuevo_estado VARCHAR(20))` y llámalo desde C#
2. Implementa un método `TransferirStock(int productoOrigenId, int productoDestinoId, int cantidad)` que use transacción
3. Agrega un punto de guardado con `MySqlTransaction.Save("punto1")` y prueba un rollback parcial

---

## 🔵 Nivel 4 — Experto

### Repository Pattern + Async/Await

El **Repository Pattern** es un patrón de arquitectura que separa la lógica de acceso a datos del resto de la aplicación.

```
[ Controlador / Servicio ]
         │
         ▼
  [ IProductoRepository ]  ← Contrato (interfaz)
         │
         ▼
  [ ProductoRepository ]   ← Implementación concreta con MySQL
         │
         ▼
    [ MySQL Server ]
```

---

### 📦 Estructura del Proyecto

```
Nivel4_Experto/
├── Program.cs
├── Models/
│   └── Producto.cs
├── Repositories/
│   ├── IProductoRepository.cs   ← Interfaz (contrato)
│   └── ProductoRepository.cs   ← Implementación
└── Services/
    └── ProductoService.cs
```

---

### 📄 Interfaz: IProductoRepository.cs

```csharp
using System.Collections.Generic;
using System.Threading.Tasks;
using Nivel4_Experto.Models;

namespace Nivel4_Experto.Repositories
{
    // El contrato define QUÉ se puede hacer, no CÓMO
    public interface IProductoRepository
    {
        Task<IEnumerable<Producto>> ObtenerTodosAsync();
        Task<Producto>              ObtenerPorIdAsync(int id);
        Task<int>                   CrearAsync(Producto producto);
        Task<bool>                  ActualizarAsync(Producto producto);
        Task<bool>                  EliminarAsync(int id);
        Task<IEnumerable<Producto>> BuscarPorNombreAsync(string termino);
    }
}
```

---

### 📄 Implementación: ProductoRepository.cs (Async)

```csharp
using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using Nivel4_Experto.Models;

namespace Nivel4_Experto.Repositories
{
    public class ProductoRepository : IProductoRepository
    {
        private readonly string _connectionString;

        public ProductoRepository(string connectionString)
        {
            _connectionString = connectionString
                ?? throw new ArgumentNullException(nameof(connectionString));
        }

        // ✅ Método helper para crear conexiones (evita repetición)
        private MySqlConnection CrearConexion() =>
            new MySqlConnection(_connectionString);

        // ✅ Leer todos - ASYNC
        public async Task<IEnumerable<Producto>> ObtenerTodosAsync()
        {
            var lista = new List<Producto>();

            // 'await using' = versión async del 'using' estándar
            await using var conexion = CrearConexion();
            await conexion.OpenAsync(); // ← No bloquea el hilo

            const string sql = "SELECT id, nombre, precio, stock, created_at FROM productos ORDER BY id";

            await using var cmd = new MySqlCommand(sql, conexion);
            await using var reader = await cmd.ExecuteReaderAsync();

            while (await reader.ReadAsync()) // ← Lee cada fila sin bloquear
            {
                lista.Add(MapearProducto(reader));
            }

            return lista;
        }

        // ✅ Leer por ID - ASYNC
        public async Task<Producto> ObtenerPorIdAsync(int id)
        {
            await using var conexion = CrearConexion();
            await conexion.OpenAsync();

            const string sql = @"SELECT id, nombre, precio, stock, created_at
                                  FROM productos WHERE id = @id";

            await using var cmd = new MySqlCommand(sql, conexion);
            cmd.Parameters.AddWithValue("@id", id);

            await using var reader = await cmd.ExecuteReaderAsync();
            return await reader.ReadAsync() ? MapearProducto(reader) : null;
        }

        // ✅ Crear - ASYNC
        public async Task<int> CrearAsync(Producto producto)
        {
            await using var conexion = CrearConexion();
            await conexion.OpenAsync();

            const string sql = @"INSERT INTO productos (nombre, precio, stock)
                                  VALUES (@nombre, @precio, @stock);
                                  SELECT LAST_INSERT_ID();";

            await using var cmd = new MySqlCommand(sql, conexion);
            AgregarParametros(cmd, producto);

            var resultado = await cmd.ExecuteScalarAsync();
            return Convert.ToInt32(resultado);
        }

        // ✅ Actualizar - ASYNC
        public async Task<bool> ActualizarAsync(Producto producto)
        {
            await using var conexion = CrearConexion();
            await conexion.OpenAsync();

            const string sql = @"UPDATE productos
                                  SET nombre = @nombre, precio = @precio, stock = @stock
                                  WHERE id = @id";

            await using var cmd = new MySqlCommand(sql, conexion);
            AgregarParametros(cmd, producto);
            cmd.Parameters.AddWithValue("@id", producto.Id);

            int filas = await cmd.ExecuteNonQueryAsync();
            return filas > 0;
        }

        // ✅ Eliminar - ASYNC
        public async Task<bool> EliminarAsync(int id)
        {
            await using var conexion = CrearConexion();
            await conexion.OpenAsync();

            await using var cmd = new MySqlCommand(
                "DELETE FROM productos WHERE id = @id", conexion);
            cmd.Parameters.AddWithValue("@id", id);

            int filas = await cmd.ExecuteNonQueryAsync();
            return filas > 0;
        }

        // ✅ Buscar por nombre - ASYNC con LIKE
        public async Task<IEnumerable<Producto>> BuscarPorNombreAsync(string termino)
        {
            var lista = new List<Producto>();

            await using var conexion = CrearConexion();
            await conexion.OpenAsync();

            const string sql = @"SELECT id, nombre, precio, stock, created_at
                                  FROM productos
                                  WHERE nombre LIKE @termino
                                  ORDER BY nombre";

            await using var cmd = new MySqlCommand(sql, conexion);
            // El '%' va en el valor del parámetro, nunca en el SQL
            cmd.Parameters.AddWithValue("@termino", $"%{termino}%");

            await using var reader = await cmd.ExecuteReaderAsync();
            while (await reader.ReadAsync())
                lista.Add(MapearProducto(reader));

            return lista;
        }

        // ✅ Métodos privados helper (evitan duplicación de código)
        private static Producto MapearProducto(System.Data.Common.DbDataReader reader) =>
            new Producto
            {
                Id        = reader.GetInt32("id"),
                Nombre    = reader.GetString("nombre"),
                Precio    = reader.GetDecimal("precio"),
                Stock     = reader.GetInt32("stock"),
                CreatedAt = reader.GetDateTime("created_at")
            };

        private static void AgregarParametros(MySqlCommand cmd, Producto p)
        {
            cmd.Parameters.AddWithValue("@nombre", p.Nombre);
            cmd.Parameters.AddWithValue("@precio", p.Precio);
            cmd.Parameters.AddWithValue("@stock", p.Stock);
        }
    }
}
```

---

### 📄 Servicio: ProductoService.cs

```csharp
using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Nivel4_Experto.Models;
using Nivel4_Experto.Repositories;

namespace Nivel4_Experto.Services
{
    // El servicio usa la INTERFAZ, no la implementación concreta
    // Esto facilita pruebas unitarias y cambiar de base de datos
    public class ProductoService
    {
        private readonly IProductoRepository _repo;

        public ProductoService(IProductoRepository repo)
        {
            _repo = repo;
        }

        public async Task<IEnumerable<Producto>> ObtenerCatalogo()
        {
            return await _repo.ObtenerTodosAsync();
        }

        public async Task<int> AgregarProducto(string nombre, decimal precio, int stock)
        {
            if (string.IsNullOrWhiteSpace(nombre))
                throw new ArgumentException("El nombre no puede estar vacío.");
            if (precio <= 0)
                throw new ArgumentException("El precio debe ser mayor a 0.");

            var producto = new Producto { Nombre = nombre, Precio = precio, Stock = stock };
            return await _repo.CrearAsync(producto);
        }
    }
}
```

---

### 📄 Program.cs — Demo Nivel 4

```csharp
using System;
using System.Threading.Tasks;
using Nivel4_Experto.Repositories;
using Nivel4_Experto.Services;

namespace Nivel4_Experto
{
    class Program
    {
        static async Task Main(string[] args)
        {
            string cs = "Server=localhost;Database=aprendizaje_db;Uid=root;Pwd=tu_password;";

            // Inyectamos la dependencia manualmente (en producción usarías DI Container)
            IProductoRepository repo    = new ProductoRepository(cs);
            var                 service = new ProductoService(repo);

            Console.WriteLine("=== Nivel 4: Repository Pattern + Async ===\n");

            // Crear producto
            int id = await service.AgregarProducto("Webcam HD", 45.99m, 20);
            Console.WriteLine($"✅ Producto creado con ID: {id}");

            // Leer catálogo
            var catalogo = await service.ObtenerCatalogo();
            Console.WriteLine("\n📋 Catálogo:");
            foreach (var p in catalogo)
                Console.WriteLine($"   {p}");

            // Buscar
            var resultados = await repo.BuscarPorNombreAsync("laptop");
            Console.WriteLine($"\n🔍 Búsqueda 'laptop': {string.Join(", ", resultados)}");
        }
    }
}
```

---

### 🏗️ Por qué usar Repository Pattern

| Sin Repository | Con Repository |
|----------------|----------------|
| SQL mezclado con lógica de negocio | Separación clara de responsabilidades |
| Difícil de testear | Fácil de mockear en pruebas unitarias |
| Cambiar de BD requiere modificar todo | Solo cambia la implementación del repositorio |
| Código duplicado | Lógica centralizada |

---

### 🎯 Reto del Nivel 4

1. Agrega `CancellationToken` a todos los métodos async para soportar cancelación
2. Crea una interfaz `IRepository<T>` genérica con los métodos CRUD básicos
3. Implementa `ProductoRepository` usando inyección de dependencias con `Microsoft.Extensions.DependencyInjection`

---

## 🟣 Nivel 5 — Maestro

### Connection Pooling, Manejo de Errores y Logging

---

### ⚡ Connection Pooling

El **pooling de conexiones** mantiene un conjunto de conexiones MySQL reutilizables, evitando el costo de crear una nueva conexión en cada petición.

```
Sin Pooling:
Petición → Abrir conexión TCP → Autenticar → Query → Cerrar conexión → Repetir

Con Pooling:
Petición → Tomar del pool → Query → Devolver al pool (reutilizable)
```

**Configuración en la connection string:**

```csharp
// Configuración completa de Connection Pooling
private static string BuildConnectionString()
{
    var builder = new MySqlConnectionStringBuilder
    {
        Server            = "localhost",
        Port              = 3306,
        Database          = "aprendizaje_db",
        UserID            = "root",
        Password          = "tu_password",
        CharacterSet      = "utf8mb4",

        // ✅ Pooling
        Pooling           = true,         // Habilitar pooling (default: true)
        MinimumPoolSize   = 5,            // Conexiones mínimas en el pool
        MaximumPoolSize   = 100,          // Conexiones máximas permitidas
        ConnectionTimeout = 30,           // Segundos para obtener una conexión del pool
        ConnectionLifeTime = 300,         // Segundos antes de destruir una conexión inactiva

        // ✅ Resiliencia
        ConnectionReset   = true,         // Resetear estado de la conexión al devolverla al pool
        AllowPublicKeyRetrieval = true,   // Necesario en algunas configuraciones MySQL 8+
        SslMode           = MySqlSslMode.Preferred,
    };

    return builder.ConnectionString;
}
```

---

### 🛡️ Manejo de Errores en Producción

```csharp
using System;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using Microsoft.Extensions.Logging;

namespace Nivel5_Maestro
{
    public class ResilientRepository
    {
        private readonly string _connectionString;
        private readonly ILogger<ResilientRepository> _logger;

        // Códigos de error MySQL relevantes
        private const int ERROR_DUPLICADO        = 1062; // Duplicate entry
        private const int ERROR_FK_VIOLACION     = 1451; // FK constraint
        private const int ERROR_TABLA_NO_EXISTE  = 1146; // Table doesn't exist
        private const int ERROR_ACCESO_DENEGADO  = 1045; // Access denied
        private const int ERROR_CONEXION_PERDIDA = 2006; // MySQL server has gone away

        public ResilientRepository(string connectionString, ILogger<ResilientRepository> logger)
        {
            _connectionString = connectionString;
            _logger = logger;
        }

        public async Task<Producto> ObtenerProductoSeguroAsync(int id)
        {
            try
            {
                _logger.LogDebug("Consultando producto ID: {ProductoId}", id);

                await using var conexion = new MySqlConnection(_connectionString);
                await conexion.OpenAsync();

                const string sql = "SELECT id, nombre, precio, stock FROM productos WHERE id = @id";
                await using var cmd = new MySqlCommand(sql, conexion);
                cmd.Parameters.AddWithValue("@id", id);

                await using var reader = await cmd.ExecuteReaderAsync();

                if (await reader.ReadAsync())
                {
                    var producto = new Producto
                    {
                        Id     = reader.GetInt32("id"),
                        Nombre = reader.GetString("nombre"),
                        Precio = reader.GetDecimal("precio"),
                        Stock  = reader.GetInt32("stock")
                    };

                    _logger.LogInformation("Producto encontrado: {Nombre}", producto.Nombre);
                    return producto;
                }

                _logger.LogWarning("Producto ID {ProductoId} no encontrado", id);
                return null;
            }
            catch (MySqlException ex) when (ex.Number == ERROR_CONEXION_PERDIDA)
            {
                _logger.LogError(ex, "Conexión perdida con MySQL. Verificar servidor.");
                throw new DatabaseConnectionException("Se perdió la conexión con la base de datos.", ex);
            }
            catch (MySqlException ex) when (ex.Number == ERROR_ACCESO_DENEGADO)
            {
                _logger.LogCritical(ex, "Credenciales de MySQL inválidas. Verificar configuración.");
                throw new DatabaseAuthException("Error de autenticación en la base de datos.", ex);
            }
            catch (MySqlException ex)
            {
                _logger.LogError(ex, "Error MySQL [{ErrorCode}]: {Message}", ex.Number, ex.Message);
                throw new DatabaseException($"Error de base de datos: {ex.Message}", ex);
            }
            catch (TimeoutException ex)
            {
                _logger.LogWarning(ex, "Timeout al consultar producto ID: {ProductoId}", id);
                throw;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error inesperado al obtener producto ID: {ProductoId}", id);
                throw;
            }
        }

        // ✅ Retry con backoff exponencial para errores transitorios
        public async Task<T> EjecutarConRetryAsync<T>(
            Func<Task<T>> operacion,
            int maxIntentos = 3)
        {
            int intentos = 0;

            while (true)
            {
                try
                {
                    intentos++;
                    return await operacion();
                }
                catch (MySqlException ex) when (EsErrorTransitorio(ex) && intentos < maxIntentos)
                {
                    int espera = (int)Math.Pow(2, intentos) * 1000; // 2s, 4s, 8s
                    _logger.LogWarning(
                        "Intento {Intento}/{Max} falló. Reintentando en {Espera}ms. Error: {Error}",
                        intentos, maxIntentos, espera, ex.Message);

                    await Task.Delay(espera);
                }
            }
        }

        private static bool EsErrorTransitorio(MySqlException ex) =>
            ex.Number == ERROR_CONEXION_PERDIDA ||
            ex.Number == 1213 || // Deadlock
            ex.Number == 1205;   // Lock wait timeout

        // Excepciones personalizadas
        public class DatabaseException : Exception
        {
            public DatabaseException(string message, Exception inner) : base(message, inner) { }
        }
        public class DatabaseConnectionException : DatabaseException
        {
            public DatabaseConnectionException(string message, Exception inner) : base(message, inner) { }
        }
        public class DatabaseAuthException : DatabaseException
        {
            public DatabaseAuthException(string message, Exception inner) : base(message, inner) { }
        }
    }
}
```

---

### 📊 Logging y Diagnóstico

```csharp
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.DependencyInjection;

// Configuración del sistema de logging
var services = new ServiceCollection();

services.AddLogging(builder =>
{
    builder
        .SetMinimumLevel(LogLevel.Debug)
        .AddConsole()        // Logs a consola
        .AddDebug()          // Logs al debug output
        // En producción: .AddApplicationInsights() o .AddSerilog()
        .AddFilter("MySql.Data", LogLevel.Warning); // Filtrar logs verbosos del driver
});

// Monitoreo del pool de conexiones
public static class ConnectionPoolMonitor
{
    public static void LogEstadoPool(ILogger logger)
    {
        // MySql.Data expone estadísticas del pool
        var stats = MySqlConnection.GetConnectionStats();

        logger.LogInformation(
            "Pool Stats → Activas: {Active} | Inactivas: {Idle} | Total: {Total}",
            stats.ActiveConnections,
            stats.IdleConnections,
            stats.TotalConnections
        );
    }
}
```

---

### ✅ Checklist de Buenas Prácticas en Producción

```
CONFIGURACIÓN
 ✅ Connection string en variables de entorno o Azure Key Vault (nunca en código)
 ✅ Usar MySqlConnectionStringBuilder para construir la cadena
 ✅ Habilitar SSL en producción (SslMode = Required)
 ✅ Configurar MinimumPoolSize y MaximumPoolSize según carga esperada

CÓDIGO
 ✅ Siempre usar 'using' o 'await using' para conexiones y comandos
 ✅ Siempre usar parámetros (nunca concatenar SQL)
 ✅ Implementar Repository Pattern para separar acceso a datos
 ✅ Usar async/await en APIs y aplicaciones web
 ✅ Agregar CancellationToken a métodos async

ERRORES Y RESILIENCIA
 ✅ Capturar MySqlException por código de error (ex.Number)
 ✅ Implementar retry con backoff exponencial para errores transitorios
 ✅ Crear excepciones personalizadas del dominio (no exponer MySqlException)
 ✅ Nunca exponer detalles internos de BD al cliente/usuario final

LOGGING
 ✅ Loguear todas las operaciones críticas con nivel apropiado
 ✅ Incluir contexto en los logs (IDs, operación, duración)
 ✅ Usar ILogger (no Console.WriteLine) para facilitar integración con herramientas
 ✅ Monitorear estado del connection pool en producción

SEGURIDAD
 ✅ Usar usuario de BD con permisos mínimos (no root en producción)
 ✅ Rotar contraseñas periódicamente
 ✅ Auditar queries lentas habilitando slow query log en MySQL
 ✅ Validar y sanitizar inputs ANTES de llegar al repositorio
```

---

### 📄 Program.cs — Demo Nivel 5 (configuración completa)

```csharp
using System;
using System.Threading.Tasks;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using MySql.Data.MySqlClient;

namespace Nivel5_Maestro
{
    class Program
    {
        static async Task Main(string[] args)
        {
            // Configurar DI Container
            var services = new ServiceCollection();

            services.AddLogging(b => b.AddConsole().SetMinimumLevel(LogLevel.Debug));
            services.AddSingleton(BuildConnectionString());
            services.AddScoped<ResilientRepository>();

            var provider = services.BuildServiceProvider();
            var logger   = provider.GetRequiredService<ILogger<Program>>();
            var repo     = provider.GetRequiredService<ResilientRepository>();

            logger.LogInformation("=== Nivel 5: Maestro ===");

            // Uso con retry automático
            var producto = await repo.EjecutarConRetryAsync(
                () => repo.ObtenerProductoSeguroAsync(1)
            );

            if (producto != null)
                logger.LogInformation("Producto: {Nombre} - ${Precio}", producto.Nombre, producto.Precio);
        }

        static string BuildConnectionString()
        {
            return new MySqlConnectionStringBuilder
            {
                Server           = Environment.GetEnvironmentVariable("MYSQL_SERVER") ?? "localhost",
                Port             = 3306,
                Database         = "aprendizaje_db",
                UserID           = Environment.GetEnvironmentVariable("MYSQL_USER") ?? "root",
                Password         = Environment.GetEnvironmentVariable("MYSQL_PASSWORD") ?? "tu_password",
                Pooling          = true,
                MinimumPoolSize  = 5,
                MaximumPoolSize  = 100,
                ConnectionLifeTime = 300,
                CharacterSet     = "utf8mb4",
            }.ConnectionString;
        }
    }
}
```

---

### 🎯 Reto Final del Nivel 5

1. Configura un **Health Check** que verifique si MySQL está disponible y responda en menos de 500ms
2. Implementa un **Circuit Breaker** manual: si hay 3 errores consecutivos, espera 30 segundos antes de reintentar
3. Crea un **middleware de logging** que mida el tiempo de cada query y loguee las que tarden más de 1 segundo como `Warning`

---

## 🏆 Resumen — Lo que dominaste

| Nivel | Habilidades |
|-------|-------------|
| 🔰 Novato | Instalación, connection string, abrir/cerrar conexión |
| 🟡 Intermedio | CRUD completo, parámetros seguros, prevención SQL Injection |
| 🟠 Avanzado | Transacciones, commit/rollback, stored procedures |
| 🔵 Experto | Repository Pattern, async/await, arquitectura en capas |
| 🟣 Maestro | Connection Pooling, resiliencia, logging, buenas prácticas productivas |

---

## 📚 Recursos Adicionales

- [Documentación oficial MySQL Connector/NET](https://dev.mysql.com/doc/connector-net/en/)
- [NuGet MySql.Data](https://www.nuget.org/packages/MySql.Data)
- [MySQL Error Codes Reference](https://dev.mysql.com/doc/mysql-errors/8.0/en/server-error-reference.html)
- [Microsoft Docs — async/await en C#](https://docs.microsoft.com/es-es/dotnet/csharp/programming-guide/concepts/async/)

---

*Generado para aprendizaje progresivo de MySQL Connector/NET 9.1.0 con C# (.NET 6+)*
