<?php

declare(strict_types=1);

namespace App\Modules\Core\Livewire;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class UserProfile extends Component
{
    public ?string $modal = null; // 'name' | 'password'

    // ─── Modal de nome ────────────────────────────────────────────────────────
    public string $newName = '';

    // ─── Modal de senha ───────────────────────────────────────────────────────
    public string $currentPassword        = '';
    public string $newPassword            = '';
    public string $newPasswordConfirmation = '';

    #[On('open-profile-modal')]
    public function openModal(string $type): void
    {
        $this->resetValidation();
        $this->newName                 = auth()->user()->name;
        $this->currentPassword         = '';
        $this->newPassword             = '';
        $this->newPasswordConfirmation = '';
        $this->modal = $type;
    }

    public function closeModal(): void
    {
        $this->modal                   = null;
        $this->currentPassword         = '';
        $this->newPassword             = '';
        $this->newPasswordConfirmation = '';
        $this->resetValidation();
    }

    // ─── Salvar nome ─────────────────────────────────────────────────────────

    public function saveName(): void
    {
        $this->validate([
            'newName' => 'required|string|min:2|max:100',
        ]);

        auth()->user()->update(['name' => $this->newName]);

        $this->closeModal();
        session()->flash('success', 'Nome atualizado com sucesso!');
    }

    // ─── Salvar senha ─────────────────────────────────────────────────────────

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword'         => 'required',
            'newPassword'             => 'required|min:8',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ]);

        if (! Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'A senha atual está incorreta.');
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->newPassword)]);

        $this->closeModal();
        session()->flash('success', 'Senha alterada com sucesso!');
    }

    public function render()
    {
        return view('livewire.profile.user-profile');
    }
}
