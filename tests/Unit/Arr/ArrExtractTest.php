<?php
namespace Modseven\Tests\Unit\Arr;

use Modseven\Tests\Support\TestCase;
use Modseven\Arr;

class ArrExtractTest extends TestCase
{
    /**
     * @dataProvider extractProvider
     * @param array $array Source array to extract from
     * @param array $paths List of paths to extract
     * @param mixed $default Default value for non-existent paths
     * @param array $expected Expected result array
     */
    public function testExtract(array $array, array $paths, $default, array $expected): void
    {
        $result = Arr::extract($array, $paths, $default);

        $this->assertSame(count($expected), count($result));
        $this->assertSame($expected, $result);
    }

    /**
     * Test extract with empty source array
     */
    public function testExtractWithEmptySourceArray(): void
    {
        $result = Arr::extract([], ['key1', 'key2'], 'default');
        $expected = [
            'key1' => 'default',
            'key2' => 'default'
        ];
        
        $this->assertSame($expected, $result);
    }

    /**
     * Test extract with empty paths array
     */
    public function testExtractWithEmptyPathsArray(): void
    {
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $result = Arr::extract($array, [], 'default');
        
        $this->assertSame([], $result);
    }

    /**
     * Test extract with null default
     */
    public function testExtractWithNullDefault(): void
    {
        $array = ['existing' => 'value'];
        $result = Arr::extract($array, ['existing', 'missing'], null);
        
        $expected = [
            'existing' => 'value',
            'missing' => null
        ];
        
        $this->assertSame($expected, $result);
    }

