<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Mandataire;

readonly class Parties
{
    public function __construct(
        public Partie $mandant,
        public Partie $mandataire
    ) {
    }

    public function buildXml(
    ): string {
        $xml = '<parties>' . "\n";
        $xml .= '  <mandant>' . "\n";
        $xml .= $this->mandant->buildXml();
        $xml .= '  </mandant>' . "\n";
        $xml .= '  <mandataire>' . "\n";
        $xml .= $this->mandataire->buildXml();
        $xml .= '  </mandataire>' . "\n";
        $xml .= '</parties>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $parties
    ): self {
        return new self(
            Partie::makeFromXml($parties->mandant),
            Partie::makeFromXml($parties->mandataire)
        );
    }
}
