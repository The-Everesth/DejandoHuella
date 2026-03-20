import './bootstrap';

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
	Alpine.data('profileForm', ({ initialName = '', initialEmail = '', initialPhotoUrl = null } = {}) => ({
		name: initialName,
		email: initialEmail,
		photoPreview: initialPhotoUrl,
		photoName: '',
		photoError: '',
		submitting: false,
		showConfirmation: false,
		touched: {
			name: false,
			email: false,
		},
		objectUrl: null,
		namePattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s"'-]+$/,
		emailPattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
		touch(field) {
			this.touched[field] = true;
		},
		releasePreview() {
			if (this.objectUrl) {
				URL.revokeObjectURL(this.objectUrl);
				this.objectUrl = null;
			}
		},
		previewPhoto(event) {
			const file = event.target.files?.[0];

			if (!file) {
				this.photoName = '';
				this.photoError = '';
				this.releasePreview();
				this.photoPreview = initialPhotoUrl;
				return;
			}

			if (!file.type.startsWith('image/')) {
				this.photoError = 'Selecciona una imagen válida en formato JPG, PNG o WEBP.';
				this.photoName = '';
				event.target.value = '';
				return;
			}

			if (file.size > 2 * 1024 * 1024) {
				this.photoError = 'La imagen de perfil no debe superar los 2 MB.';
				this.photoName = '';
				event.target.value = '';
				return;
			}

			this.releasePreview();
			this.objectUrl = URL.createObjectURL(file);
			this.photoPreview = this.objectUrl;
			this.photoName = file.name;
			this.photoError = '';
		},
		get trimmedName() {
			return this.name.trim();
		},
		get trimmedEmail() {
			return this.email.trim();
		},
		get nameError() {
			if (!this.touched.name && this.trimmedName === initialName.trim()) {
				return '';
			}

			if (this.trimmedName === '') {
				return 'Ingresa tu nombre del perfil.';
			}

			if (!this.namePattern.test(this.trimmedName)) {
				return 'Usa solo letras, espacios, comillas dobles, apóstrofes o guiones.';
			}

			return '';
		},
		get emailError() {
			if (!this.touched.email && this.trimmedEmail.toLowerCase() === initialEmail.trim().toLowerCase()) {
				return '';
			}

			if (this.trimmedEmail === '') {
				return 'Ingresa tu correo electrónico.';
			}

			if (!this.emailPattern.test(this.trimmedEmail)) {
				return 'Ingresa un correo electrónico válido.';
			}

			return '';
		},
		get hasBlockingErrors() {
			return Boolean(this.nameError || this.emailError || this.photoError);
		},
	}));

	Alpine.data('passwordSecurityForm', ({ minLength = 8 } = {}) => ({
		currentPassword: '',
		password: '',
		confirmation: '',
		submitting: false,
		showConfirmation: false,
		touched: {
			password: false,
			confirmation: false,
		},
		touch(field) {
			this.touched[field] = true;
		},
		get rules() {
			return {
				length: this.password.length >= minLength,
				lower: /[a-záéíóúñü]/.test(this.password),
				upper: /[A-ZÁÉÍÓÚÑÜ]/.test(this.password),
				number: /\d/.test(this.password),
				symbol: /[^A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s]/.test(this.password),
			};
		},
		get strengthScore() {
			return Object.values(this.rules).filter(Boolean).length;
		},
		get strengthLevel() {
			if (!this.password) {
				return 0;
			}

			if (this.strengthScore <= 2) {
				return 1;
			}

			if (this.strengthScore <= 4) {
				return 2;
			}

			return 3;
		},
		get strengthLabel() {
			return {
				0: 'Sin evaluar',
				1: 'Débil',
				2: 'Media',
				3: 'Fuerte',
			}[this.strengthLevel];
		},
		get strengthTone() {
			return {
				0: 'slate',
				1: 'rose',
				2: 'amber',
				3: 'emerald',
			}[this.strengthLevel];
		},
		get strengthBadgeClass() {
			return {
				slate: 'bg-slate-100 text-slate-700',
				rose: 'bg-rose-100 text-rose-700',
				amber: 'bg-amber-100 text-amber-700',
				emerald: 'bg-emerald-100 text-emerald-700',
			}[this.strengthTone];
		},
		get feedbackToneClass() {
			return {
				slate: 'text-slate-500',
				rose: 'text-rose-600',
				amber: 'text-amber-600',
				emerald: 'text-emerald-700',
			}[this.strengthTone];
		},
		get passwordError() {
			if (!this.password) {
				return this.touched.password ? 'Ingresa una contraseña nueva.' : '';
			}

			const missing = [];

			if (!this.rules.length) {
				missing.push(`al menos ${minLength} caracteres`);
			}

			if (!this.rules.upper) {
				missing.push('una letra mayúscula');
			}

			if (!this.rules.lower) {
				missing.push('una letra minúscula');
			}

			if (!this.rules.number) {
				missing.push('un número');
			}

			if (!this.rules.symbol) {
				missing.push('un símbolo');
			}

			if (missing.length === 0) {
				return '';
			}

			return `La contraseña nueva debe incluir ${missing.join(', ')}.`;
		},
		get confirmationError() {
			if (!this.confirmation) {
				return this.touched.confirmation ? 'Confirma la nueva contraseña.' : '';
			}

			if (this.confirmation !== this.password) {
				return 'La confirmación no coincide con la contraseña nueva.';
			}

			return '';
		},
		get feedbackMessage() {
			if (!this.password) {
				return 'Usa una combinación de letras, números y símbolos para proteger tu cuenta.';
			}

			return this.passwordError || 'La contraseña cumple con los requisitos de seguridad.';
		},
		strengthSegmentClass(segment) {
			if (this.strengthLevel < segment) {
				return 'bg-slate-200';
			}

			return {
				slate: 'bg-slate-300',
				rose: 'bg-rose-500',
				amber: 'bg-amber-500',
				emerald: 'bg-emerald-500',
			}[this.strengthTone];
		},
		ruleDotClass(condition) {
			return condition ? 'bg-emerald-500' : 'bg-slate-300';
		},
		ruleTextClass(condition) {
			return condition ? 'text-emerald-700' : 'text-slate-500';
		},
	}));
});

window.Alpine = Alpine;

Alpine.start();
