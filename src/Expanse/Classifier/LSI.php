<?php

namespace Expanse\Classifier;

class LSI extends \Expanse\Classifier {
	public function __construct(array $options = array()) {
		$this->auto_rebuild = true;
		if (isset($options['auto_rebuild'])) {
			$this->auto_rebuild = $options['auto_rebuild'];
		}

		$this->word_list = new WordList;
		$this->items = array();

		$this->version = 0;
		$this->built_at_version = 1;
	}

	public function needs_rebuild() {
		return (count($this->items) > 1 && ($this->version != $this->built_at_version));
	}

	public function add_item($item, array $categories = array(), Closure $block = null) {
		$clean_word_hash = WordHash::clean_word_hash((is_object($block) && ($block instanceof Closure)) ? call_user_func_array($block, array($item)) : $item);
		$this->items[$item] = new ContentNode($clean_word_hash, $categories);
		$this->version++;
		if ($this->auto_rebuild) $this->build_index();
	}

	public function categories_for($item) {
		if (! array_key_exists($item, $this->items)) return array();
		return $this->items[$items]->categories();
	}

	public function remove_item($item) {
		if (array_key_exists($item, $this->items)) {
			unset($this->items[$item]);
			$this->version++;
		}
	}

	public function build_index($cutoff = 0.75) {
		if (! $this->needs_rebuild()) return;
		$this->make_word_list();

		$doc_list = $this->items;
		$doc_list_map = array_keys($doc_list);
		$tda = array_map(function($node) {
			$node->raw_vector_with($this->word_list);
			return $node->raw_vector->toArray();
		}, $doc_list);

		$tdm = self::transpose($tda);
		$ntdm = $this->build_reduced_matrix($tdm, $cutoff);

		for($col = 0; $col < count($ntdm); $col++) {
			// if (isset($doc_list_map[$col])) {
			// 	$doc_list[$doc_list_map[$col]]->lsi_vector = array_map(function($i) use ($col) {
			// 		return $i[$col];
			// 	}, $ntdm);
			// // doc_list[col].lsi_vector = ntdm.column(col) if doc_list[col]
			// // doc_list[col].lsi_norm = ntdm.column(col).normalize  if doc_list[col]
			// }
		}

		$this->built_at_version = $this->version;
	}

	public function highest_relative_content( $max_chunks=10 ) {
		// return [] if needs_rebuild?

		// avg_density = Hash.new
		// @items.each_key { |x| avg_density[x] = proximity_array_for_content(x).inject(0.0) { |x,y| x + y[1]} }

		// avg_density.keys.sort_by { |x| avg_density[x] }.reverse[0..max_chunks-1].map
	}

	public function proximity_array_for_content( $doc, Closure $block = null) {
		if ($this->needs_rebuild()) return array();

		$content_node = $this->node_for_content( $doc, $block );
		$_items = $this->items;
		$result = array_map(function($item) use ($content_node) {
			$val = self::matrix_mult($content_node->search_vector(), self::transpose($item->search_vector()));
			return array($item, $val[0]);
		}, $_items);
		uksort($result, function($a, $b) {
			if ($b[1] == $a[1]) return 0;
			if ($b[1] > $a[1]) return 1;
			return -1;
		});
		return array_reverse($result);
	}

	public function proximity_norms_for_content( $doc, Closure $block = null) {
	//		return [] if needs_rebuild?

	//		content_node = node_for_content( doc, &block )
	//		result = 
	//		@items.keys.collect do |item|
	//		if $GSL
	//			val = content_node.search_norm * @items[item].search_norm.col
	//		else
	//			val = (Matrix[content_node.search_norm] * @items[item].search_norm)[0]
	//			end
	//			[item, val]
	//			end
	//			result.sort_by { |x| x[1] }.reverse
	} 

	public function search( $string, $max_nearest=3 ) {
	//			return [] if needs_rebuild?
	//			carry = proximity_norms_for_content( string )
	//			result = carry.collect { |x| x[0] }
	//			return result[0..max_nearest-1]
	}

