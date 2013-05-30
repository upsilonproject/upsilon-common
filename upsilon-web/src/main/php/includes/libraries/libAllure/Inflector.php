<?php
/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

abstract class Inflector {
	public static function singular($str)
	{
		$str = strtolower(trim($str));
		$end = substr($str, -3);

		if ($end == 'ies')
		{
			$str = substr($str, 0, strlen($str)-3).'y';
		}
		elseif ($end == 'ses')
		{
			$str = substr($str, 0, strlen($str)-2);
		}
		else
		{
			$end = substr($str, -1);

			if ($end == 's')
			{
				$str = substr($str, 0, strlen($str)-1);
			}
		}

		return $str;
	}

	public static function quantify($message, $count) {
		if ($count == 1) {
			return self::singularize($message);
		} else {
			return self::pluralize($message);
		}
	}

	public static function singularize($s) {
		return self::singular($s);
	}

	public static function pluralize($s, $force = false) {
		return self::plural($s, $force);
	}

	public static function plural($str, $force = FALSE)
	{
		$str = strtolower(trim($str));
		$end = substr($str, -1);

		if ($end == 'y')
		{
			// Y preceded by vowel => regular plural
			$vowels = array('a', 'e', 'i', 'o', 'u');
			$str = in_array(substr($str, -2, 1), $vowels) ? $str.'s' : substr($str, 0, -1).'ies';
		}
		elseif ($end == 's')
		{
			if ($force == TRUE)
			{
				$str .= 'es';
			}
		}
		else
		{
			$str .= 's';
		}

		return $str;
	}


	public static function camelize($str) {
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		return substr(str_replace(' ', '', $str), 1);
	}

	public static function underscore($str) {
		if (strpos($str, ' ')) {
			// Spaces
			return strtolower(preg_replace('/[\s]+/', '_', strtolower(trim($str))));
		} else {
			// Camel
			return strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', $str));
		}
	}

	public static function humanize($str) {
		$str = trim($str);
		$str = preg_replace('/([a-z])([A-Z])/', '\1 \2', $str);
		$str = strtolower($str);
		$str = ucwords($str);

		return $str;
	}
}

?>
