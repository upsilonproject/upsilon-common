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

class HtmlLinksCollection implements \Iterator {
	private $title;
	private $collection = array();
	private $iteratorPosition = 0; // Dont use the collection pointer
	private $defaultIcon;

	public function __construct($title = null) {
		$this->title = $title;
	}

	public function setDefaultIcon($icon) {
		$this->defaultIcon = $icon;
	}

	public function current() {
		return $this->collection[$this->iteratorPosition];
	}

	public function next() {
		$this->iteratorPosition++;
	}

	public function prev() {
		$this->iteratorPosition--;
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function rewind() {
		$this->iteratorPosition = 0;
	}

	public function getChildCollection($linkTitle = null) {
		if (empty($title)) {
			$title = $this->collection[$this->iteratorPosition]['title'];
		}

		if (isset($this->childCollection[$title])) {
			return $this->childCollection[$title]->getAll();
		} else {
			return false;
		}
	}

	public function valid() {
		if (count($this->collection) == 0) {
			return false;
		} else if ($this->iteratorPosition >= count($this->collection)) {
			return false;
		} else {
			return true;
		}
	}

	public function hasLinks() {
		return $this->getCount() > 0;
	}

	public function getCount() {
		return count($this->collection);
	}


	public function addIfPriv($priv, $url, $title, $iconUrl = null, $containerClass = null) {
		$this->addIf(Session::hasPriv($priv), $url, $title, $iconUrl, $containerClass);
	}

	public function addIf($test, $url, $title, $iconUrl = null, $containerClass = null) {
		if ($test) {
			$this->add($url, $title, $iconUrl, $containerClass);
		}
	}

	public function add($url, $title, $iconUrl = null, $containerClass = null) {
		if (empty($iconUrl) && !empty($this->defaultIcon)) {
			$iconUrl = $this->defaultIcon;
		}

		$this->collection[] = array(
			'url' => $url,
			'title' => $title,
			'iconUrl' => $iconUrl,
			'enabled' => true,
			'containerClass'=> $containerClass,
			'children' => array(),
		);

		return key($this->collection);
	}

	public function addChildCollection($title, HtmlLinksCollection $childCollection = null) {
		if ($childCollection == null) {
			$childCollection = new HtmlLinksCollection();
			$childCollection->defaultIcon = $this->defaultIcon;
		}

		foreach ($this->collection as &$link) {
			if ($link['title'] == $title) {
				$link['children'] = $childCollection;
				return $childCollection;
			}
		}

		throw new Exception('Cannot add child links collection, cannot find parent by title: ' . $title);
	}

	public function getAll() {
		return $this->collection;
	}

	public function setEnabled($linkIndex, $toEnabledState) {
		$this->collection[$linkIndex]['enabled'] = $toEnabledState;
	}

	public function getTitle() {
		return $this->title;
	}
}

?>
