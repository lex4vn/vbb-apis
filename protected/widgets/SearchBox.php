<?php
class SearchBox extends CWidget {
	public $searchUrl;
	public function init() {
		
	}
	
	public function run() {
		$this->render("SearchBox", array('searchUrl' => $this->searchUrl));
	}
}