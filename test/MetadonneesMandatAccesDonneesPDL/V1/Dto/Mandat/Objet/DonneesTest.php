<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;

class DonneesTest extends TestCase
{
    public function testConstructor(): void
    {
        $donnees = new Donnees(true, false, true, 24);

        $this->assertTrue($donnees->technique);
        $this->assertFalse($donnees->contractuel);
        $this->assertTrue($donnees->usage);
        $this->assertSame(24, $donnees->dureeHistoriqueEnMois);
    }

    public function testConstructorAllTrue(): void
    {
        $donnees = new Donnees(true, true, true, 36);

        $this->assertTrue($donnees->technique);
        $this->assertTrue($donnees->contractuel);
        $this->assertTrue($donnees->usage);
        $this->assertSame(36, $donnees->dureeHistoriqueEnMois);
    }

    public function testConstructorAllFalse(): void
    {
        $donnees = new Donnees(false, false, false, 12);

        $this->assertFalse($donnees->technique);
        $this->assertFalse($donnees->contractuel);
        $this->assertFalse($donnees->usage);
        $this->assertSame(12, $donnees->dureeHistoriqueEnMois);
    }

    public function testConstructorWithZeroDuration(): void
    {
        $donnees = new Donnees(true, true, true, 0);

        $this->assertSame(0, $donnees->dureeHistoriqueEnMois);
    }

    public function testConstructorWithNegativeDuration(): void
    {
        // Test edge case: durée négative (pas de validation dans le constructeur)
        $donnees = new Donnees(true, true, true, -12);

        $this->assertSame(-12, $donnees->dureeHistoriqueEnMois);
    }

    public function testConstructorWithLargeDuration(): void
    {
        $donnees = new Donnees(true, true, true, 999999);

        $this->assertSame(999999, $donnees->dureeHistoriqueEnMois);
    }

    public function testBuildXmlAllTrue(): void
    {
        $donnees = new Donnees(true, true, true, 24);

        $xml = $donnees->buildXml();

        $this->assertStringContainsString('<donnees>', $xml);
        $this->assertStringContainsString('<technique>true</technique>', $xml);
        $this->assertStringContainsString('<contractuel>true</contractuel>', $xml);
        $this->assertStringContainsString('<usage>true</usage>', $xml);
        $this->assertStringContainsString('<dureeHistoriqueEnMois>24</dureeHistoriqueEnMois>', $xml);
        $this->assertStringContainsString('</donnees>', $xml);
    }

    public function testBuildXmlAllFalse(): void
    {
        $donnees = new Donnees(false, false, false, 12);

        $xml = $donnees->buildXml();

        $this->assertStringContainsString('<technique>false</technique>', $xml);
        $this->assertStringContainsString('<contractuel>false</contractuel>', $xml);
        $this->assertStringContainsString('<usage>false</usage>', $xml);
        $this->assertStringContainsString('<dureeHistoriqueEnMois>12</dureeHistoriqueEnMois>', $xml);
    }

    public function testBuildXmlMixed(): void
    {
        $donnees = new Donnees(true, false, true, 36);

        $xml = $donnees->buildXml();

        $this->assertStringContainsString('<technique>true</technique>', $xml);
        $this->assertStringContainsString('<contractuel>false</contractuel>', $xml);
        $this->assertStringContainsString('<usage>true</usage>', $xml);
        $this->assertStringContainsString('<dureeHistoriqueEnMois>36</dureeHistoriqueEnMois>', $xml);
    }

    public function testBuildXmlWithZeroDuration(): void
    {
        $donnees = new Donnees(true, true, true, 0);

        $xml = $donnees->buildXml();

        $this->assertStringContainsString('<dureeHistoriqueEnMois>0</dureeHistoriqueEnMois>', $xml);
    }

    public function testBuildXmlWithNegativeDuration(): void
    {
        $donnees = new Donnees(true, true, true, -24);

        $xml = $donnees->buildXml();

        $this->assertStringContainsString('<dureeHistoriqueEnMois>-24</dureeHistoriqueEnMois>', $xml);
    }

    public function testMakeFromXmlAllTrue(): void
    {
        $xmlString = '
        <donnees>
            <technique>true</technique>
            <contractuel>true</contractuel>
            <usage>true</usage>
            <dureeHistoriqueEnMois>24</dureeHistoriqueEnMois>
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        $this->assertTrue($donnees->technique);
        $this->assertTrue($donnees->contractuel);
        $this->assertTrue($donnees->usage);
        $this->assertSame(24, $donnees->dureeHistoriqueEnMois);
    }

    public function testMakeFromXmlAllFalse(): void
    {
        $xmlString = '
        <donnees>
            <technique>false</technique>
            <contractuel>false</contractuel>
            <usage>false</usage>
            <dureeHistoriqueEnMois>12</dureeHistoriqueEnMois>
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        $this->assertFalse($donnees->technique);
        $this->assertFalse($donnees->contractuel);
        $this->assertFalse($donnees->usage);
        $this->assertSame(12, $donnees->dureeHistoriqueEnMois);
    }

