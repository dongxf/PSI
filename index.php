<?php
require "Mobile_Detect.php";

$detect = new Mobile_Detect();
if ($detect->isMobile()) {
	header('Location: /web/Mobile');
} else {
	header('Location: /web/');
}