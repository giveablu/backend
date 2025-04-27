<div>
    <div class="container-fluid p-0">
        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>App</strong> FAQs</h3>
            </div>

            <div class="mt-n1 col-auto ms-auto text-end">
                <button class="btn btn-primary" type="button" onclick="addFaqBtn()">Add New FAQ</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
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
                            <th>Questions</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr wire:key='{{ $faq->id }}'>

                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $faq->question }}</td>

                                <td class="table-action text-center">
                                    <a class="text-info mx-2" wire:click.prevent="editFaq({{ $faq->id }})">
                                        <x-feathericon-edit />
                                    </a>
                                    <a class="text-danger mx-2" wire:confirm='Are You Sure?' wire:click.prevent="deleteFaq({{ $faq->id }})">
                                        <x-feathericon-trash-2 />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $faqs->links() }}
            </div>

            <div class="modal fade" id="addFaqModal" role="dialog" aria-hidden="true" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New FAQ</h5>
                            <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close" wire:click='resetInput'></button>
                        </div>
                        <div class="modal-body m-3">
                            <form wire:submit="storeFaq">
                                <div class="mb-3">
                                    <label class="form-label">Question</label>
                                    <textarea class="form-control" wire:model="question" rows="3"></textarea>
                                    @error('question')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Answer</label>
                                    <textarea class="form-control" wire:model="answer" rows="5"></textarea>
                                    @error('answer')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <button class="btn btn-primary" type="submit">Add FAQ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editFaqModal" role="dialog" aria-hidden="true" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit FAQ</h5>
                            <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close" wire:click='resetInput'></button>
                        </div>
                        <div class="modal-body m-3">
                            <form wire:submit="updateFaq">
                                <input type="hidden" wire:model='faqId'>

                                <div class="mb-3">
                                    <label class="form-label">Question</label>
                                    <textarea class="form-control" wire:model="question" rows="3"></textarea>
                                    @error('question')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Answer</label>
                                    <textarea class="form-control" wire:model="answer" rows="5"></textarea>
                                    @error('answer')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <button class="btn btn-primary" type="submit">Update FAQ</button>
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
        // edit modal
        let addFaqModal = new bootstrap.Modal(document.querySelector('#addFaqModal'), {});

        // add faq btn
        function addFaqBtn() {
            addFaqModal.show();
        }

        document.addEventListener('faq-created', (e) => {
            addFaqModal.hide();
        });

        // edit modal
        let editFaqModal = new bootstrap.Modal(document.querySelector('#editFaqModal'), {});
        document.addEventListener('edit-faq', (e) => {
            editFaqModal.show();
        });

        document.addEventListener('faq-updated', (e) => {
            editFaqModal.hide();
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
