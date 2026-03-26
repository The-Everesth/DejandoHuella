@extends('layouts.app')

@section('content')
    <div class="space-y-1 py-1">
        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-teal-700 via-teal-600 to-emerald-500 text-white shadow-xl shadow-teal-900/10">
            <div class="grid gap-8 px-6 py-8 sm:px-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)] lg:items-end">
                <div class="space-y-4">
                    <div class="space-y-3">
                        <h1 class="text-3xl font-semibold sm:text-4xl">Perfil</h1>
                        <p class="max-w-2xl text-sm leading-6 text-white/85 sm:text-base">
                            Aqui puedes visualizar tu informacion y hacer el cambio de contraseña, ademas de eliminar tu cuenta si lo deseas.
                        </p>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                    <div class="flex items-center gap-4">
                        
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-black/15 text-2xl font-semibold text-white ring-4 ring-white/15">
                                {{ $user->profile_initials }}
                            </div>

                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Cuenta activa</p>
                            <p class="mt-2 truncate text-xl font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-sm text-white/75">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6">
            <div class="space-y-6">
                <!--
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>
                -->
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="rounded-[1.75rem] border border-rose-100 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
