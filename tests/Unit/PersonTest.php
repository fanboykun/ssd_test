<?php

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create person', function () {
    // Arrange
    $personData = [
        'name' => 'John Doe',
        'birthday' => '1990-01-01',
        'residence' => 'New York'
    ];

    // Act
    $person = Person::create($personData);

    // Assert
    expect($person)
        ->toBeInstanceOf(Person::class)
        ->name->toBe($personData['name'])
        ->birthday->toBe($personData['birthday'])
        ->residence->toBe($personData['residence']);
});

test('person has correct date format', function () {
    // Arrange
    $person = Person::factory()->create([
        'birthday' => '1990-01-01'
    ]);

    // Assert
    expect(Carbon\Carbon::parse($person->birthday)->format('Y-m-d'))
        ->toBe('1990-01-01');
});

test('can update person attributes', function () {
    // Arrange
    $person = Person::factory()->create();
    $newName = 'Jane Doe';

    // Act
    $person->update(['name' => $newName]);

    // Assert
    expect($person->fresh())
        ->name->toBe($newName);
});

test('can delete person', function () {
    // Arrange
    $person = Person::factory()->create();

    // Act
    $person->delete();

    // Assert
    expect(Person::find($person->id))->toBeNull();
});

test('person factory creates valid data', function () {
    // Act
    $person = Person::factory()->create();

    // Assert
    expect($person)
        ->name->not->toBeEmpty()
        ->birthday->not->toBeEmpty()
        ->residence->not->toBeEmpty();
});
