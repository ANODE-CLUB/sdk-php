<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Anode\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;

$pdfHandler = new Pdf(
    'mandat.pdf'
);
$mandat = $pdfHandler->getMandat();

echo '<pre>' . var_export($mandat, true) . '</pre>';
