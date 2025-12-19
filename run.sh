#!/bin/bash
echo "Building static site..."
php build.php
echo "Starting server at http://localhost:8000"
php -S localhost:8000
