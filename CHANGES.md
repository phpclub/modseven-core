# CHANGES

## [Unreleased] — branch `unittests`

### Bug Fixes

- **`Log\Syslog::write()`** — `syslog()` expects an `int` priority; added private `levelToSyslog()` that maps PSR-3 level strings to `LOG_*` constants. Previously both the message and stack-trace calls passed a raw string, causing a TypeError in PHP 8.
- **`Debug::source()`** — `strlen()` was passed an `int` (`$range['end']`); added explicit `(string)` cast.
- **`File::split()`** / **`File::join()`** — `str_pad()` first argument must be `string`; both call sites now cast the integer piece counter with `(string)`.
- **`Validation::errors()`** — `I18n::get()` accepts at most 2 parameters; three call sites were passing a spurious `NULL` second argument followed by `$translate`. Removed the `NULL` and moved `$translate` to position 2.
- **`Inflector::singular()`** / **`Inflector::plural()`** — `$count` is always cast to `float`, so strict comparisons against the integer literal `1` / `!== 1` always resolved the wrong way (float `1.0 !== int 1` is `true` in PHP). Changed both comparisons to `1.0`, restoring correct singularisation and pluralisation.
- **`Num::ordinal()`** — replaced `||` with `&&` in the teen-exception condition; the OR was a tautology that made the entire `switch` dead code, so every number incorrectly returned `'th'`.
- **`Text::bytes()`** — guarded `strpos($force_unit, 'i')` against `null`; calling with a null force-unit caused a deprecation/TypeError in PHP 8.4.
- **`Cookie::delete()` / `Cookie::set()`** — changed `self::_setcookie()` to `static::_setcookie()` so that subclass overrides (the intended proxy pattern for unit testing) are actually dispatched.

### PHP 8.4 Compatibility

Resolved all "implicitly marking parameter as nullable is deprecated" warnings across the framework:

| File | Parameter(s) fixed |
|------|--------------------|
| `system/classes/Core.php` | `listFiles($paths)`, `message($path)` |
| `system/classes/Error/Exception.php` | `__construct($previous)` |
| `system/classes/Exception.php` | removed `E_STRICT` from `$php_errors` (constant deprecated in PHP 8.4) |
| `system/classes/Form.php` | `label($text)` |
| `system/classes/Fragment.php` | `_cacheKey($i18n)` |
| `system/classes/HTTP/Exception.php` | `__construct($previous)`, `request($request)` |
| `system/classes/HTTP/Exception/Redirect.php` | `__construct($previous)` |
| `system/classes/HTTP/Header.php` | `offsetGet()` — added `mixed` return type |
| `system/classes/HTTP/Response.php` | `status($code)` |
| `system/classes/I18n.php` | `__()` helper `$values` parameter |
| `system/classes/Request.php` | `cookie($value)`, `headers($value)` |
| `system/classes/Valid.php` | `range($step)`, `decimal($digits)` |
| `system/classes/Validation.php` | `rule($params)`, `error($params)`, `errors($file)`, `offsetGet()` return type |
| `system/classes/Validation/Exception.php` | `__construct($previous)` |
| `system/classes/View.php` | `__construct($file, $data)`, `factory($file, $data)`, `render($file)` |

### Test Infrastructure

- **`phpunit.xml`** — migrated schema from `10.0` to `10.5`; moved `<coverage><include>` to `<source><include>` as required by PHPUnit 10.5.
- **`tests/bootstrap.php`** — defined `DOCROOT` constant required by `Debug::path()`.
- **`CLAUDE.md`** — added project-level guidance for Claude Code (commands, architecture, Docker entry point).

### New Unit Tests

654 tests / 1323 assertions — 0 failures, 0 deprecations.

| Test file | Class covered | Tests |
|-----------|---------------|-------|
| `tests/Unit/ValidTest.php` | `Valid` | 117 |
| `tests/Unit/RouteTest.php` | `Route` | 27 |
| `tests/Unit/TextTest.php` | `Text` | ~35 |
| `tests/Unit/HtmlTest.php` | `HTML` | ~30 |
| `tests/Unit/ValidationTest.php` | `Validation` | 20 |
| `tests/Unit/DateTest.php` | `Date` | 25 |
| `tests/Unit/FormTest.php` | `Form` | 24 |
| `tests/Unit/CookieTest.php` | `Cookie` | 15 |
| `tests/Unit/NumTest.php` | `Num` | ~22 |
| `tests/Unit/UTF8Test.php` | `UTF8` | 19 |
| `tests/Unit/LogTest.php` | `Log` | 8 |
| `tests/Unit/InflectorTest.php` | `Inflector` | ~12 |
| `tests/Unit/UrlTest.php` | `URL` | ~12 |
| `tests/Unit/SecurityTest.php` | `Security` | 6 |
| `tests/Unit/I18nTest.php` | `I18n` | 8 |
| `tests/Unit/ExceptionTest.php` | `Exception` | 7 |
| `tests/Unit/HttpTest.php` | `HTTP` | 4 |
| `tests/Unit/ModelTest.php` | `Model` | 2 |
| `tests/Unit/ConfigTest.php` | `Config` | 3 |
| `tests/Unit/ControllerTest.php` | `Controller` | 1 |

#### Methods skipped (require full Core / Config / Session initialisation)

- `Inflector::singular()`, `plural()`, `uncountable()` — need `Config::load('inflector')`
- `Text::autoLink()`, `autoLinkUrls()`, `autoLinkEmails()`, `userAgent()` — need Config
- `Security::token()`, `check()` — need `Session::instance()`
- `HTML::anchor()`, `style()`, `script()`, `image()` with relative URIs — need `URL::site()`
- `URL::site()`, `base()` — need `Core::init()`
