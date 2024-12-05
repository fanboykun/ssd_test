<?php

namespace App\Livewire\Person;

use App\Livewire\Forms\PersonForm;
use App\Models\Person;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPerson extends Component
{

    use WithPagination;
    public PersonForm $personForm;
    public $search = '';
    public $per_page = 10;

    public function render()
    {
        return view('livewire.person.index-person', [
            "persons" => Person::where('name', 'like', '%' . $this->search . '%')
                ->orderBy('id', 'desc')
                ->paginate($this->per_page)
        ])->layout('layouts.app');
    }

    public function handleAdd()
    {
        $this->personForm->store();
        $this->dispatch('close-modal', 'add-person-modal');
    }

    public function handleEdit()
    {
        $this->personForm->update();
        $this->dispatch('close-modal', 'edit-person-modal');
    }

    public function handleDelete()
    {
        $this->personForm->delete();
        $this->dispatch('close-modal', 'confirm-person-deletion');
    }
}
