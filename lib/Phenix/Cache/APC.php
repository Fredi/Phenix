<?php
/**
 * APC storage engine for cache
 *
 * Original code: http://www.rooftopsolutions.nl/blog/107
 *
 * Usage:
 *
 * $cache = new Cache_APC();
 * $key = "Posts:all";
 * if (!$data = $cache->fetch($key))
 * {
 *     $data = BaseModel::factory("Posts")->find_many();
 *     $cache->store($key, $data, 60); // 1 minute
 * }
 * return $data;
 */
class Cache_APC extends Cache
{
	function store($key, $data, $ttl)
	{
		return apc_store($key, serialize($data), $ttl);
	}

	function fetch($key)
	{
		return ($data = apc_fetch($key)) ? unserialize($data) : false;
	}

	function delete($key)
	{
		return apc_delete($key);
	}
}
