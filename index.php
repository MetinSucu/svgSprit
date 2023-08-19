<?php

require "class.svgsprit.php";

// Kullanım örneği
$sourceDir = __DIR__ . "/svg/";
$outputFile = 'svgsprite.svg';
$excludeFiles = ['excluded'];// Dizindeki hariç tutulacak dosyalar.
$includeFiles = []; // Dizindeki eklenecek dosyalar ( boş bırakılırsa tüm dizini tarar )

$spriteGenerator = new SvgSpriteGenerator($sourceDir, $outputFile, $excludeFiles, $includeFiles);
$spriteGenerator->generateSprite();
