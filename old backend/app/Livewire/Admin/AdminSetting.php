<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class AdminSetting extends Component
{
    public $app_version, $default_amount, $app_feature = [], $single_feature, $feature_key;

    public function mount()
    {
        $setting = Setting::first();
        $this->app_version = $setting->app_version;
        $this->default_amount = $setting->default_amount;
        $this->app_feature = explode('|', $setting->app_feature);
    }

    public function updateSetting()
    {
        $this->validate([
            'app_version' => ['required'],
            'default_amount' => ['required']
        ]);

        $setting = Setting::first();
        $setting->update([
            'default_amount' => $this->default_amount,
            'app_version' => $this->app_version,
        ]);

        $this->dispatch('session-message');
        return back()->with('success', 'Setting Updated');
    }

    public function resetInput(){
        $this->single_feature = '';
    }

    public function storeFeature()
    {
        $this->validate([
            'single_feature' => ['required']
        ]);

        array_push($this->app_feature, $this->single_feature);

        Setting::first()->update([
            'app_feature' => implode('|', $this->app_feature)
        ]);

        $this->dispatch('close-add-modal');
        $this->dispatch('feature-message');
        return back()->with('success', 'Feature Added');
    }

    public function deleteFeature($key)
    {
        unset($this->app_feature[$key]);

        Setting::first()->update([
            'app_feature' => implode('|', $this->app_feature)
        ]);

        $this->dispatch('feature-deleted');
        $this->dispatch('feature-message');
        return back()->with('success', 'Feature Deleted');
    }

    public function editFeature($key)
    {
        $this->single_feature = $this->app_feature[$key];
        $this->feature_key = $key;
        $this->dispatch('open-edit-modal');
    }

    public function updateFeature()
    {
        $this->validate([
            'single_feature' => ['required']
        ]);

        $this->app_feature[$this->feature_key] = $this->single_feature;
        Setting::first()->update([
            'app_feature' => implode('|', $this->app_feature)
        ]);

        $this->dispatch('close-edit-modal');
        $this->dispatch('feature-message');
        return back()->with('success', 'Feature Updated');
    }

    public function render()
    {
        return view('livewire.admin.admin-setting');
    }
}