	public function find_related( $doc, $max_nearest=3, Closure $block = null) {
		$carry = $this->proximity_array_for_content( $doc, $block );
		foreach ($carry as $pair) {
			if ($pair[0] == $doc) {
				unset($pair);
			}
		}
		$result = array_map(function($x) { return $x[0]; }, $carry);
		return $result[range(0, $max_nearest - 1)];
	}

	public function classify( $doc, $cutoff=0.30, Closure $block = null) {
	//			icutoff = (@items.size * cutoff).round
	//			carry = proximity_array_for_content( doc, &block )
	//			carry = carry[0..icutoff-1]
	//			votes = {}
	//			carry.each do |pair|
	//			categories = @items[pair[0]].categories
	//			categories.each do |category| 
	//			votes[category] ||= 0.0
	//			votes[category] += pair[1] 
	//			end
	//			end

	//			ranking = votes.keys.sort_by { |x| votes[x] }
	//			return ranking[-1]
	}

	public function highest_ranked_stems( $doc, $count=3 ) {
	//			raise "Requested stem ranking on non-indexed content!" unless @items[doc]
	//			arr = node_for_content(doc).lsi_vector.to_a
	//			top_n = arr.sort.reverse[0..count-1]
	//			return top_n.collect { |x| @word_list.word_for_index(arr.index(x))}
	}

	private function build_reduced_matrix($matrix, $cutoff = 0.75) {
		# TODO: Check that M>=N on these dimensions! Transpose helps assure this
		list($u, $v, $s) = $this->SV_decomp($matrix);

		# TODO: Better than 75% term, please. :\
		sort($s);
		$reversed = array_reverse($s);
		$s_cutoff = $reversed[round(count($s) * $cutoff) - 1];

		for($ord = 0; $ord < count($s); $ord++) {
			if ($s[$ord] < $s_cutoff)
				$s[$ord] = 0.0;
		}
		# Reconstruct the term document matrix, only with reduced rank
		$u = self::matrix_mult(self::diagonal($s), self::transpose($v));
		return $u;
	}

	private function node_for_content($item, Closure $block = null) {
		if ($this->items[$item]) {
			return $this->items[$item];
		} else {
			$clean_word_hash = WordHash::clean_word_hash((is_object($block) && ($block instanceof Closure)) ? call_user_func_array($block, array($item)) : $item);

			$cn = new ContentNode($clean_word_hash, $categories);

			if (! $this->needs_rebuild()) {
				$cn->raw_vector_with($this->word_list);
			}
		}

		return $cn;
	}

	private function make_word_list() {
		$this->word_list = new WordList;
		foreach ($this->items as $node) {
			$hashed_node = WordHash::word_hash($node);
			foreach ($hashed_node as $key => $idx) {
				$this->word_list->add_word($key);
			}
		}
	}

	/**
	 * transpose function found at 
	 * http://stackoverflow.com/a/3423692/16645
	 */
	private static function transpose($array) {
		array_unshift($array, null);
		var_dump($array);
		$result = call_user_func_array('array_map', $array);
		return $result;
	}

