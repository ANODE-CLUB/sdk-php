<?php

namespace AnodeClub\Test\MetadonneesMandatAccesDonneesPDL\V1\Dto;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use PHPUnit\Framework\TestCase;

class MandatTest extends TestCase
{
    private PersonnePhysique $personnePhysique;
    private PersonneMorale $personneMorale;
    private Parties $parties;
    private Objet $objet;
    private Consentement $consentement;

    protected function setUp(): void
    {
        $this->personnePhysique = new PersonnePhysique(
            nom: 'Dupont',
            prenom: 'Jean',
            adressePostale: '123 rue de la Paix, 75001 Paris',
            adresseEmail: 'jean.dupont@email.com',
            numeroTelephone: '0123456789'
        );

        $representantLegal = new RepresentantLegal(
            nom: 'Martin',
            prenom: 'Pierre',
            fonction: 'Directeur',
            adresseEmail: 'pierre.martin@acme.com',
            numeroTelephone: '0987654321'
        );

        $this->personneMorale = new PersonneMorale(
            denominationSociale: 'ACME Corp',
            siren: '123456789',
            adresseSiegeSocial: '456 avenue des Entreprises, 75002 Paris',
            representantLegal: $representantLegal
        );

        $this->parties = new Parties(
            mandant: $this->personnePhysique,
            mandataire: $this->personneMorale
        );

        $donnees = new Donnees(
            technique: true,
            contractuel: false,
            usage: true,
            dureeHistoriqueEnMois: 12
        );

        $pointsDeLivraison = new PointsDeLivraison();
        $pointsDeLivraison->add(new Prm('12345678901234'));
        $pointsDeLivraison->add(new Pce('56789012345678'));

        $delegations = new Delegations();
        $delegations->add('gestionFacture');
        $delegations->add('accesService');

        $this->objet = new Objet(
            donnees: $donnees,
            pointsDeLivraison: $pointsDeLivraison,
            delegations: $delegations
        );

        $this->consentement = new Consentement(
            donneLe: new \DateTimeImmutable('2024-01-01T10:00:00+00:00'),
            expireLe: new \DateTimeImmutable('2025-01-01T10:00:00+00:00')
        );
    }

    public function testConstruct(): void
    {
        $mandat = new Mandat(
            parties: $this->parties,
            objet: $this->objet,
            consentement: $this->consentement
        );

        $this->assertSame($this->parties, $mandat->parties);
        $this->assertSame($this->objet, $mandat->objet);
        $this->assertSame($this->consentement, $mandat->consentement);
    }

    public function testBuildXml(): void
    {
        $mandat = new Mandat(
            parties: $this->parties,
            objet: $this->objet,
            consentement: $this->consentement
        );

        $xml = $mandat->buildXml();

        $this->assertStringContainsString('<mandat>', $xml);
        $this->assertStringContainsString('</mandat>', $xml);
        $this->assertStringContainsString('<parties>', $xml);
        $this->assertStringContainsString('<objet>', $xml);
        $this->assertStringContainsString('<consentement>', $xml);

        // Vérifier que c'est un XML valide
        $domDocument = new \DOMDocument();
        $this->assertTrue($domDocument->loadXML($xml));

        // Vérifier la structure XML spécifique
        $simpleXml = new \SimpleXMLElement($xml);
        $this->assertEquals('mandat', $simpleXml->getName());
        $this->assertTrue(isset($simpleXml->parties));
        $this->assertTrue(isset($simpleXml->objet));
        $this->assertTrue(isset($simpleXml->consentement));
    }

