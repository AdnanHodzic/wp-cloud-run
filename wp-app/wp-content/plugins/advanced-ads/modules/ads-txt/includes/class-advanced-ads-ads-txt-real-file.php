<?php
/**
 * Represents a real ads.txt file.
 */
class Advanced_Ads_Ads_Txt_Real_File {
	private $records = [];

	/**
	 * Parse a real file.
	 *
	 * @param string $file File data.
	 */
	public function parse_file( $file ) {
		$lines = preg_split( '/\r\n|\r|\n/', $file );
		$comments = [];

		foreach ( $lines as $line ) {
			$line = explode( '#', $line );

			if ( ! empty( $line[1] ) && $comment = trim( $line[1] ) ) {
				$comments[] = '# ' . $comment;
			}

			if ( ! trim( $line[0] ) ) {
				continue;
			}

			$rec = explode( ',', $line[0] );
			$data = [];

			foreach ( $rec as $k => $r ) {
				$r = trim( $r, " \n\r\t," );
				if ( $r ) {
					$data[] = $r;
				}
			}

			if ( $data ) {
				// Add the record and comments that were placed above or to the right of it.
				$this->add_record( implode( ', ', $data ), $comments );
			}

			$comments = [];
		}
	}

	/**
	 * Add record.
	 *
	 * @string $data     Record without comments.
	 * @array  $comments Comments related to the record.
	 */
	private function add_record( $data, $comments = [] ) {
		$this->records[] = [ $data, $comments ];
	}

	/**
	 * Get records
	 *
	 * @return array
	 */
	public function get_records() {
		return $this->records;
	}

	/**
	 * Output file
	 *
	 * @return string
	 */
	public function output() {
		$r = '';
		foreach ( $this->records as $rec ) {
			foreach ( $rec[1] as $rec1 ) {
				$r .= $rec1 . "\n";
			}
			$r .= $rec[0] . "\n";
		}
		return $r;
	}

	/**
	 * Subtract another ads.txt file.
	 *
	 * @return string
	 */
	public function subtract( Advanced_Ads_Ads_Txt_Real_File $subtrahend ) {
		$r1 = $subtrahend->get_records();
		foreach (  $this->records as $k => $record ) {
			foreach ( $r1 as $r ) {
				if ( $record[0] === $r[0] ) {
					unset( $this->records[ $k ] );
				}
			}
		}
	}
}
