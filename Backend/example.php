<?php
// Simple PHP filler code
echo "Hello from PHP!<br>";

// Example function
function greet($name) {
    return "Welcome, " . htmlspecialchars($name) . "!";
}

echo greet("Developer");
?>
