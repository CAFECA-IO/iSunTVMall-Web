<?php

class CcgwException extends Exception {
	public function errorMessage() {
		return $this->getMessage ();
	}
}

?>