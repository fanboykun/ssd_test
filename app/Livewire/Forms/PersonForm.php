<?php

namespace App\Livewire\Forms;

use App\Models\Person;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PersonForm extends Form
{
    public ?int $person_id;

    #[Validate('required|min:5')]
    public $name = '';

    #[Validate("required|date")]
    public $birthday = '';

    #[Validate('required|min:5')]
    public $residence = '';

    public function set(Person $person)
    {
        $this->person = $person;
        $this->name = $person->name;
        $this->bithday = $person->birthday;
        $this->residence = $person->residence;
    }

    public function store()
    {
        $this->validate();
        $new_person = Person::create($this->except('person_id'));
        $this->reset();
        return $new_person;
    }

    public function update()
    {
        if (!$this->person_id) {
            return session()->flash('error', 'Person not found');
        }
        $this->validate();
        Person::find($this->person_id)->update($this->except('person_id'));
        $this->reset();
    }

    public function delete()
    {
        if (!$this->person_id) {
            return session()->flash('error', 'Person not found');
        }
        $this->validate([
            "person_id" => "required|exists:App\Models\Person,id"
        ]);
        Person::find($this->person_id)->delete();
    }
}
