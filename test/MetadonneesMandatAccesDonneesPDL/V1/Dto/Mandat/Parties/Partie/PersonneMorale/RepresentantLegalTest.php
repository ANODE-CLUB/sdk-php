<?php

namespace AnodeClub\Test\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use PHPUnit\Framework\TestCase;

class RepresentantLegalTest extends TestCase
{
    public function testConstruct(): void
    {
        $representantLegal = new RepresentantLegal(
            nom: 'Dupont',
            prenom: 'Jean',
            fonction: 'Directeur Général',
            adresseEmail: 'jean.dupont@entreprise.com',
            numeroTelephone: '0123456789'
        );

        $this->assertEquals('Dupont', $representantLegal->nom);
        $this->assertEquals('Jean', $representantLegal->prenom);
        $this->assertEquals('Directeur Général', $representantLegal->fonction);
        $this->assertEquals('jean.dupont@entreprise.com', $representantLegal->adresseEmail);
        $this->assertEquals('0123456789', $representantLegal->numeroTelephone);
    }

    public function testConstructWithEmptyNom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        new RepresentantLegal(
            nom: '',
            prenom: 'Jean',
            fonction: 'Directeur',
            adresseEmail: 'jean@entreprise.com',
            numeroTelephone: '0123456789'
        );
    }

    public function testConstructWithEmptyPrenom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire.');

        new RepresentantLegal(
            nom: 'Dupont',
            prenom: '',
            fonction: 'Directeur',
            adresseEmail: 'jean@entreprise.com',
            numeroTelephone: '0123456789'
        );
    }

    public function testConstructWithEmptyFonction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La fonction est obligatoire.');

        new RepresentantLegal(
            nom: 'Dupont',
            prenom: 'Jean',
            fonction: '',
            adresseEmail: 'jean@entreprise.com',
            numeroTelephone: '0123456789'
        );
    }

    public function testConstructWithEmptyAdresseEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'adresse email est obligatoire.');

        new RepresentantLegal(
            nom: 'Dupont',
            prenom: 'Jean',
            fonction: 'Directeur',
            adresseEmail: '',
            numeroTelephone: '0123456789'
        );
    }

    public function testConstructWithEmptyNumeroTelephone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numéro de téléphone est obligatoire.');

        new RepresentantLegal(
            nom: 'Dupont',
            prenom: 'Jean',
            fonction: 'Directeur',
            adresseEmail: 'jean@entreprise.com',
            numeroTelephone: ''
        );
    }

    public function testBuildXml(): void
    {
        $representantLegal = new RepresentantLegal(
            nom: 'Dupont',
            prenom: 'Jean',
            fonction: 'Directeur Général',
            adresseEmail: 'jean.dupont@entreprise.com',
            numeroTelephone: '0123456789'
        );

        $xml = $representantLegal->buildXml();

        $this->assertStringContainsString('<representantLegal>', $xml);
        $this->assertStringContainsString('<nom>Dupont</nom>', $xml);
        $this->assertStringContainsString('<prenom>Jean</prenom>', $xml);
        $this->assertStringContainsString('<fonction>Directeur Général</fonction>', $xml);
        $this->assertStringContainsString('<adresseEmail>jean.dupont@entreprise.com</adresseEmail>', $xml);
        $this->assertStringContainsString('<numeroTelephone>0123456789</numeroTelephone>', $xml);
        $this->assertStringContainsString('</representantLegal>', $xml);

        // Vérifier que c'est un XML valide
        $domDocument = new \DOMDocument();
        $this->assertTrue($domDocument->loadXML($xml));
    }

    public function testBuildXmlWithSpecialCharacters(): void
    {
        $representantLegal = new RepresentantLegal(
            nom: 'Dupont & Fils',
            prenom: 'Jean-François',
            fonction: 'Directeur < CEO >',
            adresseEmail: 'jean@entreprise.com',
            numeroTelephone: '01.23.45.67.89'
        );

        $xml = $representantLegal->buildXml();

        // Vérifier l'échappement des caractères spéciaux
        $this->assertStringContainsString('<nom>Dupont &amp; Fils</nom>', $xml);
        $this->assertStringContainsString('<fonction>Directeur &lt; CEO &gt;</fonction>', $xml);

        // Vérifier que c'est un XML valide
        $domDocument = new \DOMDocument();
        $this->assertTrue($domDocument->loadXML($xml));
    }

    public function testMakeFromXml(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<representantLegal>
    <nom>Dupont</nom>
    <prenom>Jean</prenom>
    <fonction>Directeur Général</fonction>
    <adresseEmail>jean.dupont@entreprise.com</adresseEmail>
    <numeroTelephone>0123456789</numeroTelephone>
</representantLegal>';

        $simpleXml = new \SimpleXMLElement($xml);
        $representantLegal = RepresentantLegal::makeFromXml($simpleXml);

        $this->assertInstanceOf(RepresentantLegal::class, $representantLegal);
        $this->assertEquals('Dupont', $representantLegal->nom);
        $this->assertEquals('Jean', $representantLegal->prenom);
        $this->assertEquals('Directeur Général', $representantLegal->fonction);
        $this->assertEquals('jean.dupont@entreprise.com', $representantLegal->adresseEmail);
        $this->assertEquals('0123456789', $representantLegal->numeroTelephone);
    }

    public function testMakeFromXmlWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<representantLegal>
    <nom></nom>
    <prenom></prenom>
    <fonction></fonction>
    <adresseEmail></adresseEmail>
    <numeroTelephone></numeroTelephone>
