<script>
function agendamento(preset = {}) {

  /* ===============================================================
     ROTAS DO BACKEND
     =============================================================== */
  const routes = {
    estados:        '{{ route('client.estados') }}',
    cidades:        '{{ route('client.cidades') }}',
    especialidades: '{{ route('client.especialidades') }}',
    procedimentos:  '{{ route('client.procedimentos') }}',
    profissionais:  '{{ route('client.profissionais') }}',

    horarios:       '{{ url('/client/horarios') }}',
    preAgendar:     '{{ route('client.preagendar') }}',
  };

  /* ===============================================================
     CSRF TOKEN PARA POST
     =============================================================== */
  const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

  /* ===============================================================
     TRATAMENTO SEGURO PARA JSON
     =============================================================== */
  async function safeJson(response) {
    const text = await response.text();

    // sessão expirada → Laravel devolve HTML da tela de login
    if (text.startsWith('<!DOCTYPE') || text.includes('<html')) {
      console.error("❌ HTML recebido — sessão expirada ou rota incorreta");
      Swal.fire({
        icon: 'error',
        title: 'Sessão expirada',
        text: 'Faça login novamente.'
      }).then(() => window.location.href = "{{ route('login') }}");
      throw new Error("Sessão expirada — HTML retornado");
    }

    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("❌ JSON inválido recebido do servidor:", text);
      throw new Error("Resposta não é JSON");
    }
  }

  /* ===============================================================
     COMPONENTE ALPINE
     =============================================================== */
  return {

    /* ------------------------------
       STATE
       ------------------------------ */
    uf: '',
    cidade: '',
    profissional: '',
    especialidade: '',
    procedimento: '',
    dataSelecionada: '',
    horarioSelecionado: '',

    ufs: [],
    cidades: [],
    profissionais: [],
    especialidades: [],
    procedimentos: [],
    horarios: [],

    profissionalSelecionado: null,
    minDate: '',
    clientName: preset.clientName,
    clientEmail: preset.clientEmail,

    /* ===============================================================
       INIT
       =============================================================== */
    init() {
      this.minDate = new Date().toISOString().slice(0, 10);
      this.dataSelecionada = this.minDate;
      this.loadUFs();
    },

    /* ===============================================================
       CARREGAMENTO DE LISTAS
       =============================================================== */

    async loadUFs() {
      const r = await fetch(routes.estados, { credentials: "include" });
      this.ufs = await safeJson(r);
    },

    async loadEspecialidades() {
      const params = new URLSearchParams({ state: this.uf, city: this.cidade });
      const r = await fetch(`${routes.especialidades}?${params}`, { credentials: "include" });
      this.especialidades = await safeJson(r);
    },

    async loadProcedimentos() {
      const params = new URLSearchParams({ state: this.uf, city: this.cidade });
      if (this.especialidade) params.append('specialty', this.especialidade);

      const r = await fetch(`${routes.procedimentos}?${params}`, { credentials: "include" });
      this.procedimentos = await safeJson(r);
    },

    async loadProfissionais() {
      const params = new URLSearchParams({
        state: this.uf,
        city: this.cidade
      });
      if (this.especialidade) params.append('specialty', this.especialidade);
      if (this.procedimento) params.append('procedure', this.procedimento);

      const r = await fetch(`${routes.profissionais}?${params}`, { credentials: "include" });
      this.profissionais = await safeJson(r);

      this.profissionalSelecionado =
        this.profissionais.find(p => p.id == this.profissional) || null;
    },

    /* ===============================================================
       EVENTOS DE TROCA DE FILTROS
       =============================================================== */

    async changeUf() {
      this.cidade = '';
      this.cidades = [];
      this.resetFilters();

      if (!this.uf) return;

      const r = await fetch(`${routes.cidades}?state=${this.uf}`, { credentials: "include" });
      this.cidades = await safeJson(r);
    },

    async changeCidade() {
      this.resetFilters();
      await this.loadEspecialidades();
      await this.loadProcedimentos();
      await this.loadProfissionais();
    },

    async changeEspecialidade() {
      await this.loadProcedimentos();
      await this.loadProfissionais();
    },

    async changeProcedimento() {
      await this.loadProfissionais();
    },

    changeProfissional() {
      this.profissional = parseInt(this.profissional);
      this.profissionalSelecionado =
        this.profissionais.find(p => p.id == this.profissional) || null;

      this.horarios = [];
    },

    resetFilters() {
      this.profissional = '';
      this.especialidade = '';
      this.procedimento = '';

      this.profissionais = [];
      this.especialidades = [];
      this.procedimentos = [];
      this.horarios = [];

      this.horarioSelecionado = '';
    },

    /* ===============================================================
       FORMATAÇÃO
       =============================================================== */

    formatarData(data) {
      const [y, m, d] = data.split('-');
      return `${d}/${m}/${y}`;
    },

    /* ===============================================================
       🔥 BUSCAR HORÁRIOS
       =============================================================== */

    async carregarHorarios() {

      if (!this.profissional || !this.procedimento || !this.dataSelecionada) {
        Swal.fire({
          icon: 'warning',
          title: 'Campos obrigatórios',
          text: 'Selecione profissional, procedimento e data.'
        });
        return;
      }

      const url = `${routes.horarios}/${this.profissional}?date=${this.dataSelecionada}`;
      console.log("🔎 URL:", url);

      const r = await fetch(url, { credentials: "include" });
      const data = await safeJson(r);

      console.log("📥 Backend retornou:", data);

      if (!data.success) {
        Swal.fire({
          icon: 'warning',
          title: 'Indisponível',
          text: data.message
        });
        this.horarios = [];
        return;
      }

      if (data.date && data.date !== this.dataSelecionada) {
        this.dataSelecionada = data.date; // backend ajusta p/ próxima data útil
      }

      this.horarios = data.slots;
    },

    selecionarHorario(h) {
      this.horarioSelecionado = h;
    },

    /* ===============================================================
       CONFIRMAÇÃO
       =============================================================== */

    async confirmarAgendamento() {

      if (!this.profissional || !this.procedimento || !this.dataSelecionada || !this.horarioSelecionado) {
        Swal.fire({
          icon: 'warning',
          title: 'Campos obrigatórios',
          text: 'Selecione profissional, procedimento, data e horário.'
        });
        return;
      }

      const payload = {
        professional_id: this.profissional,
        procedure: this.procedimento,
        date: this.dataSelecionada,
        time: this.horarioSelecionado,
        client_name: this.clientName,
        client_email: this.clientEmail,
      };

      const res = await fetch(routes.preAgendar, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(payload)
      });

      const data = await safeJson(res);

      if (!data.success) {
        return Swal.fire({
          icon: 'error',
          title: 'Erro',
          text: data.message
        });
      }

      Swal.fire({
        icon: 'success',
        title: 'Pré-agendamento enviado!',
        text: 'Aguarde a confirmação por e-mail.'
      });

      this.horarios = [];
      this.horarioSelecionado = '';
    }
  };
}
</script>
