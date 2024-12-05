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
        ->assertSee('Persons Management');
});

test('can list persons with pagination', function () {
    // Arrange
    $persons = Person::factory()->count(15)->create();
    
    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->assertViewHas('persons')
        ->assertSee($persons[0]->name)
        ->assertSee('Showing')
        ->assertSee('of')
        ->assertSee('results');
});

test('can search persons', function () {
    // Arrange
    $searchPerson = Person::factory()->create(['name' => 'John Doe']);
    Person::factory()->count(5)->create();
    
    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee(Person::first()->name);
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
        ->set('form.name', $personData['name'])
        ->set('form.birthday', $personData['birthday'])
        ->set('form.residence', $personData['residence'])
        ->call('store')
        ->assertHasNoErrors()
        ->assertEmitted('person-saved');
        
    $this->assertDatabaseHas('persons', $personData);
});

test('validates required fields when creating person', function () {
    Livewire::test(IndexPerson::class)
        ->set('form.name', '')
        ->set('form.birthday', '')
        ->set('form.residence', '')
        ->call('store')
        ->assertHasErrors(['form.name', 'form.birthday', 'form.residence']);
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
        ->set('form.person_id', $person->id)
        ->set('form.name', $updatedData['name'])
        ->set('form.birthday', $updatedData['birthday'])
        ->set('form.residence', $updatedData['residence'])
        ->call('update')
        ->assertHasNoErrors()
        ->assertEmitted('person-saved');
        
    $this->assertDatabaseHas('persons', $updatedData);
});

test('can delete person', function () {
    // Arrange
    $person = Person::factory()->create();
    
    // Act & Assert
    Livewire::test(IndexPerson::class)
        ->call('delete', $person->id)
        ->assertEmitted('person-deleted');
        
    $this->assertDatabaseMissing('persons', ['id' => $person->id]);
});
