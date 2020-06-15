<?php

namespace shgysk8zer0\HTTP\Traits;

trait SerializableCookieTrait
{
	use CookieTrait;



	public function serialize(): string
	{
		return serialize([
			'name'     => $this->getName(),
			'value'    => $this->getValue(),
			'domain'   => $this->getDomain(),
			'path'     => $this->getPath(),
			'maxAge'   => $this->getMaxAge(),
			'expires'  => $this->getExpiresAsString(),
			'sameSite' => $this->getSameSite(),
			'httpOnly' => $this->getHttpOnly(),
			'secure'   => $this->getSecure(),
		]);
	}

	public function unserialize($data): void
	{
		[
			'name'     => $name,
			'value'    => $value,
			'domain'   => $domain,
			'path'     => $path,
			'maxAge'   => $max_age,
			'expires'  => $expires,
			'sameSite' => $same_site,
			'httpOnly' => $http_only,
			'secure'   => $secure,
		] = unserialize($data);

		$this->setName($name);
		$this->setValue($value);
		$this->setDomain($domain);
		$this->setPath($path);
		$this->setMaxAge($max_age);
		$this->setExpires(is_string($expires) ? new DateTimeImmutable($expires) : $expires);
		$this->setSameSite($same_site);
		$this->setHttpOnly($http_only);
		$this->setSecure($secure);
	}
}
