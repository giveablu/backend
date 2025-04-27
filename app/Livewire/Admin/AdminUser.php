<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class AdminUser extends Component
{
    use WithPagination;


    public function addUser()
    {
        return redirect()->route('admin.users.create');
    }

    public function render()
    {
        $users = User::where('role', '!=', 'admin')->paginate(10);
        return view('livewire.admin.admin-user', [
            'users' => $users
        ]);
    }
}
