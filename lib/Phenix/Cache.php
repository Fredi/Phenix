<?php
/**
 * Cache Adapter
 *
 * Original code: http://www.rooftopsolutions.nl/blog/107
 */
abstract class Cache
{
	abstract function fetch($key);
	abstract function store($key, $data, $ttl);
	abstract function delete($key);
}
