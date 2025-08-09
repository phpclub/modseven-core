# Создание unittest'ов для Modseven на основе Koseven тестов

## Цель проекта

Создать **современный test suite** для Modseven Framework, используя существующие тесты Koseven как:
- **Карту покрытия функционала** - какие компоненты и методы нужно протестировать
- **Справочник edge cases** - какие граничные случаи важно покрыть
- **Источник test scenarios** - какие сценарии использования тестировать

**НЕ цель**: Сохранять обратную совместимость или портировать старые тесты as-is.

## Подход к созданию тестов

### 1. Анализ покрытия (Coverage Analysis)

Для каждого класса Koseven определить:
- **Какие методы тестируются** в существующих тестах
- **Какие edge cases покрываются**
- **Какие сценарии использования проверяются**
- **Какие входные данные используются** для тестирования

### 2. Сопоставление с Modseven API (API Mapping)

Для каждого протестированного в Koseven функционала найти в Modseven:
- **Аналогичный функционал** (тот же или похожий метод)
- **Альтернативные способы** достижения того же результата
- **Новый функционал**, которого не было в Koseven

### 3. Создание современных тестов

Написать **новые тесты с нуля** для Modseven, которые:
- Покрывают тот же функционал, что и в Koseven
- Используют современные подходы к тестированию
- Соответствуют архитектуре Modseven
- Добавляют покрытие для нового функционала

## Структура проекта

```
tests/
├── Unit/           # Тесты отдельных классов и методов
│   ├── Arr/
│   │   ├── ArrTest.php
│   │   ├── CallbackTest.php
│   │   └── UtilityTest.php
│   ├── Core/
│   ├── Text/
│   └── ...
├── Integration/    # Тесты взаимодействия компонентов
├── Feature/        # End-to-end функциональные тесты
├── Support/        # Вспомогательные классы для тестов
│   ├── TestCase.php
│   ├── Fixtures/
│   └── Helpers/
├── bootstrap.php
└── phpunit.xml
```

## Методология создания тестов

### Пример: Arr::callback() метод

#### Шаг 1: Анализ существующего теста в Koseven
```php
// Koseven test показывает нам:
// - Что метод должен выполнять callback функции
// - Какие типы callback'ов поддерживались
// - Какие входные данные использовались
public function test_callback()
{
    $this->assertSame('foobar', Arr::callback('strtolower', 'FOOBAR'));
    // Показывает: нужно тестировать строковые callback'и
}
```

#### Шаг 2: Анализ нового API в Modseven
```php
// Modseven Arr::callback(string $str): array
// - Парсит строку callback'а
// - Возвращает [command, params]
// - Поддерживает статические методы ClassName::method
```

#### Шаг 3: Создание comprehensive тестов
```php
<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class CallbackTest extends TestCase
{
    /**
     * @dataProvider simpleCallbackProvider
     */
    public function testSimpleCallback(string $input, array $expected): void
    {
        $result = Arr::callback($input);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider staticMethodProvider
     */
    public function testStaticMethodCallback(string $input, array $expected): void
    {
        $result = Arr::callback($input);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider parametrizedCallbackProvider
     */
    public function testParametrizedCallback(string $input, array $expected): void
    {
        $result = Arr::callback($input);
        $this->assertSame($expected, $result);
    }

    public function testCallbackExecution(): void
    {
        // Проверяем, что разобранный callback действительно работает
        [$command, $params] = Arr::callback('strtolower(FOOBAR)');
        $result = call_user_func($command, ...$params);
        $this->assertSame('foobar', $result);
    }

    public function testEscapedCommasInParameters(): void
    {
        $result = Arr::callback('method(param1\,with\,commas,param2)');
        $expected = ['method', ['param1,with,commas', 'param2']];
        $this->assertSame($expected, $result);
    }

    public static function simpleCallbackProvider(): array
    {
        return [
            ['strtolower', ['strtolower', null]],
            ['trim', ['trim', null]],
            ['count', ['count', null]],
        ];
    }

    public static function staticMethodProvider(): array
    {
        return [
            ['MyClass::method', [['MyClass', 'method'], null]],
            ['Namespace\\Class::staticMethod', [['Namespace\\Class', 'staticMethod'], null]],
        ];
    }

    public static function parametrizedCallbackProvider(): array
    {
        return [
            ['str_replace(old,new)', ['str_replace', ['old', 'new']]],
            ['substr(0,5)', ['substr', ['0', '5']]],
            ['Class::method(param)', [['Class', 'method'], ['param']]],
        ];
    }
}
```

