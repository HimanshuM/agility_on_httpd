<?php

use Phpm\UnitTest;
use StringHelpers\Str;

	class HumanizeTest extends UnitTest {

		function testHumanize() {
			$this->assertEquals("Himanshu Malpande", Str::humanize("HimanshuMalpande"));
		}

	}

?>