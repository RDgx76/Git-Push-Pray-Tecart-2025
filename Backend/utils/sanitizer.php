<?php
function clean($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
