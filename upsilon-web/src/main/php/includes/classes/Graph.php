<?php

class Graph {
	private $xScaleMin = 0;
	private $xScaleMax = 100;

	private $yScaleMin = 0;
	private $yScaleMax = 100;

	private $leftMargin = 30;
	private $topMargin = 20;
	private $rightMargin = 30;
	private $bottomMargin = 150;

	private $fontSize = 8;

	public function __construct() {
		$this->height = 220;
		$this->width = 800;

		$this->colors = array();
		$this->image = imagecreate($this->width, $this->height);
		$this->colors['background'] = imagecolorallocate($this->image, 255, 255, 255);
		$this->colors['border'] = imagecolorallocate($this->image, 0, 0, 0);
		$this->colors['text'] = imagecolorallocate($this->image, 0, 0, 0);
		$this->colors['plots'] = imagecolorallocate($this->image, 100, 100, 100);

		$this->colors['good'] = imagecolorallocate($this->image, 0, 255, 0);
		$this->colors['bad'] = imagecolorallocate($this->image, 255, 0, 0);
		$this->colors['timeout'] = imagecolorallocate($this->image, 255, 127, 0);
		$this->colors['skipped'] = imagecolorallocate($this->image, 0, 240, 255);

		imagefill($this->image, 0, 0, $this->colors['background']);
		imagerectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $this->colors['border']);
	}

	public function output() {
		header('Content-Type: image/png');
		imagepng($this->image);
	}

	public function writeText($x, $y, $string, $angle = 0) {
		imagettftext($this->image, $this->fontSize, $angle, $x, $y, $this->colors['text'], 'resources/fonts/cour.ttf', $string);
	}

	public function drawAxis($x = true, $y = true) {
		if ($x) {
			imageline($this->image, $this->leftMargin, $this->topMargin, $this->leftMargin, $this->height - ($this->bottomMargin), $this->colors['text']);
		}

		if ($y) {
			imageline($this->image, $this->leftMargin, ($this->height - $this->bottomMargin), ($this->width - $this->leftMargin - $this->rightMargin + 2), ($this->height - $this->bottomMargin), $this->colors['text']);
		}
	}

	public function setXscale($min, $max) {
		$this->xScaleMin = $min;
		$this->xScaleMax = $max;
	}

	public function setYscale($min, $max) {
		$this->yScaleMin = $min;
		$this->yScaleMax = $max;
	}

	public function getXinc($count) {
		return ($this->width - $this->leftMargin - $this->rightMargin) / $count;
	}

	public function writeXaxisLabels(array $labels) {
		$xinc = $this->getXinc(count($labels));
		$x = $this->leftMargin;

		foreach ($labels as $label) {
			$this->writeText($x + $this->fontSize / 2, ($this->height - $this->bottomMargin + 140), $label, 90);
			$x += $xinc;	
		}
	}

	public function plot($x, $y, $karma, $text) {
		$x = $this->leftMargin + $x;
		$y = ($this->height - $this->bottomMargin) + -$y;

		$this->writeText($x + 5, $y - 4, $text, 45);
		imagefilledellipse($this->image, $x, $y, 7, 7, $this->getResultColor($karma));
	}

	private function getResultColor($text) {
		switch($text) {
		case 'GOOD': return $this->colors['good'];
		case 'BAD': return $this->colors['bad'];
		case 'TIMEOUT': return $this->colors['timeout'];
		case 'SKIPPED': return $this->colors['skipped'];
		default:
			return $this->colors['plots'];
		}
	}

	public function plotResults(array $results) {
		$xinc = $this->getXinc(count($results));
		$x = 0;

		$labels = array();

		foreach ($results as $res) {
			$labels[] = $res['date'];

			$this->plot($x, 0, $res['karma']);
			$x += $xinc;
		}

		$this->writeXaxisLabels($labels);
	}

	public function getYlimits($metrics) {
		$yMin = 0;
		$yMax = 0;

		foreach ($metrics as $itemMetric) {
			if ($yMin > $itemMetric->value) {
				$yMin = $itemMetric->value;
			}

			if ($yMax < $itemMetric->value) {
				$yMax = $itemMetric->value;
			}
		}

		return array(
			'min' => $yMin,
			'max' => $yMax,
		);
	}

	public function getYrelPos($yMin, $yMax, $yToPlot) {
		$y = $yMax - ($yToPlot - $yMin);
		$yRange = $yMax - $yMin;
		
		if ($yRange == 0) {
			return 0;
		} else {
			$per = ($y / $yRange) * 20;
	//		var_dump($yMin, $yMax, $y, $yRange, $per); exit;
		}

		return $per; 
	}

	public function plotMetrics(array $metrics, $metric) {
		$this->writeText(10, 10, "Metric: " . $metric);

		if (count($metrics) == 0) {
			return;
		}

		$xinc = $this->getXinc(count($metrics));
		$x = 0;

		$yLimits = $this->getYlimits($metrics);

		$labels = array();

		foreach ($metrics as $res) {
			$labels[] = $res->date;

			$yPos = $this->getYrelPos($yLimits['min'], $yLimits['max'], $res->value);
			$this->plot($x, $yPos, $res->karma, $res->value);
			$x += $xinc;
		}

		$this->writeXaxisLabels($labels);
	}
}

?>