    public function testMakeFromXmlMixed(): void
    {
        $xmlString = '
        <donnees>
            <technique>true</technique>
            <contractuel>false</contractuel>
            <usage>true</usage>
            <dureeHistoriqueEnMois>36</dureeHistoriqueEnMois>
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        $this->assertTrue($donnees->technique);
        $this->assertFalse($donnees->contractuel);
        $this->assertTrue($donnees->usage);
        $this->assertSame(36, $donnees->dureeHistoriqueEnMois);
    }

    public function testMakeFromXmlWithStringBooleans(): void
    {
        // Test avec différentes représentations de booléens
        $xmlString = '
        <donnees>
            <technique>true</technique>
            <contractuel>false</contractuel>
            <usage>true</usage>
            <dureeHistoriqueEnMois>48</dureeHistoriqueEnMois>
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        // La conversion should handle string parsing
        $this->assertTrue($donnees->technique); // "true" -> true
        $this->assertFalse($donnees->contractuel); // "false" -> false
        $this->assertTrue($donnees->usage); // "true" -> true
    }

    public function testMakeFromXmlWithMissingElements(): void
    {
        $xmlString = '
        <donnees>
            <technique>true</technique>
            <!-- Éléments manquants -->
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        $this->assertTrue($donnees->technique);
        $this->assertFalse($donnees->contractuel); // Valeur par défaut pour élément manquant
        $this->assertFalse($donnees->usage);
        $this->assertSame(0, $donnees->dureeHistoriqueEnMois); // Cast de string vide -> 0
    }

    public function testMakeFromXmlWithZeroDuration(): void
    {
        $xmlString = '
        <donnees>
            <technique>true</technique>
            <contractuel>true</contractuel>
            <usage>true</usage>
            <dureeHistoriqueEnMois>0</dureeHistoriqueEnMois>
        </donnees>';

        $xml = simplexml_load_string($xmlString);
        $donnees = Donnees::makeFromXml($xml);

        $this->assertSame(0, $donnees->dureeHistoriqueEnMois);
    }

    public function testRoundTripSerializationAllTrue(): void
    {
        $originalDonnees = new Donnees(true, true, true, 24);

        // Sérialisation
        $xml = $originalDonnees->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedDonnees = Donnees::makeFromXml($xmlElement);

        $this->assertSame($originalDonnees->technique, $reconstructedDonnees->technique);
        $this->assertSame($originalDonnees->contractuel, $reconstructedDonnees->contractuel);
        $this->assertSame($originalDonnees->usage, $reconstructedDonnees->usage);
        $this->assertSame($originalDonnees->dureeHistoriqueEnMois, $reconstructedDonnees->dureeHistoriqueEnMois);
    }

    public function testRoundTripSerializationAllFalse(): void
    {
        $originalDonnees = new Donnees(false, false, false, 12);

        // Sérialisation
        $xml = $originalDonnees->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedDonnees = Donnees::makeFromXml($xmlElement);

        $this->assertSame($originalDonnees->technique, $reconstructedDonnees->technique);
        $this->assertSame($originalDonnees->contractuel, $reconstructedDonnees->contractuel);
        $this->assertSame($originalDonnees->usage, $reconstructedDonnees->usage);
        $this->assertSame($originalDonnees->dureeHistoriqueEnMois, $reconstructedDonnees->dureeHistoriqueEnMois);
    }

    public function testRoundTripSerializationMixed(): void
    {
        $originalDonnees = new Donnees(true, false, true, 36);

        // Sérialisation
        $xml = $originalDonnees->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedDonnees = Donnees::makeFromXml($xmlElement);

        $this->assertSame($originalDonnees->technique, $reconstructedDonnees->technique);
        $this->assertSame($originalDonnees->contractuel, $reconstructedDonnees->contractuel);
        $this->assertSame($originalDonnees->usage, $reconstructedDonnees->usage);
        $this->assertSame($originalDonnees->dureeHistoriqueEnMois, $reconstructedDonnees->dureeHistoriqueEnMois);
    }

    public function testRoundTripSerializationWithEdgeCases(): void
    {
        // Test avec valeurs edge cases
        $originalDonnees = new Donnees(true, false, true, -12);

        // Sérialisation
        $xml = $originalDonnees->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedDonnees = Donnees::makeFromXml($xmlElement);

        $this->assertSame($originalDonnees->technique, $reconstructedDonnees->technique);
        $this->assertSame($originalDonnees->contractuel, $reconstructedDonnees->contractuel);
        $this->assertSame($originalDonnees->usage, $reconstructedDonnees->usage);
        $this->assertSame($originalDonnees->dureeHistoriqueEnMois, $reconstructedDonnees->dureeHistoriqueEnMois);
    }

    public function testImmutability(): void
    {
        $donnees = new Donnees(true, false, true, 24);

        // Vérifier que la classe est readonly
        $this->assertTrue((new \ReflectionClass($donnees))->isReadOnly());
    }

    public function testPublicProperties(): void
    {
        $donnees = new Donnees(true, false, true, 24);

        // Vérifier que toutes les propriétés sont publiques et accessibles
        $reflection = new \ReflectionClass($donnees);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->assertCount(4, $properties);

        $propertyNames = array_map(fn ($prop) => $prop->getName(), $properties);
        $this->assertContains('technique', $propertyNames);
        $this->assertContains('contractuel', $propertyNames);
        $this->assertContains('usage', $propertyNames);
        $this->assertContains('dureeHistoriqueEnMois', $propertyNames);
    }
}
