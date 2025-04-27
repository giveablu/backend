<div>
    <div class="container-fluid p-0">

        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>App</strong> Setting</h3>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
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
                <form wire:submit="updateSetting">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="default_amount">Default Amount</label>
                            <input class="form-control" id="default_amount" type="text" wire:model='default_amount'>
                            @error('default_amount')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="app_version">App Version</label>
                            <input class="form-control" id="app_version" type="text" wire:model="app_version">
                            @error('app_version')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Update Setting</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <div class="row mb-xl-3 mb-2">
                    <div class="d-none d-sm-block col-auto">
                        <h4 class="text-info"><strong>Apps</strong> Features</h4>
                    </div>
        
                    <div class="mt-n1 col-auto ms-auto text-end">
                        <button class="btn btn-primary" type="button" wire:click='resetInput' onclick="addFeature()">New Feature</button>
                    </div>
                </div>
                <div id='featureMessage'>
                    @if (session('success'))
                        <h6 class="card-subtitle text-success fw-bolder">{{ session('success') }}</h6>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Feature Description</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($app_feature as $key => $feature)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $feature }}</td>

                                <td class="table-action text-center">
                                    <a class="text-info mx-2" wire:click.prevent="editFeature({{ $key }})">
                                        <x-feathericon-edit />
                                    </a>
                                    <a class="text-danger mx-2" wire:confirm='Are You Sure?' wire:click.prevent="deleteFeature({{ $key }})">
                                        <x-feathericon-trash-2 />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="addFeatureModal" role="dialog" aria-hidden="true" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New Feature</h5>
                            <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close" wire:click='resetInput'></button>
                        </div>
                        <div class="modal-body m-3">
                            <form wire:submit="storeFeature">
                                <div class="mb-3">
                                    <label class="form-label">Feature Description</label>
                                    <textarea class="form-control" wire:model="single_feature" rows="3"></textarea>
                                    @error('single_feature')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <button class="btn btn-primary" type="submit">Create Feature</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editFeatureModal" role="dialog" aria-hidden="true" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Feature</h5>
                            <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close" wire:click='resetInput'></button>
                        </div>
                        <div class="modal-body m-3">
                            <form wire:submit="updateFeature">
                                <div class="mb-3">
                                    <label class="form-label">Feature Description</label>
                                    <textarea class="form-control" wire:model="single_feature" rows="3"></textarea>
                                    @error('single_feature')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <button class="btn btn-primary" type="submit">Update Feature</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('admin-script')
    <script>
        let sessionMessage = document.querySelector('#sessionMessage');
        document.addEventListener('session-message', () => {
            setTimeout(() => {
                sessionMessage.style.display = 'none';
            }, 2000);
        });

        let addModal = new bootstrap.Modal(document.querySelector('#addFeatureModal'), {});
        let editModal = new bootstrap.Modal(document.querySelector('#editFeatureModal'), {});

        function addFeature(){
            addModal.show();
        }

        document.addEventListener('close-add-modal', ()=>{
            addModal.hide();
        });

        document.addEventListener('open-edit-modal', ()=>{
            editModal.show();
        });

        document.addEventListener('close-edit-modal', ()=>{
            editModal.hide();
        });

        let featureMessage = document.querySelector('#featureMessage');
        document.addEventListener('feature-message', () => {
            setTimeout(() => {
                featureMessage.style.display = 'none';
            }, 2000);
        });
    </script>
@endpush
