<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;

readonly class Donnees
{
    public function __construct(
        public bool $technique,
        public bool $contractuel,
        public bool $usage,
        public int $dureeHistoriqueEnMois
    ) {
    }

    public function buildXml(
    ): string {
        $xml = '<donnees>' . "\n";
        $xml .= '  <technique>' . ($this->technique ? 'true' : 'false') . '</technique>' . "\n";
        $xml .= '  <contractuel>' . ($this->contractuel ? 'true' : 'false') . '</contractuel>' . "\n";
        $xml .= '  <usage>' . ($this->usage ? 'true' : 'false') . '</usage>' . "\n";
        $xml .= '  <dureeHistoriqueEnMois>' . $this->dureeHistoriqueEnMois . '</dureeHistoriqueEnMois>' . "\n";
        $xml .= '</donnees>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $donnees
    ): self {
        return new self(
            trim((string) $donnees->technique) === 'true',
            trim((string) $donnees->contractuel) === 'true',
            trim((string) $donnees->usage) === 'true',
            (int) $donnees->dureeHistoriqueEnMois
        );
    }
}
