<?php

namespace Test\Mandat\Dto;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PersonnePhysique;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PersonneMorale;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PersonneMorale\Organisation;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PersonneMorale\RepresentantLegal;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Mandataire;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Mandataire\Delegations;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PointsDeLivraison;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PointsDeLivraison\Prm;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\PointsDeLivraison\Pce;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;

class MetadonneesMandatAccesDonneesPDLTest extends TestCase
{
    private PersonnePhysique $personnePhysique;
    private PersonneMorale $personneMorale;
    private Mandataire $mandataire;
    private Donnees $donnees;
    private PointsDeLivraison $pointsDeLivraison;
    private Consentement $consentement;

    protected function setUp(): void
    {
        $this->personnePhysique = new PersonnePhysique(
            'Dupont',
            'Jean',
            '123 rue de la Paix, 75001 Paris',
            'jean.dupont@example.com',
            '0123456789'
        );

        $this->personneMorale = new PersonneMorale(
            new Organisation('ACME Corp', '123456789', '456 avenue des Entreprises, 69001 Lyon'),
            new RepresentantLegal('Martin', 'Pierre', 'Directeur', 'pierre.martin@acme.com', '0987654321')
        );

        $this->mandataire = new Mandataire(
            'ENERGY PROVIDER',
            '987654321',
            '789 boulevard des Fournisseurs, 13001 Marseille',
            (new Delegations())->add('DYNERGY')->add('Partenaire-1')
        );

        $this->donnees = new Donnees(true, false, true, 24);

        $this->pointsDeLivraison = (new PointsDeLivraison())
            ->add(new Prm('12345678901234'))
            ->add(new Pce('GI123456'));

        $this->consentement = new Consentement(
            new \DateTimeImmutable('2024-01-01T10:00:00+01:00'),
            new \DateTimeImmutable('2024-12-31T23:59:59+01:00')
        );
    }

    public function testConstructorWithPersonnePhysique(): void
    {
        $mandat = new MetadonneesMandatAccesDonneesPDL(
            $this->personnePhysique,
            $this->mandataire,
            $this->donnees,
            $this->pointsDeLivraison,
            $this->consentement
        );

        $this->assertSame($this->personnePhysique, $mandat->mandant);
        $this->assertSame($this->mandataire, $mandat->mandataire);
        $this->assertSame($this->donnees, $mandat->donnees);
        $this->assertSame($this->pointsDeLivraison, $mandat->pointsDeLivraison);
        $this->assertSame($this->consentement, $mandat->consentement);
    }

    public function testConstructorWithPersonneMorale(): void
    {
        $mandat = new MetadonneesMandatAccesDonneesPDL(
            $this->personneMorale,
            $this->mandataire,
            $this->donnees,
            $this->pointsDeLivraison,
            $this->consentement
        );

        $this->assertSame($this->personneMorale, $mandat->mandant);
        $this->assertSame($this->mandataire, $mandat->mandataire);
        $this->assertSame($this->donnees, $mandat->donnees);
        $this->assertSame($this->pointsDeLivraison, $mandat->pointsDeLivraison);
        $this->assertSame($this->consentement, $mandat->consentement);
    }

    public function testBuildXmlWithPersonnePhysique(): void
    {
        $mandat = new MetadonneesMandatAccesDonneesPDL(
            $this->personnePhysique,
            $this->mandataire,
            $this->donnees,
            $this->pointsDeLivraison,
            $this->consentement
        );

        $xml = $mandat->buildXml();

        $this->assertStringContainsString('<personnePhysique>', $xml);
        $this->assertStringContainsString('<nom>Dupont</nom>', $xml);
        $this->assertStringContainsString('<prenom>Jean</prenom>', $xml);
        $this->assertStringContainsString('<mandataire>', $xml);
        $this->assertStringContainsString('<donnees>', $xml);
        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('<consentement>', $xml);
    }

    public function testBuildXmlWithPersonneMorale(): void
    {
        $mandat = new MetadonneesMandatAccesDonneesPDL(
            $this->personneMorale,
            $this->mandataire,
            $this->donnees,
            $this->pointsDeLivraison,
            $this->consentement
        );

        $xml = $mandat->buildXml();

        $this->assertStringContainsString('<personneMorale>', $xml);
        $this->assertStringContainsString('<organisation>', $xml);
        $this->assertStringContainsString('<denominationSociale>ACME Corp</denominationSociale>', $xml);
        $this->assertStringContainsString('<representantLegal>', $xml);
        $this->assertStringContainsString('<nom>Martin</nom>', $xml);
    }

    public function testMakeFromXmlWithPersonnePhysique(): void
    {
        $xmlString = '
        <root>
            <personnePhysique>
                <nom>Test</nom>
                <prenom>User</prenom>
                <adressePostale>Test Address</adressePostale>
                <adresseEmail>test@example.com</adresseEmail>
                <numeroTelephone>0123456789</numeroTelephone>
            </personnePhysique>
            <mandataire>
                <organisation>
                    <denominationSociale>Test Mandataire</denominationSociale>
                    <siren>123456789</siren>
                    <adresseSiegeSocial>Mandataire Address</adresseSiegeSocial>
                </organisation>
            </mandataire>
            <donnees>
                <technique>true</technique>
                <contractuel>false</contractuel>
                <usages>true</usages>
                <dureeHistoriqueEnMois>12</dureeHistoriqueEnMois>
            </donnees>
            <pointsDeLivraison>
                <pointDeLivraison>
                    <prm>12345678901234</prm>
                </pointDeLivraison>
            </pointsDeLivraison>
            <consentement>
                <donneLe>2024-01-01T10:00:00+01:00</donneLe>
                <expireLe>2024-12-31T23:59:59+01:00</expireLe>
            </consentement>
        </root>';

        $xml = simplexml_load_string($xmlString);
        $mandat = MetadonneesMandatAccesDonneesPDL::makeFromXml($xml);

        $this->assertInstanceOf(PersonnePhysique::class, $mandat->mandant);
        $this->assertSame('Test', $mandat->mandant->nom);
        $this->assertSame('User', $mandat->mandant->prenom);
        $this->assertSame('Test Mandataire', $mandat->mandataire->denominationSociale);
    }

