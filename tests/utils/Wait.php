<?php
namespace tests\utils;
/**
 * @author alex
 * @date   11.11.14
 * @time   15:17
 */
class Wait {
	/**
	 * @param callable    $funcCondition Must return boolean
	 * @param integer     $timeOut       In seconds
	 * @param string|null $message       Error message
	 * @return mixed
	 * @throws \Exception
	 */
	public static function explicit(callable $funcCondition, $timeOut = 10, $message = null) {
		$FinTime = time() + $timeOut;
		while (time() <= $FinTime) {
			usleep(10);
			$res = call_user_func($funcCondition);
			if ($res !== false) {
				return $res;
			}
		}

		if (empty($message)) {
			$message = 'explicit wait failed';
		}
		throw new \Exception($message);
	}
} 