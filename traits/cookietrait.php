<?php

namespace shgysk8zer0\HTTP\Traits;

use \DateTimeInterface;

use \DateInterval;

use \DateTimeImmutable;

use \InvalidArgumentException;

trait CookieTrait
{
	private $_name;

	private $_value = null;

	private $_domain = null;

	private $_path = null;

	private $_http_only = false;

	private $_secure = false;

	private $_max_age = null;

	private $_expires = null;

	private $_same_site = null;



	public function getName(): string
	{
		return $this->_name;
	}

	public function setName(string $name): void
	{
		$this->_name = $name;
	}

	public function getValue():? string
	{
		return $this->_value;
	}

	public function setValue(?string $value): void
	{
		$this->_value = $value;
	}

	public function getDomain():? string
	{
		return $this->_domain;
	}

	public function setDomain(?string $value): void
	{
		$this->_domain = $value;
	}

	public function expiresIn(?DateInterval $value): void
	{
		$date = new DateTimeImmutable();
		$this->setExpires($date->add($value));
	}

	public function getMaxAge():? int
	{
		return $this->_max_age;
	}

	public function setMaxAge(?int $value): void
	{
		$this->_max_age = $value;
	}

	public function getExpires():? DateTimeInterface
	{
		return $this->_expires;
	}

	public function getExpiresAsString():? string
	{
		if ($expires = $this->getExpires()) {
			return $expires->format(DateTimeInterface::COOKIE);
		} else {
			return null;
		}
	}

	public function setExpires(?DateTimeInterface $value): void
	{
		$this->_expires = $value;
	}

	public function getPath():? string
	{
		return $this->_path;
	}

	public function setPath(?string $value): void
	{
		$this->_path = $value;
	}

	public function getHttpOnly(): bool
	{
		return $this->_http_only;
	}

	public function setHttpOnly(bool $value): void
	{
		$this->_http_only = $value;
	}

	public function getSecure(): bool
	{
		return $this->_secure;
	}

	public function setSecure(bool $value): void
	{
		$this->_secure = $value;
	}

	public function getSameSite():? string
	{
		return $this->_same_site;
	}

	public function setSameSite(?string $value): void
	{
		if (is_null($value)) {
			$this->_same_site = null;
		} elseif (! is_string($value)) {
			throw new InvalidArgumentException('SameSite must be a string');
		} elseif (in_array($value, ['Lax', 'Strict', 'None'])) {
			$this->_same_site = $value;
		} else {
			throw new InvalidArgumentException(sprintf('%s is not a valid value for SameSite', $value));
		}
	}
}
