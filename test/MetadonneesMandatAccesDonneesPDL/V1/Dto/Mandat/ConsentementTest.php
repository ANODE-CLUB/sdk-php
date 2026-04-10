<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;

class ConsentementTest extends TestCase
{
    public function testConstructor(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:00:00+01:00');
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');

        $consentement = new Consentement($donneLe, $expireLe);

        $this->assertSame($donneLe, $consentement->donneLe);
        $this->assertSame($expireLe, $consentement->expireLe);
    }

    public function testConstructorWithSameDate(): void
    {
        $date = new \DateTimeImmutable('2024-06-15T12:00:00+02:00');

        $consentement = new Consentement($date, $date);

        $this->assertSame($date, $consentement->donneLe);
        $this->assertSame($date, $consentement->expireLe);
    }

    public function testConstructorWithExpirationBeforeConsentement(): void
    {
        // Edge case: expiration avant consentement (doit lancer une exception)
        $donneLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');
        $expireLe = new \DateTimeImmutable('2024-01-01T10:00:00+01:00');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date à laquelle le consentement a été donné doit être antérieure à son expiration.');

        new Consentement($donneLe, $expireLe);
    }

    public function testConstructorWithDifferentTimezones(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:00:00+01:00'); // Europe/Paris
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59-05:00'); // America/New_York

        $consentement = new Consentement($donneLe, $expireLe);

        $this->assertSame($donneLe, $consentement->donneLe);
        $this->assertSame($expireLe, $consentement->expireLe);
        $this->assertSame('+01:00', $consentement->donneLe->format('P'));
        $this->assertSame('-05:00', $consentement->expireLe->format('P'));
    }

    public function testBuildXml(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:30:45+01:00');
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');

        $consentement = new Consentement($donneLe, $expireLe);
        $xml = $consentement->buildXml();

        $this->assertStringContainsString('<consentement>', $xml);
        $this->assertStringContainsString('<donneLe>2024-01-01T10:30:45+01:00</donneLe>', $xml);
        $this->assertStringContainsString('<expireLe>2024-12-31T23:59:59+01:00</expireLe>', $xml);
        $this->assertStringContainsString('</consentement>', $xml);
    }

    public function testBuildXmlWithDifferentTimezones(): void
    {
        $donneLe = new \DateTimeImmutable('2024-06-15T14:30:00+02:00'); // Heure d'été
        $expireLe = new \DateTimeImmutable('2024-12-15T14:30:00+01:00'); // Heure d'hiver

        $consentement = new Consentement($donneLe, $expireLe);
        $xml = $consentement->buildXml();

        $this->assertStringContainsString('<donneLe>2024-06-15T14:30:00+02:00</donneLe>', $xml);
        $this->assertStringContainsString('<expireLe>2024-12-15T14:30:00+01:00</expireLe>', $xml);
    }

    public function testBuildXmlWithUtcTimezone(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:00:00+00:00');
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59+00:00');

        $consentement = new Consentement($donneLe, $expireLe);
        $xml = $consentement->buildXml();

        $this->assertStringContainsString('<donneLe>2024-01-01T10:00:00+00:00</donneLe>', $xml);
        $this->assertStringContainsString('<expireLe>2024-12-31T23:59:59+00:00</expireLe>', $xml);
    }

    public function testMakeFromXml(): void
    {
        $xmlString = '
        <consentement>
            <donneLe>2024-01-01T10:30:45+01:00</donneLe>
            <expireLe>2024-12-31T23:59:59+01:00</expireLe>
        </consentement>';

        $xml = simplexml_load_string($xmlString);
        $consentement = Consentement::makeFromXml($xml);

        $expectedConsentementeLe = new \DateTimeImmutable('2024-01-01T10:30:45+01:00');
        $expectedExpirationLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');

        $this->assertEquals($expectedConsentementeLe, $consentement->donneLe);
        $this->assertEquals($expectedExpirationLe, $consentement->expireLe);
    }

    public function testMakeFromXmlWithDifferentTimezones(): void
    {
        $xmlString = '
        <consentement>
            <donneLe>2024-06-15T14:30:00+02:00</donneLe>
            <expireLe>2024-12-15T14:30:00-05:00</expireLe>
        </consentement>';

        $xml = simplexml_load_string($xmlString);
        $consentement = Consentement::makeFromXml($xml);

        $this->assertSame('2024-06-15T14:30:00+02:00', $consentement->donneLe->format('c'));
        $this->assertSame('2024-12-15T14:30:00-05:00', $consentement->expireLe->format('c'));
    }

    public function testMakeFromXmlWithUtc(): void
    {
        $xmlString = '
        <consentement>
            <donneLe>2024-01-01T10:00:00+00:00</donneLe>
            <expireLe>2024-12-31T23:59:59+00:00</expireLe>
        </consentement>';

        $xml = simplexml_load_string($xmlString);
        $consentement = Consentement::makeFromXml($xml);

        $this->assertSame('2024-01-01T10:00:00+00:00', $consentement->donneLe->format('c'));
        $this->assertSame('2024-12-31T23:59:59+00:00', $consentement->expireLe->format('c'));
    }

