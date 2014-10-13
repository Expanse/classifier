<?php

namespace Expanse\Classifier;

class WordHash {
	private static $skip_words = array("a", "again", "all", "along", "are", "also", "an", "and", "as", "at", "but", "by", "came", "can", "cant", "couldnt", "did", "didn", "didnt", "do", "doesnt", "dont", "ever", "first", "from", "have", "her", "here", "him", "how", "i", "if", "in", "into", "is", "isnt", "it", "itll", "just", "last", "least", "like", "most", "my", "new", "no", "not", "now", "of", "on", "or", "should", "sinc", "so", "some", "th", "than", "this", "that", "the", "their", "then", "those", "to", "told", "too", "true", "try", "until", "url", "us", "were", "when", "whether", "while", "with", "within", "yes", "you", "youll");

	public static function word_hash($string) {
		$result = self::clean_word_hash($string);
		return $result;
	}

	public static function clean_word_hash($string) {
		$string = preg_replace('/[^\w\s]/', '', $string);
		$result = self::word_hash_for_words(preg_split('/\s+/', $string));
		return $result;
	}

	private static function word_hash_for_words(array $word_arr) {
		$d = array();

		foreach ($word_arr as $word) {
			$word = strtolower($word);

			$stem = \Porter::Stem($word);

			if (preg_match('/[^\w]/', $word) or (!in_array($word, self::$skip_words) && strlen($word) > 2)) {
				if (! isset($d[$stem])) $d[$stem] = 0;
				$d[$stem]++;
			}
		}

		return $d;
	}
}
