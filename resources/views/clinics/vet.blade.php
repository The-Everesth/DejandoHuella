@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Alert messages -->
            <div id="alert" class="mb-4 hidden rounded-lg p-4 text-sm font-medium"></div>

            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-bold mb-6">Mi Clínica Veterinaria</h2>
                <form id="clinicForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la clínica *</label>
                        <input type="text" id="name" name="name" placeholder="Nombre de la clínica" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" id="address" name="address" placeholder="Dirección completa" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="phone" name="phone" placeholder="Teléfono de contacto" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Servicios ofrecidos</label>
                        <div id="servicesList" class="border border-gray-300 rounded p-3 bg-gray-50 max-h-40 overflow-y-auto"></div>
                        <p class="text-xs text-gray-500 mt-2">Selecciona los servicios que ofrece tu clínica</p>
                    </div>

                    <div class="flex items-center gap-3 border-t pt-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="published" name="published" class="w-4 h-4">
                            <span class="text-sm font-medium text-gray-700">Publicar mi clínica</span>
                        </label>
                    </div>

                    <div class="flex gap-3 border-t pt-4">
                        <button type="submit" id="saveBtn" class="flex-1 bg-teal-700 text-white px-4 py-2 rounded hover:bg-teal-800 transition font-medium">Guardar clínica</button>
                        <button type="button" id="deleteBtn" class="flex-1 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition font-medium">Eliminar clínica</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
let clinicData = @json($clinic ?? null);
let servicesData = @json($services ?? []);
let selectedServices = [];

function showAlert(msg, type = 'success') {
    const alert = document.getElementById('alert');
    alert.textContent = msg;
    alert.className = `mb-4 rounded-lg p-4 text-sm font-medium ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    alert.classList.remove('hidden');
    setTimeout(() => alert.classList.add('hidden'), 5000);
}

async function loadServicesList() {
    const res = await fetch('/api/medical-services');
    const json = await res.json();
    const services = json.data || [];
    const container = document.getElementById('servicesList');
    container.innerHTML = '';
    
    services.forEach(service => {
        const checked = clinicData && clinicData.services && clinicData.services.includes(service.id);
        const label = document.createElement('label');
        label.className = 'flex items-center gap-2 cursor-pointer p-2 hover:bg-gray-100 rounded';
        label.innerHTML = `<input type="checkbox" value="${service.id}" ${checked ? 'checked' : ''} class="w-4 h-4 service-check"> <span>${service.name}</span>`;
        container.appendChild(label);
    });
}

function prefillForm() {
    if (clinicData) {
        document.getElementById('name').value = clinicData.name || '';
        document.getElementById('address').value = clinicData.address || '';
        document.getElementById('phone').value = clinicData.phone || '';
        document.getElementById('published').checked = clinicData.published || false;
        
        // Prefill selected services
        if (clinicData.services && Array.isArray(clinicData.services)) {
            document.querySelectorAll('.service-check').forEach(chk => {
                if (clinicData.services.includes(chk.value)) {
                    chk.checked = true;
                }
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', async function() {
    await loadServicesList();
    prefillForm();
    
    // Track selected services
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('service-check')) {
            selectedServices = Array.from(document.querySelectorAll('.service-check:checked')).map(chk => chk.value);
        }
    });
});

document.getElementById('clinicForm').addEventListener('submit', async function(e){
    e.preventDefault();
    
    selectedServices = Array.from(document.querySelectorAll('.service-check:checked')).map(chk => chk.value);
    
    const payload = {
        name: document.getElementById('name').value.trim(),
        address: document.getElementById('address').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        services: selectedServices,
        published: document.getElementById('published').checked
    };
    
    if (!payload.name) {
        showAlert('El nombre de la clínica es requerido', 'error');
        return;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    try {
        const res = await fetch('/veterinario/clinica', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': token},
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success || res.ok) {
            showAlert('Clínica guardada exitosamente');
            clinicData = json.data || payload;
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('Error: ' + (json.message || 'No se pudo guardar'), 'error');
        }
    } catch(err) {
        console.error(err);
        showAlert('Error: ' + err.message, 'error');
    }
});

document.getElementById('deleteBtn').addEventListener('click', async function(){
    if (!clinicData) {
        showAlert('No hay clínica registrada para eliminar', 'error');
        return;
    }
    if (!confirm('¿Estás seguro de que quieres eliminar tu clínica? Esta acción no se puede deshacer.')) return;
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    try {
        const res = await fetch('/veterinario/clinica', {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': token}
        });
        const json = await res.json();
        if (json.success || res.ok) {
            showAlert('Clínica eliminada');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('Error: ' + (json.message || 'No se pudo eliminar'), 'error');
        }
    } catch(err) {
        console.error(err);
        showAlert('Error: ' + err.message, 'error');
    }
});
</script>
