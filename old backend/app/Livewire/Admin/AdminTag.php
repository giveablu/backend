<?php

namespace App\Livewire\Admin;

use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class AdminTag extends Component
{
    use WithPagination;

    public $tagId, $name;

    public function resetInput(){
        $this->name = '';
        $this->resetErrorBag();
    }

    public function createTag()
    {
        $this->validate([
            'name' => ['required', 'max:50', 'unique:tags,name']
        ]);

        Tag::create([
            'name' => $this->name
        ]);

        $this->reset();
        $this->dispatch('session-message');
        return back()->with('success', 'Tag Created');
    }

    public function editTag($id)
    {
        $tag = Tag::find($id);

        $this->tagId = $tag->id;
        $this->name = $tag->name;

        $this->resetErrorBag();
    }

    public function updateTag()
    {
        $this->validate([
            'tagId' => ['required'],
            'name' => ['required', 'max:50']
        ]);

        $tag = Tag::find($this->tagId);

        $tag->update([
            'name' => $this->name
        ]);

        $this->dispatch('session-message');
        return back()->with('success', 'Tag Updated');
    }

    public function deleteTag($id)
    {
        $tag = Tag::find($id);
        $tag->delete();

        $this->dispatch('session-message');
        return back()->with('success', 'Tag Deleted');
    }

    public function render()
    {
        $tags = Tag::paginate(10);
        return view('livewire.admin.admin-tag', [
            'tags' => $tags
        ]);
    }
}
