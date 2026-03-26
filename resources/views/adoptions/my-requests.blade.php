
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

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="flex-shrink-0 flex items-center justify-center w-14 h-14 rounded-full border"
                        style="
                            @if($isApproved) background-color: #e6f9f0; border-color: #34d399; @endif
                            @if($isRejected) background-color: #fef2f2; border-color: #f87171; @endif
                            @if($isPending) background-color: #fef9e6; border-color: #fbbf24; @endif
                        "
                    >
                        @if($isApproved)
                            <!-- Palomita verde -->
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                <path d="M8 12.5l3 3 5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                            </svg>
                        @elseif($isRejected)
                            <!-- Tachita roja -->
                            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                <path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                        @elseif($isPending)
                            <!-- Reloj amarillo -->
                            <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                        @else
                            <!-- Icono neutro -->
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-gray-800 truncate">{{ $item['petName'] ?? 'Sin nombre' }}</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 mt-1 truncate">
                            @if(!empty($item['petType']))
                                <span class="capitalize">{{ $item['petType'] }}</span>
                            @endif
                            @if(!empty($item['petSex']))
                                <span>• {{ ucfirst($item['petSex']) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 md:items-end md:text-right">
                    @if($reviewerNote !== '')
                        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-2 text-sm text-blue-900 max-w-md">
                            <div class="font-semibold mb-1 flex items-center gap-1">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M8 16h8M8 8h8" />
                                </svg>
                                Nota de la veterinaria/refugio
                            </div>
                            <div class="whitespace-pre-line">{{ $reviewerNote }}</div>
                            @if($reviewerNoteAtLabel !== '')
                                <div class="mt-1 text-xs text-blue-700">Actualizada: {{ $reviewerNoteAtLabel }}</div>
                            @endif
                        </div>
                    @endif
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
                                class="inline-flex items-center rounded-full border border-red-300 bg-red-50 px-4 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 mt-2 md:mt-0"
                            >
                                Cancelar
                            </button>
                        </form>
                    @endif
                </div>
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
