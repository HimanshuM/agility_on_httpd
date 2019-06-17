<!DOCTYPE html>
<html>
<head>
	<title>Agility</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">
	<style>
		* {
			box-sizing: border-box;
		}
		body {
			margin: 0;
			padding: 0;
			font-family: 'Roboto', sans-serif;
			display: flex;
			flex-direction: column;
		}
		.exception {
			background: #d20b14;
			color: white;
			padding: 10px 20px;
		}
		.code {
			padding: 10px 20px;
		}
		.trace {
			padding: 10px 20px;
		}
		.row {
			display: flex;
			flex-direction: column;
			padding: 10px 0;
		}
		.row:not(:last-child) {
			border-bottom: 1px solid #98acbd;
		}
		.code {
			display: flex;
			flex-direction: column;
		}
		.lines {
			background: #2a2a2a;
			color: white;
			display: flex;
			width: 100%;
			overflow: auto;
		}
		.lines label {
			color: #999;
			padding: 0 5px;
			width: 5%;
		}
		.lines pre {
			margin: 0;
			width: 95%;
			overflow: auto;
		}
		.lines pre.highlight {
			background: #a33;
		}
		.lines pre::-webkit-scrollbar {
			display: none;
		}
		.kwd {
			color: #36dcf1!important;
		}
	</style>
	<script src="https://cdn.jsdelivr.net/gh/google/code-prettify@master/loader/run_prettify.js?skin=desert"></script>
</head>
<body>
	<?php
		$trace = $e->getTrace();
		list($sourceFile, $code, $lineNum) = $this->exceptionGetSource($e);
	?>
	<div class="exception">
		<?= get_class($e) ?> thrown in <?= ($trace[0]["class"] ?? "").($trace[0]["type"] ?? "").$trace[0]["function"]; ?>(): <?= $e->getMessage(); ?>
	</div>
	<div class="code">
		<div class="row"><?= $sourceFile; ?>:</div>
		<?php foreach ($code as $i => $line) { ?>
			<div class="lines">
				<label><?= $i ?></label>
				<pre class="prettyprint <?= $i == $lineNum ? "highlight" : "" ?>"><?= $line ?></pre>
			</div>
		<?php } ?>
	</div>
	<div class="trace">
		<h3>Stack Trace:</h3>
		<?php foreach ($trace as $line) { ?>
			<div class="row">
				<div class="line"><?= ($line["class"] ?? "").($line["type"] ?? "").$line["function"]; ?>()</div>
				<div class="line"><?= ($line["file"] ?? "&#60;php:internal&#62;").(!empty($line["line"]) ? ":".$line["line"] : ""); ?></div>
			</div>
		<?php } ?>
	</div>
</body>
</html>