</representantLegal>';

        $simpleXml = new \SimpleXMLElement($xml);
        RepresentantLegal::makeFromXml($simpleXml);
    }

    public function testMakeFromXmlWithMissingElements(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<representantLegal>
</representantLegal>';

        $simpleXml = new \SimpleXMLElement($xml);
        $representantLegal = RepresentantLegal::makeFromXml($simpleXml);

        $this->assertNull($representantLegal);
    }

    public function testMakeFromXmlWithPartialDataThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire.');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<representantLegal>
    <nom>Dupont</nom>
    <prenom></prenom>
    <fonction>Directeur</fonction>
    <adresseEmail>jean@entreprise.com</adresseEmail>
    <numeroTelephone>0123456789</numeroTelephone>
</representantLegal>';

        $simpleXml = new \SimpleXMLElement($xml);
        RepresentantLegal::makeFromXml($simpleXml);
    }

    public function testXmlRoundTrip(): void
    {
        // Test de cycle complet : objet -> XML -> objet
        $original = new RepresentantLegal(
            nom: 'Martin',
            prenom: 'Pierre',
            fonction: 'Président',
            adresseEmail: 'pierre.martin@corp.fr',
            numeroTelephone: '0987654321'
        );

        // Sérialiser en XML
        $xml = $original->buildXml();

        // Désérialiser depuis XML
        $simpleXml = new \SimpleXMLElement($xml);
        $deserialized = RepresentantLegal::makeFromXml($simpleXml);

        // Vérifier que les objets sont équivalents
        $this->assertInstanceOf(RepresentantLegal::class, $deserialized);
        $this->assertEquals($original->nom, $deserialized->nom);
        $this->assertEquals($original->prenom, $deserialized->prenom);
        $this->assertEquals($original->fonction, $deserialized->fonction);
        $this->assertEquals($original->adresseEmail, $deserialized->adresseEmail);
        $this->assertEquals($original->numeroTelephone, $deserialized->numeroTelephone);
    }

    public function testReadonlyProperties(): void
    {
        $representantLegal = new RepresentantLegal(
            nom: 'Test',
            prenom: 'User',
            fonction: 'Testeur',
            adresseEmail: 'test@example.com',
            numeroTelephone: '0000000000'
        );

        $reflection = new \ReflectionClass($representantLegal);

        // Vérifier que toutes les propriétés sont en lecture seule
        $nomProperty = $reflection->getProperty('nom');
        $this->assertTrue($nomProperty->isReadOnly());

        $prenomProperty = $reflection->getProperty('prenom');
        $this->assertTrue($prenomProperty->isReadOnly());

        $fonctionProperty = $reflection->getProperty('fonction');
        $this->assertTrue($fonctionProperty->isReadOnly());

        $adresseEmailProperty = $reflection->getProperty('adresseEmail');
        $this->assertTrue($adresseEmailProperty->isReadOnly());

        $numeroTelephoneProperty = $reflection->getProperty('numeroTelephone');
        $this->assertTrue($numeroTelephoneProperty->isReadOnly());
    }
}
