#!/usr/bin/env php
<?php declare(strict_types=1);
/**
 * Calcul du checksum d'un message sysex Roland
 * génération du message sysex complet d'une commande DT1
 * usage: ./roland-sysex-dt1.php 01 50 12 45 FD
 *
 * Algorithme:
 * sum = somme de chaque octet d'adresse et de données
 * cs = sum % 128
 *
 * ROLAND :
 * F0 : début sysex
 * 41 : Roland manufacturer ID
 * 10 : Device ID, 17 = 10H
 * 00 : model ID #1 (FA06)
 * 00 : model ID #2 (FA06)
 * 77 : model ID #3 (FA06)
 * 12 : Command ID (DT1)
 * aa : address MSB
 * bb : address
 * cc : address
 * dd : address LSB
 * ee : data
 * sum: checksum
 * F7 : fin sysex
 *
 * @see https://www.roland.com/fr/support/by_product/fa-06/owners_manuals/19f18438-0147-4c93-9083-f5909c182cbc/
 */

if (sizeof($argv) === 1) {
    echo "Usage: ./roland-sysex-dt1.php [d] aa bb cc dd ee\n";
    die();
}

// retrait du nom du script
array_shift($argv);

// mode debug activé si 1er paramètre = "d"
if ($argv[0] === 'd') {
   define('DEBUG_MODE', true);
   array_shift($argv);
} else {
   define('DEBUG_MODE', false);
}
$bytes = $argv;

/*
des exemples
Temporary Studio Set    : 18 00 00 00
Studio Set Pad Common   : 18 00 51 00
Studio Set Pad (Pad 01) : 18 00 52 00
Studio Set Pad (Pad 02) : 18 00 52 40

Studio Set Pad (Pad 03) : 18 00 53 00
Studio Set Pad (Pad 04) : 18 00 53 40
Studio Set Pad (Pad 05) : 18 00 54 00
Studio Set Pad (Pad 06) : 18 00 54 40
Studio Set Pad (Pad 07) : 18 00 55 00
Studio Set Pad (Pad 08) : 18 00 55 40

Studio Set Pad (Pad 09) : 18 00 56 00
Studio Set Pad (Pad 10) : 18 00 56 40
Studio Set Pad (Pad 11) : 18 00 57 00
Studio Set Pad (Pad 12) : 18 00 57 40
Studio Set Pad (Pad 13) : 18 00 58 00
Studio Set Pad (Pad 14) : 18 00 58 40
Studio Set Pad (Pad 15) : 18 00 59 00
Studio Set Pad (Pad 16) : 18 00 59 40
*/

$sum = 0;
if (DEBUG_MODE) {
    printf("ROLAND SySex Checksum Calculator\n");
    printf("data      :");
}
foreach ($bytes as $idx => $byte) {
    $byte = hexdec($byte);
    if (DEBUG_MODE) {
        printf(" %02x", $byte);
    }
    $sum = $sum + $byte;
}
if (DEBUG_MODE) {
    printf("\n");
}

$remainder = $sum % 128;
$checksum = 128 - $remainder;

if ($checksum === 128) {
    $checksum = 0;
}

if (DEBUG_MODE) {
    printf("sum       : %02x\n", $sum);
    printf("remainder : %02x\n", $remainder);
    printf("checksum  : %02x\n", $checksum);
}

$out = [];
$out[] = 0xF0; // début sysex
$out[] = 0x41; // Roland manufacturer ID
$out[] = 0x10; // Device ID, 17 = 10H
$out[] = 0x00; // model ID #1 (FA06)
$out[] = 0x00; // model ID #2 (FA06)
$out[] = 0x77; // model ID #3 (FA06)
$out[] = 0x12; // Command ID (DT1)

foreach ($bytes as $idx => $byte) {
    $out[] = hexdec($byte);
}
$out[] = $checksum;
$out[] = 0xF7; // fin sysex

if (DEBUG_MODE) {
    foreach ($out as $char) {
        printf("%02x ", $char);
    }
    echo "\n";
}

if (!DEBUG_MODE) {
    // affichage de la chaîne binaire (= contenu du .syx)
    foreach ($out as $char) {
        echo pack('C', $char);
    }
}
