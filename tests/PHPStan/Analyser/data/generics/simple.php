<?php

namespace Generics\Simple;

function acceptDateTime(DateTime $dt) {
}

/**
 * @template T
 * @return T
 */
function a($x) {
	return $x;
}

/**
 * @template T of DateTime
 * @return T
 */
function b($x) {
	return $x;
}

acceptDateTime(a(new DateTime()));

acceptDateTime(b(new DateTime()));
acceptDateTime(b(new DateTimeImmutable()));


