@extends('layouts.app')

@section('title', 'Agenda | Clinica Facil')

@section('content')
<div x-data="agendaApp()" x-init="initCalendar()">

  {{-- HEADER --}}
  <div class="bg-white rounded-xl shadow p-4 mb-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      <h1 class="text-xl font-semibold text-gray-700">
        <i class="bi bi-calendar3"></i> Agenda
      </h1>
      <div class="flex flex-wrap items-center gap-3">
        <select x-model="filterProfessional" @change="refetchEvents()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
          <option value="">Todos os Profissionais</option>
          @foreach($professionals as $prof)
            <option value="{{ $prof->id }}">{{ $prof->user->name }}</option>
          @endforeach
        </select>
        <button @click="openCreateModal()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
          <i class="bi bi-plus-lg"></i> Novo Agendamento
        </button>
      </div>
    </div>
  </div>

  {{-- CALENDAR --}}
  <div class="bg-white rounded-xl shadow p-4 mb-4">
    <div id="calendar"></div>
  </div>

  {{-- LEGENDA --}}
  <div class="bg-white rounded-xl shadow p-4">
    <h3 class="font-semibold text-gray-700 mb-3">
      <i class="bi bi-info-circle"></i> Legenda
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">

      {{-- Status --}}
      <div>
        <p class="font-medium text-gray-600 mb-2">Status do Agendamento</p>
        <ul class="space-y-1">
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Pendente
          </li>
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Confirmado
          </li>
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Concluido
          </li>
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Cancelado
          </li>
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-gray-500 inline-block"></span> Nao compareceu
          </li>
        </ul>
      </div>

      {{-- Pagamento --}}
      <div>
        <p class="font-medium text-gray-600 mb-2">Status do Pagamento</p>
        <ul class="space-y-1">
          <li class="flex items-center gap-2">
            <i class="bi bi-currency-dollar text-red-500 text-lg"></i>
            <span>Pagamento pendente</span>
          </li>
          <li class="flex items-center gap-2">
            <i class="bi bi-currency-dollar text-green-500 text-lg"></i>
            <span>Pago</span>
          </li>
          <li class="flex items-center gap-2">
            <i class="bi bi-shield-check text-indigo-500 text-lg"></i>
            <span>Plano / Convenio</span>
          </li>
        </ul>
      </div>

      {{-- Bloqueios --}}
      <div>
        <p class="font-medium text-gray-600 mb-2">Marcacoes Especiais</p>
        <ul class="space-y-1">
          <li class="flex items-center gap-2">
            <span class="w-4 h-3 rounded bg-red-100 border border-red-300 inline-block"></span>
            <span>Data bloqueada (profissional)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-4 h-3 rounded bg-blue-100 border border-blue-300 inline-block"></span>
            <span>Feriado</span>
          </li>
        </ul>
      </div>

      {{-- Interacoes --}}
      <div>
        <p class="font-medium text-gray-600 mb-2">Interacoes</p>
        <ul class="space-y-1">
          <li class="flex items-center gap-2">
            <i class="bi bi-arrows-move text-gray-500"></i>
            <span>Arraste para remarcar</span>
          </li>
          <li class="flex items-center gap-2">
            <i class="bi bi-arrows-expand text-gray-500"></i>
            <span>Redimensione para alterar duracao</span>
          </li>
          <li class="flex items-center gap-2">
            <i class="bi bi-cursor text-gray-500"></i>
            <span>Clique no evento para editar</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- MODAL CREATE / EDIT --}}
  <div x-show="showModal" x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
       @keydown.escape.window="showModal = false" style="display: none;">
    <div @click.outside="showModal = false"
         class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700" x-text="editingId ? 'Editar Agendamento' : 'Novo Agendamento'"></h2>
        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form @submit.prevent="saveAppointment()" class="space-y-4">

        {{-- Paciente --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Paciente *</label>
          <select x-model="form.client_id" required
                  class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
            <option value="">Selecione o paciente</option>
            <template x-for="c in appData.clients" :key="c.id">
              <option :value="c.id" x-text="c.name"></option>
            </template>
          </select>
        </div>

        {{-- Profissional --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Profissional *</label>
          <select x-model="form.professional_id" required @change="filterServices()"
                  class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
            <option value="">Selecione o profissional</option>
            <template x-for="p in appData.professionals" :key="p.id">
              <option :value="p.id" x-text="p.name"></option>
            </template>
          </select>
        </div>

        {{-- Servico --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Servico *</label>
          <select x-model="form.service_id" required @change="calcEndTime()"
                  class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
            <option value="">Selecione o servico</option>
            <template x-for="s in filteredServices" :key="s.id">
              <option :value="s.id" x-text="s.name + ' (' + s.duration_min + ' min)'"></option>
            </template>
          </select>
        </div>

        {{-- Data / Hora --}}
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-gray-600 font-medium">Inicio *</label>
            <input type="datetime-local" x-model="form.start_at" required @change="calcEndTime()"
                   class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
          </div>
          <div>
            <label class="text-sm text-gray-600 font-medium">Fim *</label>
            <input type="datetime-local" x-model="form.end_at" required
                   class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
          </div>
        </div>

        {{-- Status (somente edicao) --}}
        <div x-show="editingId">
          <label class="text-sm text-gray-600 font-medium">Status</label>
          <select x-model="form.status" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
            <option value="pending">Pendente</option>
            <option value="confirmed">Confirmado</option>
            <option value="done">Concluido</option>
            <option value="cancelled">Cancelado</option>
            <option value="no_show">Nao compareceu</option>
          </select>
        </div>

        {{-- Pagamento --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Pagamento</label>
          <div class="flex gap-4 mt-1">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" x-model="form.payment_status" value="pending" class="text-red-500">
              <i class="bi bi-currency-dollar text-red-500"></i> Pendente
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" x-model="form.payment_status" value="paid" class="text-green-500">
              <i class="bi bi-currency-dollar text-green-500"></i> Pago
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" x-model="form.payment_status" value="plan" class="text-indigo-500">
              <i class="bi bi-shield-check text-indigo-500"></i> Plano
            </label>
          </div>
        </div>

        {{-- Valor Cobrado --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Valor Cobrado (R$)</label>
          <input type="number" x-model="form.charged_amount" step="0.01" min="0"
                 class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                 :placeholder="selectedServicePrice ? 'Preço do serviço: R$ ' + Number(selectedServicePrice).toFixed(2) : 'Deixe vazio para usar preço do serviço'">
          <p class="text-xs text-gray-400 mt-1">Deixe vazio para usar o preço padrão do serviço.</p>
        </div>

        {{-- Cor --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Cor do evento</label>
          <div class="flex items-center gap-3 mt-1">
            <input type="color" x-model="form.color"
                   class="w-10 h-10 rounded border cursor-pointer p-0"
                   title="Escolha uma cor">
            <button type="button" @click="form.color = ''"
                    class="text-xs text-gray-500 hover:text-gray-700 underline">
              Usar cor padrao (status)
            </button>
            <span x-show="form.color" class="text-xs text-gray-400" x-text="form.color"></span>
          </div>
        </div>

        {{-- Observacoes --}}
        <div>
          <label class="text-sm text-gray-600 font-medium">Observacoes</label>
          <textarea x-model="form.notes" rows="2" maxlength="500"
                    class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                    placeholder="Observacoes do atendimento..."></textarea>
        </div>

        {{-- Botoes --}}
        <div class="flex justify-between items-center pt-2">
          <div>
            <button x-show="editingId" type="button" @click="deleteAppointment()"
                    class="text-red-600 hover:text-red-800 text-sm font-medium">
              <i class="bi bi-trash"></i> Excluir
            </button>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="showModal = false"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border rounded-lg">
              Cancelar
            </button>
            <button type="submit" :disabled="saving"
                    class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium disabled:opacity-50">
              <span x-show="!saving" x-text="editingId ? 'Atualizar' : 'Agendar'"></span>
              <span x-show="saving">Salvando...</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>

</div>

{{-- FullCalendar CDN --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
const APP_DATA = {
  clients: @json($clientsJson),
  professionals: @json($professionalsJson),
  services: @json($servicesJson),
  csrfToken: '{{ csrf_token() }}'
};

function agendaApp() {
  return {
    calendar: null,
    filterProfessional: '',
    showModal: false,
    editingId: null,
    saving: false,
    appData: APP_DATA,
    filteredServices: APP_DATA.services,

    get selectedServicePrice() {
      if (!this.form.service_id) return null;
      var svc = this.appData.services.find(function(s) { return s.id == this.form.service_id; }.bind(this));
      return svc ? svc.price : null;
    },
    form: {
      client_id: '',
      professional_id: '',
      service_id: '',
      start_at: '',
      end_at: '',
      status: 'pending',
      payment_status: 'pending',
      charged_amount: '',
      color: '',
      notes: '',
    },

    initCalendar() {
      const self = this;
      const calendarEl = document.getElementById('calendar');

      this.calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        height: 700,
        nowIndicator: true,
        navLinks: true,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        selectable: true,
        eventStartEditable: true,
        eventDurationEditable: true,

        eventSources: [
          {
            url: '/admin/agenda/events',
            extraParams: function() { return { professional_id: self.filterProfessional }; },
          },
          {
            url: '/admin/agenda/blocked-dates',
            extraParams: function() { return { professional_id: self.filterProfessional }; },
          }
        ],

        eventContent: function(arg) {
          if (arg.event.display === 'background') return;

          var props = arg.event.extendedProps;
          var paymentIcon = '';

          switch (props.payment_status) {
            case 'paid':
              paymentIcon = '<i class="bi bi-currency-dollar" style="color:#4ade80" title="Pago"></i>';
              break;
            case 'plan':
              paymentIcon = '<i class="bi bi-shield-check" style="color:#a5b4fc" title="Plano/Convenio"></i>';
              break;
            default:
              paymentIcon = '<i class="bi bi-currency-dollar" style="color:#f87171" title="Pagamento pendente"></i>';
          }

          function esc(str) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
          }

          var time = arg.timeText ? '<span style="font-size:0.7em;opacity:0.85">' + arg.timeText + '</span> ' : '';
          var html = '<div style="padding:2px 4px;overflow:hidden;line-height:1.3;cursor:pointer">' +
            time +
            '<span style="font-weight:600;font-size:0.8em">' + paymentIcon + ' ' + esc(arg.event.title) + '</span>' +
            '<br><span style="font-size:0.7em;opacity:0.8">' + esc(props.service_name) + '</span>' +
            '</div>';

          return { html: html };
        },

        eventDidMount: function(info) {
          if (info.event.extendedProps.type === 'blocked' || info.event.extendedProps.type === 'holiday') {
            info.el.style.pointerEvents = 'none';
          }
        },

        dateClick: function(info) {
          self.openCreateModal(info.dateStr);
        },

        eventClick: function(info) {
          if (info.event.display === 'background') return;
          self.openEditModal(info.event);
        },

        eventDrop: function(info) {
          self.handleDragDrop(info);
        },

        eventResize: function(info) {
          self.handleDragDrop(info);
        },
      });

      this.calendar.render();
    },

    refetchEvents() {
      if (this.calendar) {
        this.calendar.refetchEvents();
      }
    },

    resetForm() {
      this.form = {
        client_id: '',
        professional_id: '',
        service_id: '',
        start_at: '',
        end_at: '',
        status: 'pending',
        payment_status: 'pending',
        charged_amount: '',
        color: '',
        notes: '',
      };
      this.filteredServices = this.appData.services;
    },

    openCreateModal(dateStr) {
      this.editingId = null;
      this.resetForm();

      if (dateStr) {
        var hasTime = dateStr.includes('T');
        if (hasTime) {
          this.form.start_at = dateStr.slice(0, 16);
        } else {
          this.form.start_at = dateStr + 'T09:00';
        }
        this.calcEndTime();
      }

      this.showModal = true;
    },

    openEditModal(event) {
      this.editingId = event.id;
      var props = event.extendedProps;

      this.form = {
        client_id: props.client_id,
        professional_id: props.professional_id,
        service_id: props.service_id,
        start_at: event.start ? this.toLocalDatetime(event.start) : '',
        end_at: event.end ? this.toLocalDatetime(event.end) : '',
        status: props.status,
        payment_status: props.payment_status || 'pending',
        charged_amount: props.charged_amount || '',
        color: props.color || '',
        notes: props.notes || '',
      };

      this.filterServices();
      this.showModal = true;
    },

    filterServices() {
      if (this.form.professional_id) {
        this.filteredServices = this.appData.services.filter(
          function(s) { return s.professional_id == this.form.professional_id; }.bind(this)
        );
      } else {
        this.filteredServices = this.appData.services;
      }
    },

    calcEndTime() {
      if (!this.form.start_at || !this.form.service_id) return;

      var service = this.appData.services.find(function(s) { return s.id == this.form.service_id; }.bind(this));
      if (!service) return;

      var start = new Date(this.form.start_at);
      start.setMinutes(start.getMinutes() + service.duration_min);
      this.form.end_at = this.toLocalDatetime(start);
    },

    toLocalDatetime(date) {
      if (typeof date === 'string') date = new Date(date);
      var pad = function(n) { return String(n).padStart(2, '0'); };
      return date.getFullYear() + '-' + pad(date.getMonth()+1) + '-' + pad(date.getDate()) +
             'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    },

    async saveAppointment() {
      this.saving = true;

      var url = this.editingId
        ? '/admin/agenda/' + this.editingId
        : '/admin/agenda';

      var method = this.editingId ? 'PUT' : 'POST';

      try {
        var res = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.appData.csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify(this.form),
        });

        var data = await res.json();

        if (!res.ok) {
          Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: data.message || 'Erro ao salvar agendamento.',
          });
          return;
        }

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: data.message,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        });

        this.showModal = false;
        this.refetchEvents();
      } catch (e) {
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro de conexao.' });
      } finally {
        this.saving = false;
      }
    },

    async deleteAppointment() {
      var result = await Swal.fire({
        title: 'Excluir agendamento?',
        text: 'Esta acao nao pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
      });

      if (!result.isConfirmed) return;

      try {
        var res = await fetch('/admin/agenda/' + this.editingId, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.appData.csrfToken,
            'Accept': 'application/json',
          },
        });

        var data = await res.json();

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: data.message,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        });

        this.showModal = false;
        this.refetchEvents();
      } catch (e) {
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao excluir.' });
      }
    },

    async handleDragDrop(info) {
      var event = info.event;

      if (!event.end) {
        info.revert();
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Evento sem horario de termino.' });
        return;
      }

      try {
        var res = await fetch('/admin/agenda/' + event.id, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.appData.csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            start_at: event.start.toISOString(),
            end_at: event.end.toISOString(),
          }),
        });

        var data = await res.json();

        if (!res.ok) {
          info.revert();
          Swal.fire({
            icon: 'error',
            title: 'Nao foi possivel remarcar',
            text: data.message || 'Erro ao remarcar agendamento.',
          });
          return;
        }

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Agendamento remarcado com sucesso.',
          showConfirmButton: false,
          timer: 2500,
          timerProgressBar: true,
        });
      } catch (e) {
        info.revert();
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro de conexao.' });
      }
    },
  };
}
</script>

<style>
  .fc .fc-toolbar-title { font-size: 1.1rem !important; }
  .fc .fc-button { font-size: 0.8rem !important; }
  .fc .fc-daygrid-event { cursor: grab; }
  .fc .fc-timegrid-event { cursor: grab; }
  .fc-event:active { cursor: grabbing !important; }
</style>
@endsection
