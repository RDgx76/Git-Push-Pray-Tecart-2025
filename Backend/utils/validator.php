<?php
function required($value) {
    return !empty(trim($value));
}

function isNumber($value) {
    return is_numeric($value);
}

function minLength($value, $len) {
    return strlen($value) >= $len;
}
