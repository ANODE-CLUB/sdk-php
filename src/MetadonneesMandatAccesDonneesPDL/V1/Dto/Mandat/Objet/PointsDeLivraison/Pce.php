<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;

readonly class Pce
{
    public function __construct(
        public string $valeur
    ) {
        if (!$valeur) {
            throw new \InvalidArgumentException('La valeur est obligatoire.');
        }
    }

    public function __toString(): string
    {
        return $this->valeur;
    }
}
