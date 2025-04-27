<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class AdminAddUser extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $role = 'donor';
    public $photo;
    public $gender = 'male';

    public function render()
    {
        return view('livewire.admin.admin-addUser'); // Adjust path if necessary
    }

    public function save()
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'role' => ['required', Rule::in(['donor', 'receiver', 'admin'])],
            'photo' => 'nullable|image|max:2048', // 2MB max, adjust as needed
            'gender' => ['required', Rule::in(['male', 'female'])],
        ]);

        // Handle file upload (if photo is provided)
        if ($this->photo) {
            $validatedData['photo'] = $this->photo->store('photos', 'public'); // Store photo in storage/photos directory
        }
        

        User::create($validatedData);

        session()->flash('message', 'User added successfully.');

        $this->reset(); // Reset input fields after saving
    }
}
