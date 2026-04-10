<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;

use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;

readonly class PersonnePhysique extends Partie
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public string $adressePostale,
        public string $adresseEmail,
        public string $numeroTelephone
    ) {
        if (!$nom) {
            throw new \InvalidArgumentException('Le nom est obligatoire.');
        }
        if (!$prenom) {
            throw new \InvalidArgumentException('Le prénom est obligatoire.');
        }
        if (!$adressePostale) {
            throw new \InvalidArgumentException('L\'adresse postale est obligatoire.');
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
        $xml = '<personnePhysique>' . "\n";
        $xml .= '  <nom>' . htmlspecialchars($this->nom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</nom>' . "\n";
        $xml .= '  <prenom>' . htmlspecialchars($this->prenom, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</prenom>' . "\n";
        $xml .= '  <adressePostale>' . htmlspecialchars($this->adressePostale, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</adressePostale>' . "\n";
        $xml .= '  <adresseEmail>' . htmlspecialchars($this->adresseEmail, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</adresseEmail>' . "\n";
        $xml .= '  <numeroTelephone>' . htmlspecialchars($this->numeroTelephone, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</numeroTelephone>' . "\n";
        $xml .= '</personnePhysique>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $personnePhysique
    ): self {
        return new self(
            (string) $personnePhysique->nom,
            (string) $personnePhysique->prenom,
            (string) $personnePhysique->adressePostale,
            (string) $personnePhysique->adresseEmail,
            (string) $personnePhysique->numeroTelephone
        );
    }
}
