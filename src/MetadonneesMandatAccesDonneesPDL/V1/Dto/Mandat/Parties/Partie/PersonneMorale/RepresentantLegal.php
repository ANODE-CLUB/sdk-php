<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;

readonly class RepresentantLegal
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public string $fonction,
        public string $adresseEmail,
        public string $numeroTelephone
    ) {
        if (!$nom) {
            throw new \InvalidArgumentException('Le nom est obligatoire.');
        }
        if (!$prenom) {
            throw new \InvalidArgumentException('Le prénom est obligatoire.');
        }
        if (!$fonction) {
            throw new \InvalidArgumentException('La fonction est obligatoire.');
        }
        if (!$adresseEmail) {
            throw new \InvalidArgumentException('L\'adresse email est obligatoire.');
        }
        if (!$numeroTelephone) {
            throw new \InvalidArgumentException('Le numéro de téléphone est obligatoire.');
        }
    }

    public function buildXml(
    ): string {
        $xml = '<representantLegal>' . "\n";
        $xml .= '  <nom>' . htmlspecialchars($this->nom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</nom>' . "\n";
        $xml .= '  <prenom>' . htmlspecialchars($this->prenom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</prenom>' . "\n";
        $xml .= '  <fonction>' . htmlspecialchars($this->fonction, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</fonction>' . "\n";
        $xml .= '  <adresseEmail>' . htmlspecialchars($this->adresseEmail, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</adresseEmail>' . "\n";
        $xml .= '  <numeroTelephone>' . htmlspecialchars($this->numeroTelephone, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</numeroTelephone>' . "\n";
        $xml .= '</representantLegal>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $representantLegal
    ): self {
        return new self(
            (string) $representantLegal->nom,
            (string) $representantLegal->prenom,
            (string) $representantLegal->fonction,
            (string) $representantLegal->adresseEmail,
            (string) $representantLegal->numeroTelephone
        );
    }
}
