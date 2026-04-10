<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;

use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;

abstract readonly class Partie
{
    abstract public function buildXml(
    ): string;

    public static function makeFromXml(
        \SimpleXMLElement $partie
    ): self {
        if ($partie->personneMorale) {
            return PersonneMorale::makeFromXml($partie->personneMorale);
        }
        if ($partie->personnePhysique) {
            return PersonnePhysique::makeFromXml($partie->personnePhysique);
        }

        throw new \InvalidArgumentException('Le type de partie est invalide.');
    }
}
