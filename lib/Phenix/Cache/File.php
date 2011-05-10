<?php
/**
 * Filesystem storage engine for cache
 *
 * Original code: http://www.rooftopsolutions.nl/blog/107
 *
 * Usage:
 *
 * $cache = new Cache_File();
 * $key = "Posts:all";
 * if (!$data = $cache->fetch($key))
 * {
 *     $data = BaseModel::factory("Posts")->find_many();
 *     $cache->store($key, $data, 60); // 1 minute
 * }
 * return $data;
 */
class Cache_File extends Cache
{
	function store($key, $data, $ttl)
	{
		$h = fopen($this->getFileName($key), 'a+');
		if (!$h)
			throw new RuntimeException('Could not write to cache');

		flock($h, LOCK_EX);
		fseek($h, 0);
		ftruncate($h, 0);

		$data = serialize(array(time() + $ttl, $data));
		if (fwrite($h, $data) === false)
			throw new RuntimeException('Could not write to cache');

		fclose($h);
	}

	function fetch($key)
	{
		$filename = $this->getFileName($key);
		if (!file_exists($filename))
			return false;
		$h = fopen($filename,'r');

		if (!$h)
			return false;

		flock($h, LOCK_SH);
		$data = file_get_contents($filename);
		fclose($h);

		$data = @unserialize($data);
		if (!$data)
		{
			unlink($filename);
			return false;
		}

		if (time() > $data[0])
		{
			unlink($filename);
			return false;
		}

		return $data[1];
	}

	function delete($key)
	{
		$filename = $this->getFileName($key);
		if (file_exists($filename))
			return unlink($filename);
		else
			return false;
	}

	private function getFileName($key)
	{
		return CACHE.DS.md5($key);
	}
}
