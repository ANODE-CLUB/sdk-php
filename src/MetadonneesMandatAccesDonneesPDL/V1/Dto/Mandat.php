<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto;

use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;

readonly class Mandat
{
    public function __construct(
        public Parties $parties,
        public Objet $objet,
        public Consentement $consentement
    ) {
    }

    public function buildXml(
    ): string {
        $xml = '<mandat>' . "\n";
        $xml .= $this->parties->buildXml();
        $xml .= $this->objet->buildXml();
        $xml .= $this->consentement->buildXml();
        $xml .= '</mandat>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $mandat
    ): self {

        return new self(
            Parties::makeFromXml($mandat->parties),
            Objet::makeFromXml($mandat->objet),
            Consentement::makeFromXml($mandat->consentement)
        );
    }
}