	private function SV_decomp($matrix, $maxSweeps = 20) {
		if (count($matrix) >= count($matrix[0])) {
			$q = self::matrix_mult(self::transpose($matrix), $matrix);
		} else {
			$q = self::matrix_mult($matrix, self::transpose($matrix));
		}

		$qrot = $q;
		$v = $this->identity(count($q));
		$azrot = null;
		$mzrot = null;
		$cnt = 0;
		$s_old = null;
		$mu = null;

		while (true) {
			$cnt++;
			foreach (range(0, count($qrot)-1) as $row) {
				foreach (range(0, count($qrot)-1) as $col) {
					if ($row == $col) continue;
					$h = atan((2 * $qrot[$row][$col]) / ($qrot[$row][$row] - $qrot[$col][$col])) / 2.0;
					$hcos = cos($h);
					$hsin = sin($h);
					$mzrot = $this->identity(count($qrot));
					$mzrot[$row][$row] = $hcos;
					$mzrot[$row][$col] = -$hsin;
					$mzrot[$col][$row] = $hsin;
					$mzrot[$col][$col] = $hcos;
					$qrot = self::matrix_mult(self::matrix_mult(self::transpose($mzrot), $qrot), $mzrot);
					$v = self::matrix_mult($v, $mzrot);
				}
			}
			if ($cnt == 1) $s_old = $qrot;
			$sum_qrot = 0.0;
			if ($cnt > 1) {
				for($r = 0; $r < count($qrot); $r++) {
					if (abs($qrot[$r][$r] - $s_old[$r][$r]) > 0.001) {
						$sum_qrot += abs($qrot[$r][$r] - $s_old[$r][$r]);
					}
				}
				$s_old = $qrot;
			}
			if (($sum_qrot <= 0.001 && $cnt > 1) or ($cnt >= $maxSweeps))
				break;
		} # of do while true

		$s = array();

		for($r = 0; $r < count($qrot); $r++) {
			$s[] = sqrt($qrot[$r][$r]);
		}

		if (count($matrix) >= count($matrix[0])) {
			$mu = self::matrix_mult(self::matrix_mult($matrix, $v), $this->diagonal($s));

			return array($mu, $v, $s);
		} else {
			//puts v.row_size
			//	puts v.column_size
			//	puts self.row_size
			//	puts self.column_size
			//	puts s.size

			//	mu = (self.trans * v *  Matrix.diagonal(*s).inverse)
			//	return [mu, v, s]
		}
	}

	private function identity($length) {
		$array = array();
		for($i = 0; $i < $length; $i++) {
			for($j = 0; $j < $length; $j++) {
				$array[$i][$j] = 0;
				if ($i == $j) {
					$array[$i][$j] = 1;
				}
			}
		}

		return $array;
	}

	private function diagonal($values) {
		$length = count($values);
		$array = array();
		for($i = 0; $i < $length; $i++) {
			for($j = 0; $j < $length; $j++) {
				$array[$i][$j] = 0;
				if ($i == $j) {
					$array[$i][$j] = array_shift($values);
				}
			}
		}

		return $array;
	}

	public static function matrix_mult($matrix1, $matrix2) {
		$array_1_cols = count($matrix1);
		$array_1_rows = count($matrix1[0]);
		$array_2_cols = count($matrix2);
		$array_2_rows = count($matrix2[0]);

		// Check to see if matrix multiplication is possible
		if($array_1_cols == $array_2_rows) {

			$m_cols = $array_2_cols;
			$m_rows = $array_1_rows;

			$array_3 = array();
			$col_index = 1;

			// Start loop for each column of the new matrix
			while($col_index <= $m_cols) {
				$m_col_index = $col_index - 1;
				$sub_array[$col_index] = array();

				// Start loop for each row of the new matrix
				$row_index = 1;
				while($row_index <= $m_rows) {
					$m_row_index = $row_index - 1;

					// Auxiliary array for each row of A
					$a_row[$row_index] = array();

					$a_index = 1;
					while($a_index <= $array_1_cols) {
						$start_p = $a_index - 1;
						$el_part_[$a_index] = $matrix1[$start_p];
						$el_part_[$a_index] = $el_part_[$a_index][$m_row_index];
						array_push($a_row[$row_index], $el_part_[$a_index]);
						++$a_index;
					}

					// Array for columns of B
					$b_col[$col_index] = $matrix2[$m_col_index];

					// Build matrix C - defined over the rows of A and the columns of B
					$c_part[$row_index][$col_index] = array_map(array('self', 'mul'), $a_row[$row_index], $b_col[$col_index]);
					$c_el[$row_index][$col_index] = array_sum($c_part[$row_index][$col_index]);
					array_push($sub_array[$col_index], $c_el[$row_index][$col_index]);

					// End row loop
					++$row_index;
				}

				array_push($array_3,$sub_array[$col_index]);

				++$col_index;
			}

			return $array_3;

		} else {
			var_dump([ $matrix1, $matrix2 ]);
			$backtrace = debug_backtrace();
			var_dump($backtrace[2]);
			throw new \Exception("Not possible; matrix1 has cols ${array_1_cols}, matrix2 has rows ${array_2_rows}");
		}
	}

	private static function mul($x, $y) {
		return $x * $y;
	}
}