    public function testMakeFromXml(): void
    {
        // Créer un XML de test valide basé sur la vraie structure
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<mandat>
    <parties>
        <mandant>
            <personnePhysique>
                <nom>Dupont</nom>
                <prenom>Jean</prenom>
                <adressePostale>123 rue de la Paix, 75001 Paris</adressePostale>
                <adresseEmail>jean.dupont@email.com</adresseEmail>
                <numeroTelephone>0123456789</numeroTelephone>
            </personnePhysique>
        </mandant>
        <mandataire>
            <personneMorale>
                <denominationSociale>ACME Corp</denominationSociale>
                <siren>123456789</siren>
                <adresseSiegeSocial>456 avenue des Entreprises, 75002 Paris</adresseSiegeSocial>
                <representantLegal>
                    <nom>Martin</nom>
                    <prenom>Pierre</prenom>
                    <fonction>Directeur</fonction>
                    <adresseEmail>pierre.martin@acme.com</adresseEmail>
                    <numeroTelephone>0987654321</numeroTelephone>
                </representantLegal>
            </personneMorale>
        </mandataire>
    </parties>
    <objet>
        <donnees>
            <technique>true</technique>
            <contractuel>false</contractuel>
            <usage>true</usage>
            <dureeHistoriqueEnMois>12</dureeHistoriqueEnMois>
        </donnees>
        <pointsDeLivraison>
            <prm>12345678901234</prm>
            <pce>56789012345678</pce>
        </pointsDeLivraison>
        <delegations>
            <delegation>gestionFacture</delegation>
            <delegation>accesService</delegation>
        </delegations>
    </objet>
    <consentement>
        <donneLe>2024-01-01T10:00:00+00:00</donneLe>
        <expireLe>2025-01-01T10:00:00+00:00</expireLe>
    </consentement>
</mandat>';

        $simpleXml = new \SimpleXMLElement($xml);
        $mandat = Mandat::makeFromXml($simpleXml);

        $this->assertInstanceOf(Mandat::class, $mandat);
        $this->assertInstanceOf(Parties::class, $mandat->parties);
        $this->assertInstanceOf(Objet::class, $mandat->objet);
        $this->assertInstanceOf(Consentement::class, $mandat->consentement);

        // Vérifier les données des parties
        $this->assertInstanceOf(PersonnePhysique::class, $mandat->parties->mandant);
        $this->assertEquals('Dupont', $mandat->parties->mandant->nom);
        $this->assertEquals('Jean', $mandat->parties->mandant->prenom);

        $this->assertInstanceOf(PersonneMorale::class, $mandat->parties->mandataire);
        $this->assertEquals('ACME Corp', $mandat->parties->mandataire->denominationSociale);
        $this->assertEquals('123456789', $mandat->parties->mandataire->siren);

        // Vérifier les dates du consentement
        $this->assertEquals('2024-01-01T10:00:00+00:00', $mandat->consentement->donneLe->format('c'));
        $this->assertEquals('2025-01-01T10:00:00+00:00', $mandat->consentement->expireLe->format('c'));

        // Vérifier les données de l'objet
        $this->assertTrue($mandat->objet->donnees->technique);
        $this->assertFalse($mandat->objet->donnees->contractuel);
        $this->assertTrue($mandat->objet->donnees->usage);
        $this->assertEquals(12, $mandat->objet->donnees->dureeHistoriqueEnMois);
    }

    public function testMakeFromXmlWithInvalidXml(): void
    {
        $this->expectException(\Error::class);

        $invalidXml = '<?xml version="1.0" encoding="UTF-8"?>
<invalid>
    <missing>data</missing>
</invalid>';

        $simpleXml = new \SimpleXMLElement($invalidXml);
        Mandat::makeFromXml($simpleXml);
    }

    public function testXmlRoundTrip(): void
    {
        // Test complet : construire un objet, le sérialiser en XML, puis le désérialiser
        $originalMandat = new Mandat(
            parties: $this->parties,
            objet: $this->objet,
            consentement: $this->consentement
        );

        // Sérialiser en XML
        $xml = $originalMandat->buildXml();

        // Désérialiser depuis XML
        $simpleXml = new \SimpleXMLElement($xml);
        $deserializedMandat = Mandat::makeFromXml($simpleXml);

        // Vérifier que les objets sont équivalents
        $this->assertEquals(
            $originalMandat->parties->mandant->nom,
            $deserializedMandat->parties->mandant->nom
        );
        $this->assertEquals(
            $originalMandat->parties->mandataire->denominationSociale,
            $deserializedMandat->parties->mandataire->denominationSociale
        );
        $this->assertEquals(
            $originalMandat->consentement->donneLe->format('c'),
            $deserializedMandat->consentement->donneLe->format('c')
        );
    }

    public function testXmlStructureIntegrity(): void
    {
        $mandat = new Mandat(
            parties: $this->parties,
            objet: $this->objet,
            consentement: $this->consentement
        );

        $xml = $mandat->buildXml();

        // Vérifier que le XML contient toutes les sections attendues
        $this->assertStringContainsString('<mandat>', $xml);
        $this->assertStringContainsString('<parties>', $xml);
        $this->assertStringContainsString('<mandant>', $xml);
        $this->assertStringContainsString('<mandataire>', $xml);
        $this->assertStringContainsString('<objet>', $xml);
        $this->assertStringContainsString('<donnees>', $xml);
        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('<delegations>', $xml);
        $this->assertStringContainsString('<consentement>', $xml);
        $this->assertStringContainsString('<donneLe>', $xml);
        $this->assertStringContainsString('<expireLe>', $xml);
        $this->assertStringContainsString('</mandat>', $xml);

        // Vérifier que c'est un XML bien formé
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($xml);
        $this->assertTrue($loaded, 'Le XML généré doit être bien formé');

        $errors = libxml_get_errors();
        $this->assertEmpty($errors, 'Le XML ne doit pas contenir d\'erreurs');
        libxml_clear_errors();
    }

    public function testReadonlyProperties(): void
    {
        $mandat = new Mandat(
            parties: $this->parties,
            objet: $this->objet,
            consentement: $this->consentement
        );

        // Vérifier que les propriétés sont en lecture seule
        $reflection = new \ReflectionClass($mandat);

        $partiesProperty = $reflection->getProperty('parties');
        $this->assertTrue($partiesProperty->isReadOnly());

        $objetProperty = $reflection->getProperty('objet');
        $this->assertTrue($objetProperty->isReadOnly());

        $consentementProperty = $reflection->getProperty('consentement');
        $this->assertTrue($consentementProperty->isReadOnly());
    }
}