    /**
     * Test extract with complex nested paths
     */
    public function testExtractWithComplexPaths(): void
    {
        $array = [
            'user' => [
                'profile' => [
                    'personal' => [
                        'name' => 'John',
                        'age' => 30
                    ],
                    'social' => [
                        'twitter' => '@john'
                    ]
                ],
                'settings' => [
                    'theme' => 'dark'
                ]
            ],
            'system' => [
                'version' => '1.0'
            ]
        ];

        $paths = [
            'user.profile.personal.name',
            'user.profile.social.twitter',
            'user.settings.theme',
            'system.version',
            'user.profile.personal.email', // doesn't exist
            'nonexistent.deep.path' // doesn't exist
        ];

        $result = Arr::extract($array, $paths, 'N/A');

        $expected = [
            'user' => [
                'profile' => [
                    'personal' => [
                        'name' => 'John',
                        'email' => 'N/A'
                    ],
                    'social' => [
                        'twitter' => '@john'
                    ]
                ],
                'settings' => [
                    'theme' => 'dark'
                ]
            ],
            'system' => [
                'version' => '1.0'
            ],
            'nonexistent' => [
                'deep' => [
                    'path' => 'N/A'
                ]
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test extract with numeric array indices
     */
    public function testExtractWithNumericIndices(): void
    {
        $array = [
            'users' => [
                0 => ['name' => 'John'],
                1 => ['name' => 'Jane'],
                2 => ['name' => 'Bob']
            ]
        ];

        $paths = ['users.0.name', 'users.1.name', 'users.5.name']; // index 5 doesn't exist

        $result = Arr::extract($array, $paths, 'Unknown');

        $expected = [
            'users' => [
                0 => ['name' => 'John'],
                1 => ['name' => 'Jane'],
                5 => ['name' => 'Unknown']
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test extract with custom delimiter
     */
    public function testExtractWithCustomDelimiter(): void
    {
        $originalDelimiter = Arr::$delimiter;
        
        try {
            // Change delimiter to /
            Arr::$delimiter = '/';
            
            $array = [
                'level1' => [
                    'level2' => [
                        'value' => 'found'
                    ]
                ]
            ];

            $result = Arr::extract($array, ['level1/level2/value', 'level1/missing'], 'default');

            $expected = [
                'level1' => [
                    'level2' => [
                        'value' => 'found'
                    ],
                    'missing' => 'default'
                ]
            ];

            $this->assertSame($expected, $result);
            
        } finally {
            // Restore original delimiter
            Arr::$delimiter = $originalDelimiter;
        }
    }

    /**
     * Test extract preserves array structure
     */
    public function testExtractPreservesStructure(): void
    {
        $array = [
            'config' => [
                'database' => ['host' => 'localhost'],
                'cache' => ['driver' => 'redis']
            ]
        ];

        $paths = ['config.database.host', 'config.cache.driver', 'config.session.timeout'];

        $result = Arr::extract($array, $paths, 'default');

        // Check that the structure is preserved and organized properly
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('database', $result['config']);
        $this->assertArrayHasKey('cache', $result['config']);
        $this->assertArrayHasKey('session', $result['config']);
        
        $this->assertSame('localhost', $result['config']['database']['host']);
        $this->assertSame('redis', $result['config']['cache']['driver']);
        $this->assertSame('default', $result['config']['session']['timeout']);
    }

    /**
     * Test extract with various data types as values
     */
    public function testExtractWithVariousDataTypes(): void
    {
        $array = [
            'string' => 'text',
            'number' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'array' => ['nested', 'values'],
            'object' => new \stdClass()
        ];

        $paths = array_keys($array); // extract all keys
        $result = Arr::extract($array, $paths, 'missing');

        // All values should be preserved as-is
        $this->assertSame($array, $result);
    }

    /**
     * Test extract with overlapping paths
     */
    public function testExtractWithOverlappingPaths(): void
    {
        $array = [
            'data' => [
                'user' => ['name' => 'John', 'email' => 'john@example.com'],
                'meta' => ['created' => '2023-01-01']
            ]
        ];

        $paths = [
            'data.user',           // entire user object
            'data.user.name',      // specific field from user
            'data.meta.created'    // field from meta
        ];

        $result = Arr::extract($array, $paths, null);

        $expected = [
            'data' => [
                'user' => [
                    'name' => 'John', 
                    'email' => 'john@example.com'
                ],
                'meta' => [
                    'created' => '2023-01-01'
                ]
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<int, array{array, array, mixed, array}>
     */
    public static function extractProvider(): array
    {
        return [
            // Basic case from Koseven tests
            [
                ['ko7' => 'awesome', 'blueflame' => 'was'],
                ['ko7', 'cakephp', 'symfony'],
                null,
                ['ko7' => 'awesome', 'cakephp' => null, 'symfony' => null]
            ],
            
            // String default value
            [
                ['chocolate cake' => 'in stock', 'carrot cake' => 'in stock'],
                ['carrot cake', 'humble pie'],
                'not in stock',
                ['carrot cake' => 'in stock', 'humble pie' => 'not in stock'],
            ],
            
            // Nested paths case from Koseven tests
            [
                ['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']],
                ['level1.level2a', 'level1.level2b'],
                null,
                ['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']],
            ],
            
            // Mixed existing and non-existing paths
            [
                ['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
                ['level1a', 'level1b.level2b'],
                null,
                ['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
            ],
            
            // Complex case with defaults from Koseven tests
            [
                ['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
                ['level1a', 'level1b.level2b', 'level1c', 'level1d.notfound'],
                'default',
                ['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2'], 'level1c' => 'default', 'level1d' => ['notfound' => 'default']],
            ],
            
            // Array as default value
            [
                ['existing' => 'value'],
                ['existing', 'missing'],
                ['default', 'array'],
                ['existing' => 'value', 'missing' => ['default', 'array']]
            ],
            
            // Empty array source
            [
                [],
                ['any', 'keys'],
                'fallback',
                ['any' => 'fallback', 'keys' => 'fallback']
            ],
            
            // Single path extraction
            [
                ['single' => ['nested' => 'value']],
                ['single.nested'],
                'not_used',
                ['single' => ['nested' => 'value']]
            ],
            
            // Deep nesting
            [
                ['a' => ['b' => ['c' => ['d' => 'deep_value']]]],
                ['a.b.c.d', 'a.b.c.e'],
                'default',
                ['a' => ['b' => ['c' => ['d' => 'deep_value', 'e' => 'default']]]]
            ],
        ];
    }
}