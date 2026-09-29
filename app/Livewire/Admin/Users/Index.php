<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $users = User::with('companies')->paginate(10);

        return view('livewire.admin.users.index', ['users' => $users]);
    }
}