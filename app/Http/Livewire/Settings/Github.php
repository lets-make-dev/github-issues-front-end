<?php

namespace App\Http\Livewire\Settings;

use Livewire\Component;

class Github extends Component
{
    public $githubAccounts = ['bytelaunch', 'bytelaunch-projects', 'irvine-public-schools-foundation', 'menufreedom', 'WPNerds'];
    public $selectedAccount = '';
    public $repositories = [ 'cat'];

    public function connect()
    {
        $this->repositories = ['repo1', 'repo2', 'repo3'];
    }

    public function render()
    {
        return view('livewire.settings.github');
    }
}