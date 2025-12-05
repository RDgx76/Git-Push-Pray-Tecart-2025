<?php
// Konfigurasi dasar aplikasi
return [
    "app_name"      => "ByteMart",
    "base_url"      => "http://localhost/ByteMart/",
    "env"           => "development",

    // Database
    "db_host"       => "localhost",
    "db_name"       => "bytemart", // DIPERBAIKI: Sesuai dengan database.sql
    "db_user"       => "root",
    "db_pass"       => "",

    // Security
    "session_name"  => "bytemart_session",
    "token_lifetime" => 3600,
];