
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-2 text-gray-800">Mis solicitudes de adopción</h1>
    <p class="mb-6 text-gray-500">Aquí puedes ver el estado de tus solicitudes de adopción.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($requests as $item)
            @php
                $status = strtolower((string) ($item['status'] ?? 'pendiente'));
                $isApproved = in_array($status, ['aprobada', 'approved'], true);
                $isRejected = in_array($status, ['rechazada', 'rejected'], true);
                $isCancelled = in_array($status, ['cancelada', 'cancelled', 'canceled'], true);
                $statusLabel = 'Pendiente';
                $statusClasses = 'bg-yellow-100 text-yellow-800';
                $requestId = (string) ($item['id'] ?? $item['_docId'] ?? '');
                $isPending = ! $isApproved && ! $isRejected && ! $isCancelled;
                $reviewerNote = trim((string) ($item['reviewerNote'] ?? ''));
                $reviewerNoteAtLabel = '';

                if (!empty($item['reviewerNoteAt'])) {
                    try {
                        $reviewerNoteAtLabel = \Carbon\Carbon::parse((string) $item['reviewerNoteAt'])->format('d/m/Y H:i');
                    } catch (\Throwable $e) {
                        $reviewerNoteAtLabel = (string) $item['reviewerNoteAt'];
                    }
                }

                if ($isApproved) {
                    $statusLabel = 'Aprobada';
                    $statusClasses = 'bg-green-100 text-green-800';
                } elseif ($isRejected) {
                    $statusLabel = 'Rechazada';
                    $statusClasses = 'bg-red-100 text-red-800';
                } elseif ($isCancelled) {
                    $statusLabel = 'Cancelada';
                    $statusClasses = 'bg-slate-200 text-slate-700';
                }
            @endphp

            <div class="p-4 border rounded bg-white">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="font-semibold">
                        Mascota: {{ $item['petName'] ?? 'Sin nombre' }}
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>

                        @if($requestId !== '' && $isPending)
                            <form
                                method="POST"
                                action="{{ route('my.requests.cancel', ['requestId' => $requestId]) }}"
                                class="cancel-my-request-form"
                                data-pet-name="{{ e((string) ($item['petName'] ?? 'Sin nombre')) }}"
                            >
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-full border border-red-300 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                >
                                    Cancelar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($reviewerNote !== '')
                    <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-900">
                        <p class="font-semibold">Nota de la veterinaria/refugio</p>
                        <p class="mt-1 whitespace-pre-line">{{ $reviewerNote }}</p>
                        @if($reviewerNoteAtLabel !== '')
                            <p class="mt-1 text-xs text-blue-700">Actualizada: {{ $reviewerNoteAtLabel }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="p-4 border rounded bg-white text-gray-600">
                Aun no has realizado solicitudes de adopcion.
            </div>
        @endforelse
    </div>

    <div id="cancelRequestConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/70 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900">Confirmar cancelacion</h3>
            <p id="cancelRequestConfirmMessage" class="mt-2 text-sm text-gray-600">
                Vas a cancelar esta solicitud de adopcion.
            </p>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    id="cancelRequestConfirmClose"
                    class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Mantener
                </button>
                <button
                    type="button"
                    id="cancelRequestConfirmAccept"
                    class="rounded-full border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                >
                    Si, cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('cancelRequestConfirmModal');
            const message = document.getElementById('cancelRequestConfirmMessage');
            const closeBtn = document.getElementById('cancelRequestConfirmClose');
            const acceptBtn = document.getElementById('cancelRequestConfirmAccept');
            const forms = Array.from(document.querySelectorAll('.cancel-my-request-form'));
            let pendingForm = null;

            if (!modal || !acceptBtn || !closeBtn || forms.length === 0) {
                return;
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                pendingForm = null;
            }

            function openModal(form) {
                pendingForm = form;
                if (message) {
                    const petName = String(form.dataset.petName || 'esta mascota').trim();
                    message.textContent = `Vas a cancelar la solicitud para ${petName}. Esta accion no se puede deshacer.`;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            forms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    openModal(form);
                });
            });

            closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            acceptBtn.addEventListener('click', () => {
                if (!pendingForm) {
                    return;
                }

                const formToSubmit = pendingForm;
                closeModal();
                formToSubmit.submit();
            });
        })();
    </script>
@endsection
