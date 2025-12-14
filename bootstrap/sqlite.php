<?php

$path = database_path('database.sqlite');

if (! file_exists($path)) {
    touch($path);
}
