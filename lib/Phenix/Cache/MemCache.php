<?php
/**
 * MemCache storage engine for cache
 *
 * Original code: http://www.rooftopsolutions.nl/blog/107
 *
 * Usage:
 *
 * $cache = new Cache_MemCache();
 * $cache->addServer('www1');
 * $cache->addServer('www2', 11211, 20); // this server has double the memory, and gets double the weight
 * $cache->addServer('www3', 11211);
 *
 * $key = "Posts:all";
 * if (!$data = $cache->fetch($key))
 * {
 *     $data = BaseModel::factory("Posts")->find_many();
 *     $cache->store($key, $data, 60); // 1 minute
 * }
 * return $data;
 */
class Cache_MemCache extends Cache
{
	public $connection;

	function __construct()
	{
		$this->connection = new MemCache;
	}

	function store($key, $data, $ttl)
	{
		return $this->connection->set($key, $data, 0, $ttl);
	}

	function fetch($key)
	{
		return $this->connection->get($key);
	}

	function delete($key)
	{
		return $this->connection->delete($key);
	}

	function addServer($host, $port = 11211, $weight = 10)
	{
		$this->connection->addServer($host, $port, true, $weight);
	}
}
