# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Modseven Core is a PHP 8.4+ framework derived from Kohana/Koseven, modernized with PSR compliance (PSR-1, 3, 4, 6, 12, 16), full namespace support (`Modseven\`), and strict typing. It is the core package — not a standalone application.

## Commands

### Install dependencies
```bash
composer install
```

### Run all tests
```bash
./vendor/bin/phpunit
```

### Run a single test file
```bash
./vendor/bin/phpunit tests/Unit/Arr/ArrPathTest.php
```

### Run a specific test suite
```bash
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite Feature
```

### Run tests with coverage (outputs to stdout + coverage-html/)
```bash
./vendor/bin/phpunit --coverage-html coverage-html
```

### Static analysis
```bash
./vendor/bin/psalm
./vendor/bin/phpstan analyse
```

### Docker development environment
```bash
cp .env.dist .env
docker compose up -d
docker compose exec modseven bash
```

PHP is only available inside the container. Run all `composer` and `phpunit` commands from within the container shell.

## Architecture

### Cascading Filesystem
Modseven inherits Kohana's cascading filesystem pattern. `Core::init()` sets up path constants (`SYSPATH`, `APPPATH`, `MODSPATH`) and a `find_file()` mechanism that searches paths in priority order: app → modules → system. This is critical to understand when tracing how config/view/class files are resolved.

### Core Bootstrap (`system/classes/Core.php`)
The `Core` class is the framework entry point. It manages:
- Environment constants (`PRODUCTION=10`, `STAGING=20`, `TESTING=30`, `DEVELOPMENT=40`)
- Static configuration properties (charset, base_url, etc.)
- `Core::init()` — initializes the framework, registers error/exception handlers, sets up the autoloader
- `Core::find_file()` — cascading file lookup across APPPATH/MODSPATH/SYSPATH

### Namespace Structure
All framework classes live under `Modseven\` (mapped to `system/classes/`). Subsystems use sub-namespaces:
- `Modseven\Cache\*` — caching with drivers (File, Memcached, Redis)
- `Modseven\Config\*` — configuration via readers/writers; `Config\File\Reader` loads PHP/YAML config files
- `Modseven\HTTP\*` — HTTP message abstractions (headers, request, response)
- `Modseven\Request\Client\*` — internal and external HTTP clients (cURL, Stream)
- `Modseven\Log\*` — PSR-3 logging with writers (File, StdOut, StdErr, Syslog)
- `Modseven\Session\*` — session drivers (Native, Cookie)
- `Modseven\Encrypt\Engine\*` — encryption via OpenSSL engine
- `Modseven\REST\*` — REST controller with JSON/HTML formatters
- `Modseven\Controller\Template` / `REST` — base controller extensions

### Request Lifecycle
`Request` → `Request\Client\Internal` dispatches to a `Controller`. `Route` matches the URL and determines controller/action. `Response` is returned and rendered. External HTTP calls go through `Request\Client\External` (cURL or Stream).

### Test Setup
Tests use three constants set in `tests/bootstrap.php`:
- `SYSPATH` / `MODSPATH` → `system/`
- `APPPATH` → `tests/Support/`

Test classes live under `Modseven\Tests\` (mapped to `tests/`). The `tests/Support/TestCase.php` base class provides framework init/teardown. Coverage is currently scoped to `system/classes/Arr.php` in `phpunit.xml` — expand the `<include>` block when adding coverage for other classes.

### PSR Integration Points
- **PSR-3** (`psr/log`): `Log` class and `Log\Writer` implement the logger interface
- **PSR-6** (`psr/cache`): `Cache\Item` implements `CacheItemInterface`
- **PSR-16** (`psr/simple-cache`): `Cache` implements `SimpleCache\CacheInterface`
