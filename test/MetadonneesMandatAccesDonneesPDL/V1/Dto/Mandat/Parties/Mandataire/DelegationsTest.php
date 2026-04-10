<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;

class DelegationsTest extends TestCase
{
    public function testConstructor(): void
    {
        $delegations = new Delegations();

        // Le constructeur ne devrait pas lever d'exception
        $this->assertInstanceOf(Delegations::class, $delegations);
    }

    public function testAddSingleDelegation(): void
    {
        $delegations = new Delegations();
        $result = $delegations->add('DYNERGY');

        // Test fluent interface
        $this->assertSame($delegations, $result);

        // Vérifier que la délégation a été ajoutée
        $delegationsArray = [];
        foreach ($delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }

        $this->assertContains('DYNERGY', $delegationsArray);
        $this->assertCount(1, $delegationsArray);
    }

    public function testAddMultipleDelegations(): void
    {
        $delegations = new Delegations();
        $delegations->add('DYNERGY')
                   ->add('FOURNISSEUR-1')
                   ->add('PARTENAIRE-2');

        $delegationsArray = [];
        foreach ($delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }

        $this->assertCount(3, $delegationsArray);
        $this->assertContains('DYNERGY', $delegationsArray);
        $this->assertContains('FOURNISSEUR-1', $delegationsArray);
        $this->assertContains('PARTENAIRE-2', $delegationsArray);

        // Vérifier l'ordre
        $this->assertSame('DYNERGY', $delegationsArray[0]);
        $this->assertSame('FOURNISSEUR-1', $delegationsArray[1]);
        $this->assertSame('PARTENAIRE-2', $delegationsArray[2]);
    }

    public function testAddEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La délégation est obligatoire.');

        $delegations = new Delegations();
        $delegations->add('');
    }

    public function testAddDuplicates(): void
    {
        $delegations = new Delegations();
        $delegations->add('DYNERGY')
                   ->add('FOURNISSEUR')
                   ->add('DYNERGY'); // Duplicate

        $delegationsArray = [];
        foreach ($delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }

        // Les doublons ne sont pas filtrés
        $this->assertCount(3, $delegationsArray);
        $this->assertSame(['DYNERGY', 'FOURNISSEUR', 'DYNERGY'], $delegationsArray);
    }

    public function testAddSpecialCharacters(): void
    {
        $delegations = new Delegations();
        $delegations->add('TEST & CO')
                   ->add('SPECIAL <CHARS>')
                   ->add('UTF-8: éàçü')
                   ->add('Symbols: @#$%^&*()');

        $delegationsArray = [];
        foreach ($delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }

        $this->assertCount(4, $delegationsArray);
        $this->assertContains('TEST & CO', $delegationsArray);
        $this->assertContains('SPECIAL <CHARS>', $delegationsArray);
        $this->assertContains('UTF-8: éàçü', $delegationsArray);
        $this->assertContains('Symbols: @#$%^&*()', $delegationsArray);
    }

    public function testIterateEmptyDelegations(): void
    {
        $delegations = new Delegations();

        $delegationsArray = [];
        foreach ($delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }

        $this->assertEmpty($delegationsArray);
    }

    public function testIterateMultipleTimes(): void
    {
        $delegations = new Delegations();
        $delegations->add('DEL1')->add('DEL2');

        // Premier parcours
        $firstIteration = [];
        foreach ($delegations->iterate() as $delegation) {
            $firstIteration[] = $delegation;
        }

        // Deuxième parcours
        $secondIteration = [];
        foreach ($delegations->iterate() as $delegation) {
            $secondIteration[] = $delegation;
        }

        // Les deux parcours doivent être identiques
        $this->assertSame($firstIteration, $secondIteration);
        $this->assertCount(2, $firstIteration);
    }

    public function testIterateReturnsGenerator(): void
    {
        $delegations = new Delegations();
        $delegations->add('TEST');

        $generator = $delegations->iterate();

        $this->assertInstanceOf(\Generator::class, $generator);
    }

    public function testFluentInterface(): void
    {
        $delegations = new Delegations();

        // Test que chaque add() retourne la même instance
        $result1 = $delegations->add('DEL1');
        $result2 = $result1->add('DEL2');
        $result3 = $result2->add('DEL3');

        $this->assertSame($delegations, $result1);
        $this->assertSame($delegations, $result2);
        $this->assertSame($delegations, $result3);
    }

    public function testPropertyInitialization(): void
    {
        // Test du bug potentiel: propriété $delegations non initialisée
        $delegations = new Delegations();

        // L'accès direct à la propriété devrait être possible
        $this->assertTrue(property_exists($delegations, 'delegations'));

        // Après construction, la propriété pourrait être non initialisée
        // Mais l'accès via add() devrait fonctionner
        $delegations->add('TEST');

        // Vérifier que la propriété est maintenant initialisée
        $this->assertIsArray($delegations->delegations);
        $this->assertContains('TEST', $delegations->delegations);
    }

    public function testDirectPropertyAccess(): void
    {
        $delegations = new Delegations();
        $delegations->add('DEL1')->add('DEL2');

        // Accès direct à la propriété publique
        $this->assertIsArray($delegations->delegations);
        $this->assertCount(2, $delegations->delegations);
        $this->assertContains('DEL1', $delegations->delegations);
        $this->assertContains('DEL2', $delegations->delegations);
    }

    public function testLargeNumberOfDelegations(): void
    {
        $delegations = new Delegations();

        // Ajouter un grand nombre de délégations
        for ($i = 0; $i < 1000; $i++) {
            $delegations->add("DELEGATION_$i");
        }

        $count = 0;
        foreach ($delegations->iterate() as $delegation) {
            $count++;
        }

        $this->assertSame(1000, $count);
        $this->assertCount(1000, $delegations->delegations);
    }

    public function testNotReadonlyClass(): void
    {
        $delegations = new Delegations();

        // Vérifier que cette classe n'est PAS readonly (contrairement aux autres)
        $reflection = new \ReflectionClass($delegations);
        $this->assertFalse($reflection->isReadOnly());
    }
}
