<?php
function rate_limit_or_exit($bucket, $limit, $windowSeconds) {
	$dir = sys_get_temp_dir() . '/rl';
	if (!is_dir($dir)) @mkdir($dir, 0700, true);

	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
	$key = $dir . '/' . md5($bucket.'|'.$ip);
	$now = time();
	$wins = array();

	if (file_exists($key)) {
		$lines = file($key, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines !== false) {
			foreach ($lines as $t) {
				$t = (int)$t;
				if ($t > $now - $windowSeconds) {
					$wins[] = $t;
				}
			}
		}
	}

	if (count($wins) >= $limit) {
		http_response_code(429);
		exit('Too Many Requests. Inténtalo más tarde.');
	}

	$wins[] = $now;
	file_put_contents($key, implode("\n", $wins));
}
