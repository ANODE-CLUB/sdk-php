<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;

readonly class Objet
{
    public function __construct(
        public Donnees $donnees,
        public PointsDeLivraison $pointsDeLivraison,
        public ?Delegations $delegations
    ) {
    }

    public function buildXml(
    ): string {
        $xml = '<objet>' . "\n";
        $xml .= $this->donnees->buildXml();
        $xml .= $this->pointsDeLivraison->buildXml();
        if ($this->delegations) {
            $xml .= $this->delegations->buildXml();
        }
        $xml .= '</objet>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $objet
    ): self {

        return new self(
            Donnees::makeFromXml($objet->donnees),
            PointsDeLivraison::makeFromXml($objet->pointsDeLivraison),
            $objet->delegations ? Delegations::makeFromXml($objet->delegations) : null
        );
    }
}
