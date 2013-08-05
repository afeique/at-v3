<?php

namespace AcrossTime;

function view($name) {
	return VIEW_DIR . $name . VIEW_EXT;
}

function layout($name) {
	return LAYOUT_DIR . $name . VIEW_EXT;
}