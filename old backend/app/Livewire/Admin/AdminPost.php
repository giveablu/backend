<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPost extends Component
{
    use WithPagination;

    public function render()
    {
        $posts = Post::with('user')->paginate(10);

        return view('livewire.admin.admin-post', [
            'posts' => $posts
        ]);
    }
}
