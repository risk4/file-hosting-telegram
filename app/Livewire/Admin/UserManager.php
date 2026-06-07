<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class UserManager extends Component
{
    public bool   $showModal   = false;
    public ?int   $editingId   = null;
    public string $name        = '';
    public string $email       = '';
    public string $password    = '';
    public bool   $isActive    = true;

    public bool   $showDeleteModal = false;
    public ?int   $deletingId      = null;

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $this->editingId ? 'nullable|min:6' : 'required|min:6',
            'isActive' => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'name', 'email', 'password');
        $this->isActive  = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->password  = '';
        $this->isActive  = $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'is_active' => $this->isActive,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            $msg = 'User diperbarui';
        } else {
            $data['password'] = $data['password'] ?? Hash::make($this->password);
            User::create($data);
            $msg = 'User ditambahkan';
        }

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'Tidak bisa hapus akun sendiri');
            return;
        }
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        User::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        $this->dispatch('notify', type: 'success', message: 'User dihapus');
    }

    public function toggleActive(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'Tidak bisa menonaktifkan akun sendiri');
            return;
        }
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        $this->dispatch('notify', type: 'success', message: 'Status diperbarui');
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        return view('livewire.admin.user-manager', compact('users'))
            ->layout('layouts.admin', ['title' => 'Manajemen User']);
    }
}