    public function testMakeFromXmlWithMissingElements(): void
    {
        $xmlString = '
        <consentement>
            <donneLe>2024-01-01T10:00:00+01:00</donneLe>
            <!-- expireLe manquant -->
        </consentement>';

        $xml = simplexml_load_string($xmlString);
        $consentement = Consentement::makeFromXml($xml);

        // SimpleXML retourne une chaîne vide pour un élément manquant
        // DateTimeImmutable avec une chaîne vide utilise la date actuelle
        $this->assertSame('2024-01-01T10:00:00+01:00', $consentement->donneLe->format('c'));
        // Au lieu de vérifier une date spécifique, vérifions que c'était récente (maintenant ± 10 secondes)
        $now = new \DateTimeImmutable();
        $diff = abs($consentement->expireLe->getTimestamp() - $now->getTimestamp());
        $this->assertLessThan(10, $diff, 'La date d\'expiration manquante doit être proche de maintenant');
    }

    public function testMakeFromXmlWithInvalidDate(): void
    {
        $xmlString = '
        <consentement>
            <donneLe>invalid-date</donneLe>
            <expireLe>2024-12-31T23:59:59+01:00</expireLe>
        </consentement>';

        // DateTimeImmutable devrait lever une exception pour un format de date invalide
        $this->expectException(\Exception::class);

        $xml = simplexml_load_string($xmlString);
        Consentement::makeFromXml($xml);
    }

    public function testRoundTripSerialization(): void
    {
        $originalConsentementeLe = new \DateTimeImmutable('2024-03-15T16:45:30+01:00');
        $originalExpirationLe = new \DateTimeImmutable('2025-03-15T16:45:30+01:00');

        $originalConsentement = new Consentement($originalConsentementeLe, $originalExpirationLe);

        // Sérialisation
        $xml = $originalConsentement->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedConsentement = Consentement::makeFromXml($xmlElement);

        $this->assertEquals($originalConsentement->donneLe, $reconstructedConsentement->donneLe);
        $this->assertEquals($originalConsentement->expireLe, $reconstructedConsentement->expireLe);
        $this->assertSame($originalConsentement->donneLe->format('c'), $reconstructedConsentement->donneLe->format('c'));
        $this->assertSame($originalConsentement->expireLe->format('c'), $reconstructedConsentement->expireLe->format('c'));
    }

    public function testRoundTripSerializationWithDifferentTimezones(): void
    {
        $originalConsentementeLe = new \DateTimeImmutable('2024-06-15T14:30:00+02:00');
        $originalExpirationLe = new \DateTimeImmutable('2024-12-15T09:30:00-05:00');

        $originalConsentement = new Consentement($originalConsentementeLe, $originalExpirationLe);

        // Sérialisation
        $xml = $originalConsentement->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedConsentement = Consentement::makeFromXml($xmlElement);

        $this->assertSame($originalConsentement->donneLe->format('c'), $reconstructedConsentement->donneLe->format('c'));
        $this->assertSame($originalConsentement->expireLe->format('c'), $reconstructedConsentement->expireLe->format('c'));
    }

    public function testEdgeCaseDatesAroundDstChanges(): void
    {
        // Test autour des changements d'heure d'été/hiver
        $donneLe = new \DateTimeImmutable('2024-03-31T01:30:00+01:00'); // Avant passage heure d'été
        $expireLe = new \DateTimeImmutable('2024-10-27T01:30:00+01:00'); // Après passage heure d'hiver

        $consentement = new Consentement($donneLe, $expireLe);

        // Sérialisation/désérialisation doit préserver les timezones
        $xml = $consentement->buildXml();
        $xmlElement = simplexml_load_string($xml);
        $reconstructedConsentement = Consentement::makeFromXml($xmlElement);

        $this->assertSame($consentement->donneLe->format('c'), $reconstructedConsentement->donneLe->format('c'));
        $this->assertSame($consentement->expireLe->format('c'), $reconstructedConsentement->expireLe->format('c'));
    }

    public function testImmutability(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:00:00+01:00');
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');

        $consentement = new Consentement($donneLe, $expireLe);

        // Vérifier que la classe est readonly
        $this->assertTrue((new \ReflectionClass($consentement))->isReadOnly());
    }

    public function testPublicProperties(): void
    {
        $donneLe = new \DateTimeImmutable('2024-01-01T10:00:00+01:00');
        $expireLe = new \DateTimeImmutable('2024-12-31T23:59:59+01:00');

        $consentement = new Consentement($donneLe, $expireLe);

        // Vérifier que toutes les propriétés sont publiques et accessibles
        $reflection = new \ReflectionClass($consentement);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->assertCount(2, $properties);

        $propertyNames = array_map(fn ($prop) => $prop->getName(), $properties);
        $this->assertContains('donneLe', $propertyNames);
        $this->assertContains('expireLe', $propertyNames);
    }
}
