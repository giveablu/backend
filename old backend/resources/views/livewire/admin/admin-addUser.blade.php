<!-- resources/views/livewire/register-user.blade.php -->

<div>
    @if (session()->has('message'))
        <div id="userMessage">
            <h6 class="card-subtitle text-success fw-bolder">{{ session('message') }}</h6>
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" id="name" wire:model.defer="name">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="email" wire:model.defer="email">
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone:</label>
            <input type="text" class="form-control" id="phone" wire:model.defer="phone">
            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <select class="form-select" id="role" wire:model.defer="role">
                <option value="" selected>--Select Role--</option>
                <option value="donor">Donor</option>
                <option value="receiver">Receiver</option>
                <option value="admin">Admin</option>
            </select>
            @error('role') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Photo:</label>
            <input type="file" class="form-control" id="photo" wire:model.defer="photo">
            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Gender:</label>
            <select class="form-select" id="gender" wire:model.defer="gender">
                <option value="" selected>--Select Gender--</option>

                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Add User</button>
    </form>
</div>
