<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;

final readonly class Pdf
{
    public const XMLNS_KEY = 'anodeMetadonneesMandatAccesDonneesPDL';
    public const XMLNS_PATH = 'https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/';

    public const TAG_KEY_DONNEES = self::XMLNS_KEY . ':metadonnees';

    public function __construct(
        public string $filePath
    ) {
    }

    public function getContent(
    ) {
        return file_get_contents($this->filePath);
    }

    public function setMandat(
        Mandat $mandat
    ): self {
        $content = $this->getContent();

        // 1. S'assurer de la présence du tag <xmpmeta>
        if (strpos($content, '<xmpmeta') === false) {
            $emptyXmp = "<xmpmeta xmlns:x=\"adobe:ns:meta/\"></xmpmeta>\n";
            $content = str_replace('%%EOF', $emptyXmp . '%%EOF', $content);
        }

        // 2. S'assurer de la présence du tag <rdf:RDF> à l'intérieur de <xmpmeta>
        if (strpos($content, '<rdf:RDF') === false) {
            $rdfStructure = " <rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"></rdf:RDF>\n";
            $content = preg_replace('/(<xmpmeta.*?>)/s', "$1\n$rdfStructure", $content);
        }

        // 3. S'assurer de la présence du tag <rdf:Description> avec votre Namespace
        $nsAttr = 'xmlns:' . self::XMLNS_KEY . '="' . self::XMLNS_PATH . '"';
        if (strpos($content, self::XMLNS_PATH) === false) {
            $descStructure = "  <rdf:Description rdf:about=\"\" $nsAttr></rdf:Description>\n";
            $content = preg_replace('/(<rdf:RDF.*?>)/s', "$1\n$descStructure", $content);
        }

        // 4. Gérer les données (Remplacement si existe, ou ajout sinon)
        $xmlDonnees = $mandat->buildXml();

        $openDonnees = '<' . self::TAG_KEY_DONNEES . '>';
        $closeDonnees = '</' . self::TAG_KEY_DONNEES . '>';
        $fullDonnees = $openDonnees . $xmlDonnees . $closeDonnees . "\n";

        $quotedOpen = preg_quote($openDonnees, '#');
        $quotedClose = preg_quote($closeDonnees, '#');
        $donneesPattern = '#' . $quotedOpen . '.*?' . $quotedClose . '\s?#si';

        if (preg_match($donneesPattern, $content)) {
            // Remplacement
            $content = preg_replace($donneesPattern, $fullDonnees, $content);
        } else {
            // Ajout : on cherche la Description qui contient votre namespace
            $nsKeyQuoted = preg_quote(self::XMLNS_KEY, '#');
            $descPattern = '#(<rdf:Description[^>]*' . $nsKeyQuoted . '[^>]*>)#si';
            $content = preg_replace($descPattern, "$1\n" . $fullDonnees, $content);
        }

        // 5. Extraction et Indentation Automatique
        // On isole le bloc xmpmeta pour le formater proprement
        if (preg_match('#<xmpmeta.*?</xmpmeta>#si', $content, $xmpMatch)) {
            $xmlContent = $xmpMatch[0];

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            // On charge le bloc extrait (en ignorant les erreurs XML mineures dues aux namespaces)
            @$dom->loadXML($xmlContent);

            // On récupère le XML formaté (en retirant la déclaration <?xml... qui s'ajoute)
            $formattedXmp = $dom->saveXML($dom->documentElement);

            // On remplace l'ancien bloc par le nouveau bloc indenté
            $content = str_replace($xmlContent, $formattedXmp, $content);
        }

        file_put_contents($this->filePath, $content);

        return $this;
    }

    public function getMandat(
    ): Mandat {
        $content = $this->getContent();

        if (stripos($content, '<xmpmeta') === false) {
            throw new \DomainException("Le bloc de métadonnées XMP est manquant dans le fichier PDF.");
        }

        $tagName = self::TAG_KEY_DONNEES;
        $pattern = '/<' . preg_quote($tagName, '/') . '>(.*?)<\/' . preg_quote($tagName, '/') . '>/s';

        if (!preg_match($pattern, $content, $matches)) {
            throw new \DomainException("Le tag spécifique '{$tagName}' est introuvable dans les métadonnées du PDF.");
        }

        $xmlDonnees = $matches[1];

        $xml = @simplexml_load_string($xmlDonnees);

        if ($xml === false) {
            throw new \DomainException("Le contenu XML du mandat GRD est corrompu ou mal formé.");
        }

        // Vérifier la structure XML et extraire le contenu du payload si nécessaire
        $rootName = $xml->getName();
        if ($rootName === self::XMLNS_KEY . ':payload') {
            // Le XML root est le payload, on extrait le contenu interne
            $children = $xml->children();
            if (count($children) === 0) {
                throw new \DomainException("Le payload XML est vide.");
            }
        }

        try {
            return Mandat::makeFromXml($xml);
        } catch (\Throwable $e) {
            throw new \DomainException("Impossible de reconstruire l'objet Mandat à partir du XML : " . $e->getMessage());
        }
    }
}
