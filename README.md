# BuzzDM

Sistema de gestión empresarial desarrollado en PHP.

---

## 📑 Índice

- [📋 Requisitos Previos](#-requisitos-previos)
- [🔧 Instalación de Composer en Windows](#-instalación-de-composer-en-windows)
  - [Opción 1: Instalador de Windows (Recomendado)](#opción-1-instalador-de-windows-recomendado)
- [📦 Instalación de Dependencias](#-instalación-de-dependencias)
  - [Paso 1: Habilitar extensiones de PHP](#paso-1-habilitar-extensiones-de-php)
  - [Paso 2: Instalar las dependencias](#paso-2-instalar-las-dependencias)
  - [Paso 3: Verificar la instalación](#paso-3-verificar-la-instalación)
- [📚 Dependencias del Proyecto](#-dependencias-del-proyecto)
  - [Dependencias de desarrollo](#dependencias-de-desarrollo)
- [⚠️ Solución de Problemas](#️-solución-de-problemas)
- [🚀 Ejecutar la Aplicación](#-ejecutar-la-aplicación)
- [Como instalar MariaDB y conectarla a HeidiSQL](#como-instalar-mariadb-y-conectarla-a-heidisql)
  - [Parte 1: Descargar MariaDB](#parte-1-descargar-mariadb)
  - [Parte 2: Instalar MariaDB](#parte-2-instalar-mariadb)
  - [Parte 3: Verificar que MariaDB está Ejecutándose](#parte-3-verificar-que-mariadb-está-ejecutándose)
  - [Parte 4: Conectar con HeidiSQL](#parte-4-conectar-con-heidisql)
  - [Solución de Problemas de MariaDB](#solución-de-problemas-de-mariadb)

---

## 📋 Requisitos Previos

- **XAMPP** (o cualquier servidor con PHP 8.2+, que es el mínimo que exige `composer.json`)
- **Composer** (gestor de dependencias de PHP)

---

## 🔧 Instalación de Composer en Windows

### Opción 1: Instalador de Windows (Recomendado)

1. Descarga el instalador desde: **https://getcomposer.org/Composer-Setup.exe**

2. Ejecuta el instalador `Composer-Setup.exe`

3. Cuando te pregunte por la ruta de PHP, selecciona:
   ```
   C:\xampp\php\php.exe
   ```

4. Completa el asistente de instalación

5. **Cierra y vuelve a abrir** la terminal (PowerShell, CMD o GIT Bash)

6. Verifica la instalación ejecutando:
   ```bash
   composer --version
   ```

---

## 📦 Instalación de Dependencias

### Paso 1: Habilitar extensiones de PHP

Antes de instalar las dependencias, asegúrate de que las siguientes extensiones estén habilitadas en `C:\xampp\php\php.ini`:

Busca estas líneas y **quita el punto y coma (;)** del inicio si lo tienen:

```ini
extension=gd
extension=zip
extension=curl
extension=mbstring
extension=fileinfo
extension=openssl
```

> **Nota:** Después de modificar `php.ini`, reinicia Apache desde el Panel de Control de XAMPP.

### Paso 2: Instalar las dependencias

1. Abre PowerShell, CMD o GIT Bash

2. Navega a la carpeta del proyecto:
   ```powershell
   cd C:\xampp\htdocs\BuzzDM
   ```

3. Ejecuta el comando de instalación:

   **Si instalaste Composer globalmente (Opción 1):**
   ```bash
   composer install
   ```

4. Espera a que se descarguen todas las dependencias. Esto creará la carpeta `vendor/`

5. Aplica las migraciones de base de datos (solo desde CLI):

   ```bash
   php php/migrate.php
   ```

   Sin este paso, el limitador de intentos (`rate_limits`) funciona en modo
   permisivo y avisa por log, en vez de proteger el login. La migración
   `password_reset_token_hash.sql` sí degrada sin romperse: mientras no esté
   aplicada, los tokens de recuperación se guardan en claro como antes.

### Variables de entorno

Copia `.env.example` a `.env` y ajústalo. Las variables sensibles **no** tienen
valor por defecto: si faltan, los endpoints que las consumen rechazan la petición
en vez de aceptarla con un secreto predecible.

| Variable | Para qué sirve |
|----------|----------------|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Conexión a MySQL |
| `APP_ENV` | `production` desactiva la carga de `.env.local` |
| `APP_URL` | URL base para enlaces y correos |
| `BACKUP_SECRET_TOKEN` | Autoriza `auto_backup.php` y `tasa_bcv_update.php` |
| `CRON_SECRET_KEY` | Secreto de las tareas de `php/cron/` |
| `BACKUP_SKIP_TLS_VERIFY` | Ponlo en `1` solo si tu certificado es autofirmado (XAMPP local) |
| `RATE_LIMIT_PROXIES` | Lista separada por comas de proxies de confianza (IP exacta o CIDR). Vacía por defecto |

> No existe bypass de autenticación por header: el antiguo `X-Dev-Token` se
> eliminó junto con `DEV_API_TOKEN`, que ya no se usa en ninguna parte.

Si la aplicación se sirve **detrás de un proxy o CDN**, conviene declarar en
`RATE_LIMIT_PROXIES` las IP o rangos CIDR de ese proxy. El limitador de intentos
solo entonces toma la IP real de `X-Forwarded-For`. Con la variable vacía usa
siempre `REMOTE_ADDR`, que es el único valor que el cliente no puede falsear: es
la opción segura, pero todos los usuarios que compartan proxy se contabilizan
como una sola IP. Al activar los proxies de confianza se acepta la cabecera
desde ese proxy, así que su lista debe incluir únicamente proxies que tú
controlas.

Ajustes opcionales de PHP para producción: `session.cookie_httponly=1`,
`session.use_strict_mode=1`, `display_errors=Off` y `expose_php=Off`.

### Paso 3: Verificar la instalación

La instalación fue exitosa si:
- Se creó la carpeta `vendor/`
- Existe el archivo `vendor/autoload.php`
- No hay errores en la terminal

---

## 📚 Dependencias del Proyecto

| Paquete | Versión | Descripción |
|---------|---------|-------------|
| `phpoffice/phpspreadsheet` | ^5.0 | Manejo de archivos Excel |
| `dompdf/dompdf` | ^3.1 | Generación de PDFs |
| `phpmailer/phpmailer` | ^6.9 | Envío de emails |
| `vlucas/phpdotenv` | ^5.6 | Variables de entorno |
| `pragmarx/google2fa` | ^9.0 | Autenticación de dos factores |
| `bacon/bacon-qr-code` | ^3.0 | Generación de códigos QR |
| `symfony/dom-crawler` | ^7.4 | Extracción de datos para reportes |
| `symfony/css-selector` | ^7.4 | Selectores CSS para el crawler |

> `monolog/monolog` se eliminó: el proyecto no tenía ninguna referencia a él.

### Dependencias de desarrollo

| Paquete | Versión | Descripción |
|---------|---------|-------------|
| `zircote/swagger-php` | ^6.0 | Documentación de API |

> `swagger-php` solo se usa al regenerar `openapi.yaml` con `php generate_docs.php`
> (uso exclusivo de CLI). `php/api_docs_config.php` ya **no** forma parte del
> autoload de producción, así que `composer install --no-dev` funciona sin cambios.
>

---

## ⚠️ Solución de Problemas

### Error: "The zip extension and unzip/7z commands are both missing"

Habilita la extensión `zip` en `php.ini`:
```ini
extension=zip
```

### Error: "ext-gd is missing from your system"

Habilita la extensión `gd` en `php.ini`:
```ini
extension=gd
```

### Error: "composer is not recognized"

- Si instalaste Composer globalmente, cierra y vuelve a abrir la terminal
- Si usas la instalación manual, usa la ruta completa:
  ```powershell
  C:\xampp\php\php.exe composer.phar install
  ```

### Error: "Your lock file does not contain a compatible set of packages"

Ejecuta:
```bash
composer update
```

---

## 🚀 Ejecutar la Aplicación

1. Inicia **Apache** desde el panel de **XAMPP** y **MariaDB** desde HeidiSQL.

2. Accede a la aplicación en tu navegador:
   ```
   http://localhost/BuzzDM
   ```

---

## Como instalar MariaDB y conectarla a HeidiSQL

### Parte 1: Descargar MariaDB

#### Paso 1: Visitar la Página de Descargas

Abre tu navegador web y ve a: **https://mariadb.org/download/**

#### Paso 2: Seleccionar tu Versión

En la página de descargas, configura las siguientes opciones:
- **Sistema Operativo:** Windows
- **Arquitectura:** x86_64 (para sistemas de 64 bits)
- **Tipo de Paquete:** MSI Package
- **Versión:** Selecciona la última versión estable (ej., 11.x o 10.x LTS)

#### Paso 3: Descargar el Instalador

Haz clic en el botón **Download** para guardar el archivo instalador MSI en tu computadora.

---

### Parte 2: Instalar MariaDB

#### Paso 1: Ejecutar el Instalador

Haz doble clic en el archivo MSI descargado para iniciar el asistente de instalación. Si aparece el Control de Cuentas de Usuario, haz clic en **Sí** para permitir que el instalador se ejecute.

#### Paso 2: Aceptar el Acuerdo de Licencia

Lee el acuerdo de licencia, marca la casilla para aceptar los términos y haz clic en **Next**.

#### Paso 3: Elegir Características

Mantén las selecciones predeterminadas marcadas. Asegúrate de que **MariaDB Server** y **Client Programs** estén seleccionados. Haz clic en **Next**.

#### Paso 4: Establecer Contraseña de Root (¡Importante!)

Este es un paso crítico. Necesitarás:
1. Ingresar una contraseña segura para el usuario root
2. Confirmar la contraseña escribiéndola nuevamente
3. ***Anota esta contraseña*** — la necesitarás para HeidiSQL
4. Deja desmarcada la opción **Enable access from remote machines for root user** por seguridad

#### Paso 5: Configurar Ajustes del Servicio

En la pantalla de configuración del servicio:
- **Install as service:** ✅ Marca esta casilla (recomendado)
- **Service Name:** Déjalo como "MariaDB" o personalízalo
- **Enable Networking:** ✅ Marca esta casilla
- **TCP Port:** Déjalo en 3306 (predeterminado)

#### Paso 6: Completar la Instalación

Haz clic en **Install** para comenzar la instalación. Espera a que el proceso termine, luego haz clic en **Finish**.

MariaDB ahora se iniciará automáticamente como un servicio de Windows.

---

### Parte 3: Verificar que MariaDB está Ejecutándose

#### Revisar Servicios de Windows

1. Presiona **Win + R** para abrir el cuadro de diálogo Ejecutar
2. Escribe `services.msc` y presiona Enter
3. Busca **MariaDB** en la lista
4. Verifica que el Estado muestre **Running** (En ejecución)

---

### Parte 4: Conectar con HeidiSQL

#### Paso 1: Abrir HeidiSQL

Inicia HeidiSQL desde tu menú Inicio. Aparecerá la ventana del Administrador de Sesiones.

#### Paso 2: Crear una Nueva Sesión

Haz clic en el botón **New** en la esquina inferior izquierda. Dale un nombre a tu sesión (ej., "MariaDB Local").

#### Paso 3: Configurar Ajustes de Conexión

En la pestaña Settings, ingresa lo siguiente:

| Configuración | Valor |
|---------------|-------|
| Network type | MariaDB or MySQL (TCP/IP) |
| Library | libmariadb.dll |
| Hostname / IP | 127.0.0.1 |
| User | root |
| Password | *(la contraseña que estableciste durante la instalación)* |
| Port | 3306 |

#### Paso 4: Guardar y Conectar

Haz clic en **Save** para guardar la configuración de tu sesión, luego haz clic en **Open** para conectar.

¡Ahora deberías estar conectado a tu servidor MariaDB y puedes comenzar a crear bases de datos!

---

### Solución de Problemas de MariaDB

#### Error 10061: Conexión Rechazada

Esto significa que MariaDB no está ejecutándose. Abre Servicios (`services.msc`), busca MariaDB y haz clic en **Start** (Iniciar).

#### Error de Acceso Denegado

Verifica tu contraseña. Si la olvidaste, puede que necesites restablecer la contraseña de root a través del modo seguro de MariaDB.

#### Puerto Ya en Uso

Otra aplicación puede estar usando el puerto 3306 (posiblemente MySQL). Detén el servicio en conflicto o configura MariaDB para usar un puerto diferente.
