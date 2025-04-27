<?php

namespace App\Livewire\Admin;

use App\Models\AppFaq;
use Livewire\Component;
use Livewire\WithPagination;

class AdminFaq extends Component
{
    use WithPagination;

    public $faqId, $question, $answer;

    public function deleteFaq($id)
    {
        $faq = AppFaq::find($id);
        $faq->delete();

        $this->dispatch('session-message');
        return back()->with('success', 'FAQ Deleted');
    }

    public function resetInput(){
        $this->question = '';
        $this->answer = '';
    }

    public function storeFaq(){
        $this->validate([
            'question' => ['required'],
            'answer' => ['required']
        ]);

        AppFaq::create([
            'question' => $this->question,
            'answer' => $this->answer,
        ]);

        $this->reset();

        $this->dispatch('faq-created');
        $this->dispatch('session-message');
        return back()->with('success', 'FAQ Created');
    }

    public function editFaq($id)
    {
        $faq = AppFaq::find($id);

        $this->faqId = $faq->id;
        $this->question = $faq->question;
        $this->answer = $faq->answer;

        $this->dispatch('edit-faq');
    }

    public function updateFaq()
    {
        $this->validate([
            'faqId' => ['required'],
            'question' => ['required'],
            'answer' => ['required']
        ]);

        $faq = AppFaq::find($this->faqId);
        $faq->update([
            'question' => $this->question,
            'answer' => $this->answer,
        ]);

        $this->reset();

        $this->dispatch('faq-updated');
        $this->dispatch('session-message');
        return back()->with('success', 'FAQ Updated');
    }

    public function render()
    {
        $faqs = AppFaq::paginate(10);

        return view('livewire.admin.admin-faq', [
            'faqs' => $faqs
        ]);
    }
}