## Конфигурация тестового окружения

### phpunit.xml
```xml

<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         convertDeprecationsToExceptions="true"
         failOnRisky="true"
         failOnWarning="true"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache"
         coverageClover="coverage/clover.xml"
         coverageHtml="coverage/html"
         coverageXml="coverage/xml">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory suffix=".php">system</directory>
        </include>
        <exclude>
            <directory>system/vendor</directory>
        </exclude>
    </source>

    <php>
        <env name="APP_ENV" value="testing"/>
    </php>
</phpunit>
```

### tests/bootstrap.php
```php
<?php
// Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize Modseven for testing
// Set up test environment, configuration, etc.

// Define test constants
define('MODSPATH', realpath(__DIR__ . '/../system/') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/Support/') . DIRECTORY_SEPARATOR);

// Initialize the framework for testing
```

### tests/Support/TestCase.php
```php
<?php
namespace Modseven\Tests\Support;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpModseven();
    }

    protected function tearDown(): void
    {
        $this->tearDownModseven();
        parent::tearDown();
    }

    /**
     * Initialize Modseven framework for testing
     */
    protected function setUpModseven(): void
    {
        // Framework initialization logic
    }

    /**
     * Clean up after test
     */
    protected function tearDownModseven(): void
    {
        // Cleanup logic
    }

    /**
     * Helper: Create test fixtures
     */
    protected function createFixture(string $type, array $data = []): mixed
    {
        // Factory method for test fixtures
        return match($type) {
            'request' => $this->createTestRequest($data),
            'response' => $this->createTestResponse($data),
            default => throw new \InvalidArgumentException("Unknown fixture type: $type")
        };
    }
}
```

## Пошаговый план реализации

### Фаза 1: Инфраструктура (1-2 дня)
1. Создать структуру директорий
2. Настроить PHPUnit конфигурацию
3. Создать базовый TestCase
4. Настроить CI/CD pipeline

### Фаза 2: Утилитарные классы (3-5 дней)
1. **Arr** - методы работы с массивами
2. **Text** - работа с строками
3. **HTML** - HTML утилиты
4. **Date** - работа с датами
5. **URL** - работа с URLs

### Фаза 3: Основные компоненты (5-10 дней)
1. **Request/Response** - HTTP объекты
2. **Route** - маршrutization
3. **Controller** - контроллеры
4. **View** - представления
5. **Config** - конфигурация

### Фаза 4: Сложные компоненты (10-15 дней)
1. **Database** - работа с БД
2. **Cache** - кэширование
3. **Session** - сессии
4. **Validation** - валидация
5. **Security** - безопасность

### Фаза 5: Интеграционные тесты (3-5 дней)
1. Взаимодействие компонентов
2. End-to-end сценарии
3. Performance тесты

## Критерии качества тестов

### Code Coverage
- **Unit tests**: 95%+ покрытие для каждого класса
- **Integration tests**: покрытие основных сценариев использования
- **Feature tests**: покрытие пользовательских историй

### Test Quality
- **Читаемость**: ясные имена тестов и методов
- **Изоляция**: каждый тест независим
- **Детерминизм**: стабильные результаты
- **Скорость**: быстрое выполнение

### Documentation
- Комментарии к сложным тестам
- Примеры использования в тестах
- README с инструкциями по запуску

## Инструменты и утилиты

### Анализ покрытия
```bash
# Генерация отчета о покрытии
vendor/bin/phpunit --coverage-html coverage/html

# Проверка минимального покрытия
vendor/bin/phpunit --coverage-text --coverage-clover coverage/clover.xml
```

### Статический анализ
```bash
# PHPStan
vendor/bin/phpstan analyse tests/ --level=8

# Psalm
vendor/bin/psalm tests/ --show-info=true
```

### Автоматизация
```bash
# Запуск всех тестов
vendor/bin/phpunit

# Запуск конкретного теста
vendor/bin/phpunit tests/Unit/Arr/CallbackTest.php

# Запуск с фильтром
vendor/bin/phpunit --filter testCallbackExecution
```

## Результат

В итоге получим:
- **Comprehensive test suite** для всего функционала Modseven
- **Высокое code coverage** (95%+)
- **Современную архитектуру тестов** с использованием лучших практик
- **CI/CD integration** для автоматического тестирования
- **Документацию** и примеры использования

Тесты станут не просто проверкой корректности, но и **живой документацией** API Modseven Framework.