    public function testMakeFromXmlWithPersonneMorale(): void
    {
        $xmlString = '
        <root>
            <personneMorale>
                <organisation>
                    <denominationSociale>Test Corp</denominationSociale>
                    <siren>987654321</siren>
                    <adresseSiegeSocial>Corp Address</adresseSiegeSocial>
                </organisation>
                <representantLegal>
                    <nom>Legal</nom>
                    <prenom>Rep</prenom>
                    <fonction>CEO</fonction>
                    <adresseEmail>rep@corp.com</adresseEmail>
                    <numeroTelephone>0198765432</numeroTelephone>
                </representantLegal>
            </personneMorale>
            <mandataire>
                <organisation>
                    <denominationSociale>Test Mandataire</denominationSociale>
                    <siren>123456789</siren>
                    <adresseSiegeSocial>Mandataire Address</adresseSiegeSocial>
                </organisation>
            </mandataire>
            <donnees>
                <technique>false</technique>
                <contractuel>true</contractuel>
                <usages>false</usages>
                <dureeHistoriqueEnMois>36</dureeHistoriqueEnMois>
            </donnees>
            <pointsDeLivraison>
                <pointDeLivraison>
                    <pce>GI789012</pce>
                </pointDeLivraison>
            </pointsDeLivraison>
            <consentement>
                <donneLe>2024-06-01T14:30:00+02:00</donneLe>
                <expireLe>2025-06-01T14:30:00+02:00</expireLe>
            </consentement>
        </root>';

        $xml = simplexml_load_string($xmlString);
        $mandat = MetadonneesMandatAccesDonneesPDL::makeFromXml($xml);

        $this->assertInstanceOf(PersonneMorale::class, $mandat->mandant);
        $this->assertSame('Test Corp', $mandat->mandant->organisation->denominationSociale);
        $this->assertSame('Legal', $mandat->mandant->representantLegal->nom);
    }

    public function testMakeFromXmlWithBothMandants(): void
    {
        // Test edge case: si les deux mandants sont présents, le professionnel l'emporte (dernière affectation)
        $xmlString = '
        <root>
            <personnePhysique>
                <nom>Particulier</nom>
                <prenom>Test</prenom>
                <adressePostale>Address</adressePostale>
                <adresseEmail>part@example.com</adresseEmail>
                <numeroTelephone>0123456789</numeroTelephone>
            </personnePhysique>
            <personneMorale>
                <organisation>
                    <denominationSociale>Professionnel Corp</denominationSociale>
                    <siren>987654321</siren>
                    <adresseSiegeSocial>Corp Address</adresseSiegeSocial>
                </organisation>
                <representantLegal>
                    <nom>Pro</nom>
                    <prenom>Rep</prenom>
                    <fonction>CEO</fonction>
                    <adresseEmail>pro@corp.com</adresseEmail>
                    <numeroTelephone>0198765432</numeroTelephone>
                </representantLegal>
            </personneMorale>
            <mandataire>
                <organisation>
                    <denominationSociale>Test Mandataire</denominationSociale>
                    <siren>123456789</siren>
                    <adresseSiegeSocial>Mandataire Address</adresseSiegeSocial>
                </organisation>
            </mandataire>
            <donnees>
                <technique>true</technique>
                <contractuel>true</contractuel>
                <usages>true</usages>
                <dureeHistoriqueEnMois>24</dureeHistoriqueEnMois>
            </donnees>
            <pointsDeLivraison></pointsDeLivraison>
            <consentement>
                <donneLe>2024-01-01T10:00:00+01:00</donneLe>
                <expireLe>2024-12-31T23:59:59+01:00</expireLe>
            </consentement>
        </root>';

        $xml = simplexml_load_string($xmlString);
        $mandat = MetadonneesMandatAccesDonneesPDL::makeFromXml($xml);

        // Le mandant particulier doit l'emporter car il est traité en dernier
        $this->assertInstanceOf(PersonnePhysique::class, $mandat->mandant);
        $this->assertSame('Particulier', $mandat->mandant->nom);
    }

    public function testRoundTripSerialization(): void
    {
        $originalMandat = new MetadonneesMandatAccesDonneesPDL(
            $this->personnePhysique,
            $this->mandataire,
            $this->donnees,
            $this->pointsDeLivraison,
            $this->consentement
        );

        // Sérialisation
        $xml = $originalMandat->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string('<root>' . $xml . '</root>');
        $reconstructedMandat = MetadonneesMandatAccesDonneesPDL::makeFromXml($xmlElement);

        // Vérifications
        $this->assertInstanceOf(PersonnePhysique::class, $reconstructedMandat->mandant);
        $this->assertSame($originalMandat->mandant->nom, $reconstructedMandat->mandant->nom);
        $this->assertSame($originalMandat->mandant->prenom, $reconstructedMandat->mandant->prenom);
        $this->assertSame($originalMandat->mandataire->denominationSociale, $reconstructedMandat->mandataire->denominationSociale);
        $this->assertSame($originalMandat->donnees->technique, $reconstructedMandat->donnees->technique);
        $this->assertSame($originalMandat->consentement->donneLe->format('c'), $reconstructedMandat->consentement->donneLe->format('c'));
    }
}
