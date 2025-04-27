<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Withdraw;
use Livewire\WithPagination;

class AdminWithdraw extends Component
{
    use WithPagination;

    public function render()
    {
        $withdraws = Withdraw::with('user')->latest()->paginate(10);
        return view('livewire.admin.admin-withdraw', [
            'withdraws' => $withdraws
        ]);
    }

    public function approve($id){
        Withdraw::find($id)->update(['status' => 1]);

        $this->dispatch('session-message');
        return back()->with('success', 'Status Updated');
    }

    public function reject($id){
        $withdraw = Withdraw::with('user')->find($id);

        $withdraw->update(['status' => 2]);

        $post = $withdraw->user->post;

        $post->update([
            'paid' => (int)$post->paid + (int)$withdraw->amount
        ]);

        $this->dispatch('session-message');
        return back()->with('success', 'Rejected Successfully');
    }
}
