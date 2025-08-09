<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class CallbackTest extends TestCase
{
	/**
	 * @dataProvider simpleCallbackProvider
	 * @param array{0: string|array{string,string}, 1: null} $expected
	 */
	public function testSimpleCallback(string $input, array $expected): void
	{
		$result = Arr::callback($input);
		$this->assertSame($expected, $result);
	}

	/**
	 * @dataProvider staticMethodProvider
	 * @param array{0: array{string,string}, 1: null} $expected
	 */
	public function testStaticMethodCallback(string $input, array $expected): void
	{
		$result = Arr::callback($input);
		$this->assertSame($expected, $result);
	}

	/**
	 * @dataProvider parametrizedCallbackProvider
	 * @param array{0: string|array{string,string}, 1: array<int,string>} $expected
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

	/**
	 * @return array<int, array{string, array{string, null}}>
	 */
	public static function simpleCallbackProvider(): array
	{
		return [
			['strtolower', ['strtolower', null]],
			['trim', ['trim', null]],
			['count', ['count', null]],
		];
	}

	/**
	 * @return array<int, array{string, array{array{string,string}, null}}>
	 */
	public static function staticMethodProvider(): array
	{
		return [
			['MyClass::method', [['MyClass', 'method'], null]],
			['Namespace\\Class::staticMethod', [['Namespace\\Class', 'staticMethod'], null]],
		];
	}

	/**
	 * @return array<int, array{string, array{string|array{string,string}, array<int,string>}}>
	 */
	public static function parametrizedCallbackProvider(): array
	{
		return [
			['str_replace(old,new)', ['str_replace', ['old', 'new']]],
			['substr(0,5)', ['substr', ['0', '5']]],
			['Class::method(param)', [['Class', 'method'], ['param']]],
		];
	}
}