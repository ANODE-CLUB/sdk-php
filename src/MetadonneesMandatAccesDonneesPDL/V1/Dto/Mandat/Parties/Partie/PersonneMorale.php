<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;

use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;

readonly class PersonneMorale extends Partie
{
    public function __construct(
        public string $denominationSociale,
        public string $siren,
        public string $adresseSiegeSocial,
        public ?RepresentantLegal $representantLegal
    ) {
    }

    public function buildXml(
    ): string {
        $xml = '<personneMorale>' . "\n";
        $xml .= '  <denominationSociale>' . htmlspecialchars($this->denominationSociale, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</denominationSociale>' . "\n";
        $xml .= '  <siren>' . htmlspecialchars($this->siren, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</siren>' . "\n";
        $xml .= '  <adresseSiegeSocial>' . htmlspecialchars($this->adresseSiegeSocial, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</adresseSiegeSocial>' . "\n";
        if ($this->representantLegal !== null) {
            $xml .= '  <representantLegal>' . "\n";
            $xml .= '    <nom>' . htmlspecialchars($this->representantLegal->nom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</nom>' . "\n";
            $xml .= '    <prenom>' . htmlspecialchars($this->representantLegal->prenom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</prenom>' . "\n";
            $xml .= '    <fonction>' . htmlspecialchars($this->representantLegal->fonction, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</fonction>' . "\n";
            $xml .= '    <adresseEmail>' . htmlspecialchars($this->representantLegal->adresseEmail, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</adresseEmail>' . "\n";
            $xml .= '    <numeroTelephone>' . htmlspecialchars($this->representantLegal->numeroTelephone, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</numeroTelephone>' . "\n";
            $xml .= '  </representantLegal>' . "\n";
        } else {
            $xml .= '  <representantLegal />' . "\n";
        }
        $xml .= '</personneMorale>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $personneMorale
    ): self {
        return new self(
            (string) $personneMorale->denominationSociale,
            (string) $personneMorale->siren,
            (string) $personneMorale->adresseSiegeSocial,
            RepresentantLegal::makeFromXml($personneMorale->representantLegal)
        );
    }
}
