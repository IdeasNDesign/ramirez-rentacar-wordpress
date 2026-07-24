let rrcChatInitialized = false;

function toggleRrcChat() {
	const widget = jQuery('#rrc-ai-chat-root');
	widget.toggleClass('collapsed');
	
	if (!widget.hasClass('collapsed') && !rrcChatInitialized) {
		initRrcChatSession();
	}
}

function initRrcChatSession() {
	const container = jQuery('#rrc-ai-chat-messages-container');
	container.html('<div class="rrc-ai-chat-loading" style="padding: 10px; font-size:12px; color:#64748b;">Conectando...</div>');

	jQuery.ajax({
		url: RrcAiAssistant.rest_url + '/chat/session',
		method: 'POST',
		beforeSend: function(xhr) {
			xhr.setRequestHeader('X-WP-Nonce', RrcAiAssistant.nonce);
		},
		data: {
			lang: RrcAiAssistant.current_lang
		},
		success: function(res) {
			rrcChatInitialized = true;
			container.html('');
			
			if (res.success && res.greeting) {
				appendRrcChatMessage('assistant', res.greeting);
			}
			renderQuickButtons(res.state);
		},
		error: function() {
			container.html('<div style="color: #ef4444; font-size: 13px; padding: 10px;">Error al conectar con Sara. Intenta de nuevo.</div>');
		}
	});
}

function sendRrcChatMessage(event) {
	if (event) event.preventDefault();
	
	const input = jQuery('#rrc-ai-chat-input');
	const message = input.val().trim();
	if (!message) return;

	input.val('');
	appendRrcChatMessage('user', message);
	
	const container = jQuery('#rrc-ai-chat-messages-container');
	const loadingId = 'loading-' + Date.now();
	container.append(`<div class="rrc-ai-message assistant" id="${loadingId}">Sara está escribiendo...</div>`);
	container.scrollTop(container[0].scrollHeight);

	jQuery.ajax({
		url: RrcAiAssistant.rest_url + '/chat/message',
		method: 'POST',
		beforeSend: function(xhr) {
			xhr.setRequestHeader('X-WP-Nonce', RrcAiAssistant.nonce);
		},
		data: { 
			message: message,
			lang: document.cookie.match('(^|;)\\s*BTMAT_LANGUAGE\\s*=\\s*([^;]+)')?.pop() || 'es'
		},
		success: function(res) {
			jQuery('#' + loadingId).remove();
			
			if (res.success) {
				appendRrcChatMessage('assistant', res.reply);
				
				if (res.vehicles && res.vehicles.length > 0) {
					appendVehicleRecommendations(res.vehicles);
				}
				
				renderQuickButtons(res.state);
			} else {
				appendRrcChatMessage('assistant', 'Lo siento, he tenido un inconveniente técnico. ¿Podrías intentar de nuevo?');
			}
		},
		error: function() {
			jQuery('#' + loadingId).remove();
			appendRrcChatMessage('assistant', 'Ha ocurrido un error en la conexión. Asegúrate de estar conectado a internet.');
		}
	});
}

function appendRrcChatMessage(role, content) {
	const container = jQuery('#rrc-ai-chat-messages-container');
	const bubble = jQuery('<div></div>')
		.addClass('rrc-ai-message')
		.addClass(role)
		.html(content.replace(/\n/g, '<br>'));
		
	container.append(bubble);
	container.scrollTop(container[0].scrollHeight);
}

function appendVehicleRecommendations(vehicles) {
	const container = jQuery('#rrc-ai-chat-messages-container');
	const cardsContainer = jQuery('<div></div>').addClass('rrc-ai-vehicle-cards');
	
	vehicles.slice(0, 3).forEach(v => {
		const formattedPrice = v.rate.total_amount ? `$${v.rate.total_amount} USD` : 'Consultar';
		
		const card = jQuery(`
			<div class="rrc-ai-vehicle-card">
				<img src="${v.image_url}" class="rrc-ai-vehicle-img" alt="${v.name}" />
				<div class="rrc-ai-vehicle-info">
					<h5 class="rrc-ai-vehicle-title">${v.name}</h5>
					<div class="rrc-ai-vehicle-price">Precio Total: ${formattedPrice}</div>
					<div style="font-size: 11px; color: #64748b; margin-bottom: 8px;">
						Capacidad: ${v.passengers} Pasajeros | Maletas: ${v.luggage}<br>
						Transmisión: ${v.transmission} | Motivo: Recomendado para Roatán
					</div>
					<button class="rrc-ai-vehicle-reserve-btn" onclick="selectRrcVehicle(${v.id})">Seleccionar y Continuar</button>
				</div>
			</div>
		`);
		cardsContainer.append(card);
	});
	
	container.append(cardsContainer);
	container.scrollTop(container[0].scrollHeight);
}

function selectRrcVehicle(vehicleId) {
	appendRrcChatMessage('user', `Selecciono la opción para el vehículo ID: ${vehicleId}`);
	sendRrcChatMessage();
}

function renderQuickButtons(state) {
	const container = jQuery('#rrc-ai-chat-quick-buttons-container');
	container.html('');
	
	let buttons = [];
	if (state.stage === 'welcome') {
		buttons = [
			{ text: 'Reservar un vehículo', msg: 'Quiero reservar un carro' },
			{ text: 'Ver flota disponible', msg: 'Ver vehículos disponibles' },
			{ text: 'Hablar con humano', msg: 'Quiero hablar con un agente' }
		];
	} else if (state.stage === 'vehicle_selection') {
		buttons = [
			{ text: 'Ver más económicos', msg: 'Muéstrame opciones más económicas' },
			{ text: 'Modificar fechas', msg: 'Quiero cambiar las fechas de mi viaje' }
		];
	} else {
		buttons = [
			{ text: 'Preguntas Frecuentes', msg: 'Preguntas Frecuentes' }
		];
	}
	
	buttons.forEach(b => {
		const btn = jQuery('<button></button>')
			.addClass('rrc-ai-quick-btn')
			.text(b.text)
			.on('click', function() {
				jQuery('#rrc-ai-chat-input').val(b.msg);
				sendRrcChatMessage();
			});
		container.append(btn);
	});
}

// Tab title change on visibility change
let originalTitle = document.title;

document.addEventListener('visibilitychange', function() {
	if (document.visibilityState === 'hidden') {
		originalTitle = document.title;
		document.title = '¡VUELVE PRONTO! 👋';
	} else if (document.visibilityState === 'visible') {
		document.title = originalTitle;
	}
});
