<div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persons') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        isEditModalOpen: false, 
        isAddModalOpen: false, 
        isDeleteModalOpen: false, 
        selectedPerson: null, 
        openModal(modal, data) {
            if(modal === 'confirm-person-deletion') {
                $dispatch('delete-person', data.id);
            }
            if(modal === 'edit-person-modal') {
                $dispatch('edit-person', data);
            }
            this.selectedPerson = data;
            $dispatch('open-modal', modal);
        } 
    }">
        <div class="flex flex-col max-h-fit max-w-7xl shadow sm:rounded-lg py-8 sm:px-8 lg:px-6 mx-auto bg-white">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="border rounded-lg divide-y divide-gray-200">
                        <div class="py-3 px-4 flex justify-between items-center">
                            <div class="relative max-w-xs">
                                <label class="sr-only">Search</label>
                                <input type="text" name="search"
                                    class="py-2 px-3 ps-9 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                    placeholder="Search persons by name" wire:model.live.debounce.50ms="search">
                                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                    <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                </div>
                            </div>
                            <x-primary-button type="button" x-on:click="openModal('add-person-modal')">
                                {{ __('Create Person') }}
                            </x-primary-button>
                        </div>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-2 sm:px-6 sm:py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            Name</th>
                                        <th scope="col"
                                            class="px-4 py-2 sm:px-6 sm:py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            Age</th>
                                        <th scope="col"
                                            class="px-4 py-2 sm:px-6 sm:py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            Address</th>
                                        <th scope="col"
                                            class="px-4 py-2 sm:px-6 sm:py-3 text-end text-xs font-medium text-gray-500 uppercase">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 overflow-auto">
                                    @forelse ($persons as $person)
                                        <tr>
                                            <td
                                                class="px-4 py-2 sm:px-6 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                {{ $person->name }}
                                            </td>
                                            <td class="px-4 py-2 sm:px-6 sm:py-4 text-sm text-gray-800">
                                                {{ Carbon\Carbon::parse($person->birthday)->format('d F Y') }}
                                            </td>
                                            <td class="px-4 py-2 sm:px-6 sm:py-4 text-sm text-gray-800">
                                                {{ $person->residence }}
                                            </td>
                                            <td
                                                class="px-4 py-2 sm:px-6 sm:py-4 whitespace-nowrap text-end text-sm font-medium">
                                                <x-secondary-button type="button" x-data=""
                                                    x-on:click.prevent="openModal('edit-person-modal', {{ $person }})">
                                                    <x-icons.edit-icon />
                                                </x-secondary-button>
                                                <x-danger-button type="button" x-data=""
                                                    x-on:click.prevent="openModal('confirm-person-deletion', {{ $person }})">
                                                    <x-icons.delete-icon />
                                                </x-danger-button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                class="px-4 py-2 sm:px-6 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                                No Data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="py-1 px-4">
                            {{ $persons->links(data: ['search' => $search]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="confirm-person-deletion" x-show="isDeleteModalOpen" focusable>
        <form wire:submit="handleDelete" x-on:delete-person.window="$wire.personForm.person_id = $event.detail"
            class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete this person?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once this person is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete this person.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Person') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="add-person-modal" x-show="isAddModalOpen" focusable>
        <div class="py-6 px-4">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Add Person') }}
            </h2>
            <form wire:submit="handleAdd" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input wire:model="personForm.name" id="name" name="name" type="text"
                        class="mt-1 block w-full" required autofocus autocomplete="name" />
                    @error('personForm.name')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>
                <div>
                    <x-input-label for="birthday" :value="__('Birthday')" />
                    <input type="date" wire:model="personForm.birthday" id="birthday" name="birthday"
                        class="mt-1 block w-full" required autofocus autocomplete="birthday" />
                    @error('personForm.birthday')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>
                <div>
                    <x-input-label for="residence" :value="__('Residence')" />
                    <x-text-input wire:model="personForm.residence" id="residence" name="residence" type="text"
                        class="mt-1 block w-full" required autofocus autocomplete="residence" />
                    @error('personForm.residence')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>


                <div class="flex items-center gap-4">

                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    <x-action-message class="me-3" on="person-created">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="edit-person-modal" x-show="isEditModalOpen" focusable>
        <div class="py-6 px-4"
            x-on:edit-person.window="() => { $wire.personForm.person_id = $event.detail.id; $wire.personForm.name = $event.detail.name; $wire.personForm.birthday = $event.detail.birthday; $wire.personForm.residence = $event.detail.residence }">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Edit Person') }}
            </h2>
            <form wire:submit="handleEdit" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input wire:model="personForm.name" id="name" name="name" type="text"
                        class="mt-1 block w-full" required autofocus autocomplete="name" />
                    @error('personForm.name')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>
                <div>
                    <x-input-label for="birthday" :value="__('Birthday')" />
                    <x-text-input wire:model="personForm.birthday" id="birthday" name="birthday" type="text"
                        class="mt-1 block w-full" required autofocus autocomplete="birthday" />
                    @error('personForm.birthday')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>
                <div>
                    <x-input-label for="residence" :value="__('Residence')" />
                    <x-text-input wire:model="personForm.residence" id="residence" name="residence" type="text"
                        class="mt-1 block w-full" required autofocus autocomplete="name" />
                    @error('personForm.residence')
                        <x-input-error class="mt-2" :messages="$message" />
                    @enderror
                </div>


                <div class="flex items-center gap-4">

                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>
        </div>
    </x-modal>

</div>