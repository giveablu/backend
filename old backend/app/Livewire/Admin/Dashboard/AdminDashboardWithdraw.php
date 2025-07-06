<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use App\Models\Withdraw;

class AdminDashboardWithdraw extends Component
{
    public function render()
    {
        $withdraws = Withdraw::where('status', 0)->with('user')->latest()->take(5)->get();
        
        return view('livewire.admin.dashboard.admin-dashboard-withdraw', [
            'withdraws' => $withdraws
        ]);
    }

    public function approve($id){
        Withdraw::find($id)->update(['status' => 1]);

        $this->dispatch('session-message');
        return back()->with('success', 'Approved Successfully');
    }

    public function reject($id){
        $withdraw = Withdraw::find($id);

        $withdraw->update(['status' => 2]);

        $post = $withdraw->user->post;

        $post->update([
            'paid' => (int)$post->paid + (int)$withdraw->amount
        ]);

        $this->dispatch('session-message');
        return back()->with('success', 'Rejected Successfully');
    }

    public function handlePayment(){
        
    }
}
