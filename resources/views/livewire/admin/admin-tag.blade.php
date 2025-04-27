<div>
    <div class="container-fluid p-0">

        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>Post</strong> Tags</h3>
            </div>

            <div class="mt-n1 col-auto ms-auto text-end">
                <button class="btn btn-primary" id="tagAddBtn" type="button" wire:click='resetInput' wire:ignore.self>Add New Tag</button>
            </div>
        </div>

        <div class="card" id="addTagCard" style="display: none" wire:ignore.self>
            <div class="card-header pb-0">
                <h5 class="card-title">New Tag</h5>
            </div>
            <div class="card-body">
                <form wire:submit='createTag'>
                    <div class="row">
                        <div class="col-9 mb-3">
                            <label class="form-label" for="name">Tag Name</label>
                            <input class="form-control" id="name" type="text" wire:model="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-3 mb-3 mt-4">
                            <button class="btn btn-info" type="submit">Add Tag</button>
                            <button class="btn btn-secondary" id="btnCloseAdd" style="display: none" type="button" wire:ignore.self>Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" id="editTagCard" style="display: none" wire:ignore.self>
            <div class="card-header pb-0">
                <h5 class="card-title">Edit Tag</h5>
            </div>
            <div class="card-body">
                <form wire:submit='updateTag'>
                    <input type="hidden" wire:model='tagId'>

                    <div class="row">
                        <div class="col-9 mb-3">
                            <label class="form-label" for="name">Tag Name</label>
                            <input class="form-control" id="name" type="text" wire:model="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-3 mb-3 mt-4">
                            <button class="btn btn-info" type="submit">Update Tag</button>
                            <button class="btn btn-secondary" id="btnCloseEdit" style="display: none" type="button" wire:ignore.self>Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <h5 class="card-title">All Tags</h5>
                <div id='sessionMessage'>
                    @if (session('success'))
                        <h6 class="card-subtitle text-success fw-bolder">{{ session('success') }}</h6>
                    @endif
                    @if (session('error'))
                        <h6 class="card-subtitle text-danger fw-bolder">{{ session('error') }}</h6>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Title</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr wire:key='{{ $tag->id }}'>

                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $tag->name }}</td>

                                <td class="table-action text-center">
                                    <a class="text-info mx-2" onclick="editTagBtn()" wire:click.prevent="editTag({{ $tag->id }})">
                                        <x-feathericon-edit />
                                    </a>
                                    <a class="text-danger mx-2" wire:confirm='Are You Sure?' wire:click.prevent="deleteTag({{ $tag->id }})">
                                        <x-feathericon-trash-2 />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>

@push('admin-script')
    <script>
        let addTagCard = document.querySelector('#addTagCard');
        let editTagCard = document.querySelector('#editTagCard');
        let tagAddBtn = document.querySelector('#tagAddBtn');
        let btnCloseAdd = document.querySelector('#btnCloseAdd');
        let btnCloseEdit = document.querySelector('#btnCloseEdit');

        // add card show
        tagAddBtn.addEventListener('click', (e) => {
            addTagCard.style.display = 'block';
            editTagCard.style.display = 'none';
            btnCloseAdd.style.display = 'inline-block';
            tagAddBtn.style.display = 'none';
        });

        // edit card show
        function editTagBtn() {
            editTagCard.style.display = 'block';
            addTagCard.style.display = 'none';
            btnCloseEdit.style.display = 'inline-block';
            tagAddBtn.style.display = 'none';
        }

        // close forms
        btnCloseAdd.addEventListener('click', (e) => {
            editTagCard.style.display = 'none';
            addTagCard.style.display = 'none';
            btnCloseAdd.style.display = 'none';
            tagAddBtn.style.display = 'inline-block';
        });

        btnCloseEdit.addEventListener('click', (e) => {
            editTagCard.style.display = 'none';
            addTagCard.style.display = 'none';
            btnCloseEdit.style.display = 'none';
            tagAddBtn.style.display = 'inline-block';
        });

        // session message hide
        let sessionMessage = document.querySelector('#sessionMessage');
        document.addEventListener('session-message', (e) => {
            setTimeout(() => {
                sessionMessage.style.display = 'none';
            }, 2000);
        });
    </script>
@endpush
