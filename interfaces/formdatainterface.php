<?php
namespace shgysk8zer0\HTTP\Interfaces;

interface FormDataInterface extends BodyInterface
{
	public function append(string $name, string $value): bool;

	public function get(string $name);

	public function getAll(string $name):? array;

	public function set(string $name, string $value): bool;

	public function has(string $name): bool;

	public function delete(string $name): bool;

	public function keys(): iterable;

	public function values(): iterable;

	public function entries(): iterable;
}
