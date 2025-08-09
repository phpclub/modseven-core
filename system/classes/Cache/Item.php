<?php

namespace Modseven\Cache;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

/**
 * Represents a single cache item.
 * Compatible with PSR-6 and Modseven-specific requirements.
 */
class Item implements CacheItemInterface
{
	/**
	 * Original cache key.
	 *
	 * @var string
	 */
	protected string $key = '';

	/**
	 * Sanitized cache key for storage backends (nullable because Cache may set it later).
	 *
	 * @var string|null
	 */
	protected ?string $_sanitizedKey = null;

	/**
	 * The cached value.
	 *
	 * @var mixed
	 */
	protected mixed $value = null;

	/**
	 * Whether the cache item is a hit.
	 *
	 * @var bool
	 */
	protected bool $hit = false;

	/**
	 * Explicit lifetime in seconds (nullable — null means "use default").
	 *
	 * @var int|null
	 */
	protected ?int $lifetime = null;

	/**
	 * Expiration time (stored as DateTimeImmutable).
	 *
	 * @var DateTimeImmutable|null
	 */
	protected ?DateTimeImmutable $expiration = null;

	/**
	 * Cache tags.
	 *
	 * @var string[]
	 */
	protected array $tags = [];

	/**
	 * Constructor.
	 *
	 * @param string|null $key Optional original key.
	 */
	public function __construct(?string $key = null)
	{
		if ($key !== null) {
			$this->setKey($key);
		}
	}

	/**
	 * Set the original key for this item.
	 *
	 * @param string $key
	 * @return void
	 */
	public function setKey(string $key): void
	{
		$this->key = $key;
		// initialize sanitized key by default — can be overridden later by setSanitizedKey()
		$this->_sanitizedKey = $this->sanitizeKey($key);
	}

	/**
	 * Returns the original key for this cache item.
	 *
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * Sets sanitized key directly (used by Cache when driver returns sanitized id).
	 *
	 * @param string $key
	 * @return void
	 */
	public function setSanitizedKey(string $key): void
	{
		$this->_sanitizedKey = $key;
	}

	/**
	 * Returns the sanitized key (or null if not set).
	 *
	 * @return string|null
	 */
	public function getSanitizedKey(): ?string
	{
		return $this->_sanitizedKey;
	}

	/**
	 * Sanitizes the cache key for safe storage (internal helper).
	 *
	 * @param string $key
	 * @return string
	 */
	protected function sanitizeKey(string $key): string
	{
		// Replace unsupported characters with underscore and trim
		return (string) preg_replace('/[^A-Za-z0-9_.]/', '_', trim($key));
	}

	/**
	 * Retrieves the value of the item from the cache.
	 *
	 * If isHit() returns false, this method MUST return null.
	 *
	 * @return mixed|null
	 */
	public function get(): mixed
	{
		return $this->isHit() ? $this->value : null;
	}

	/**
	 * Sets the value represented by this cache item.
	 *
	 * NOTE: if a backend driver returns FALSE for a missing value, we treat that as a miss.
	 *
	 * @param mixed $value
	 * @return static
	 */
	public function set(mixed $value): static
	{
		$this->value = $value;

		// Treat driver "false" (common sentinel for missing) as a cache miss.
		$this->hit = ($value !== false);

		return $this;
	}

	/**
	 * Confirms if the cache item lookup resulted in a cache hit.
	 *
	 * @return bool True if a cache hit, false otherwise.
	 */
	public function isHit(): bool
	{
		if (! $this->hit) {
			return false;
		}

		if ($this->expiration !== null && $this->expiration < new DateTimeImmutable()) {
			return false;
		}

		return true;
	}

	/**
	 * Sets whether this cache item is a hit.
	 *
	 * @param bool $hit
	 * @return void
	 */
	public function setHit(bool $hit): void
	{
		$this->hit = $hit;
	}

	/**
	 * Returns the lifetime in seconds.
	 * Defaults to 3600 if not set (Modseven behaviour).
	 *
	 * @return int Lifetime in seconds (>= 0)
	 */
	public function getLifeTime(): int
	{
		return $this->lifetime ?? 3600;
	}

	/**
	 * Returns all tags associated with this cache item.
	 *
	 * @return string[]
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * Sets tags for this cache item.
	 *
	 * @param string[] $tags
	 * @return static
	 */
	public function setTags(array $tags): static
	{
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Sets the expiration time for this cache item (absolute).
	 *
	 * Signature matches PSR-6 (psr/cache 3.0).
	 *
	 * @param DateTimeInterface|null $expiration Expiration or null for no expiration.
	 * @return static
	 */
	public function expiresAt(?DateTimeInterface $expiration): static
	{
		if ($expiration === null) {
			$this->expiration = null;
			$this->lifetime = null;
			return $this;
		}

		if ($expiration instanceof DateTimeImmutable) {
			$this->expiration = $expiration;
		} elseif ($expiration instanceof DateTime) {
			$this->expiration = DateTimeImmutable::createFromMutable($expiration);
		} else {
			throw new InvalidArgumentException(sprintf(
				'Expiration must be an instance of DateTimeInterface or null, %s given',
				get_debug_type($expiration)
			));
		}

		$secs = $this->expiration->getTimestamp() - time();
		$this->lifetime = $secs > 0 ? (int) $secs : 0;

		return $this;
	}

	/**
	 * Sets the expiration time for this cache item, relative to the current time.
	 *
	 * Signature matches PSR-6 (psr/cache 3.0): DateInterval|int|null
	 *
	 * @param DateInterval|int|null $time Seconds, DateInterval, or null.
	 * @return static
	 */
	public function expiresAfter(DateInterval|int|null $time): static
	{
		if ($time === null) {
			$this->expiration = null;
			$this->lifetime = null;
			return $this;
		}

		if (is_int($time)) {
			$this->expiration = (new DateTimeImmutable())->modify("+{$time} seconds");
			$this->lifetime = $time;
			return $this;
		}

		// $time is guaranteed to be DateInterval here
		$this->expiration = (new DateTimeImmutable())->add($time);
		$secs = $this->expiration->getTimestamp() - time();
		$this->lifetime = $secs > 0 ? (int) $secs : 0;
		return $this;
	}
}