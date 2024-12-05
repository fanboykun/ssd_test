<?php

use App\Models\Person;
use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Person\IndexPerson;
use function Pest\Laravel\{get, post, put, delete};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('dashboard page can be rendered', function () {
    get('/dashboard')
        ->assertOk()
        ->assertSee('Persons');
});

test('can list persons with pagination', function () {
    // Arrange
    $persons = Person::factory()->count(15)->create();

    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->assertViewHas('persons')
        ->assertSee($persons[$persons->count() - 1]->name)
        ->assertSee('Showing')
        ->assertSee('of')
        ->assertSee('results');
});

test('can search persons', function () {
    // Arrange
    $persons = Person::factory()->count(5)->create();

    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->set('search', $persons[0]->name)
        ->assertSee($persons[0]->name);

});

test('can create new person', function () {
    // Arrange
    $personData = [
        'name' => 'Jane Doe',
        'birthday' => '1990-01-01',
        'residence' => 'New York'
    ];

    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->set('personForm.name', $personData['name'])
        ->set('personForm.birthday', $personData['birthday'])
        ->set('personForm.residence', $personData['residence'])
        ->call('handleAdd')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('persons', $personData);
});

test('validates required fields when creating person', function () {
    Livewire::test(IndexPerson::class)
        ->set('personForm.name', '')
        ->set('personForm.birthday', '')
        ->set('personForm.residence', '')
        ->call('handleAdd')
        ->assertHasErrors(['personForm.name', 'personForm.birthday', 'personForm.residence']);
});

test('can update existing person', function () {
    // Arrange
    $person = Person::factory()->create();
    $updatedData = [
        'name' => 'Updated Name',
        'birthday' => '1995-05-05',
        'residence' => 'Updated City'
    ];

    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->set('personForm.person_id', $person->id)
        ->set('personForm.name', $updatedData['name'])
        ->set('personForm.birthday', $updatedData['birthday'])
        ->set('personForm.residence', $updatedData['residence'])
        ->call('handleEdit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('persons', $updatedData);
});

test('can delete person', function () {
    // Arrange
    $person = Person::factory()->create();

    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->set('personForm.person_id', $person->id)
        ->call('handleDelete');

    $this->assertDatabaseMissing('persons', ['id' => $person->id]);
});
