<script>
function agendamento(preset = {}) {

  /* ============================================================
     ROTAS API
     ============================================================ */
  const API_BASE = '/api/client';

  const routes = {
    estados:        `${API_BASE}/public/estados`,
    cidades:        `${API_BASE}/public/cidades`,
    especialidades: `${API_BASE}/public/especialidades`,
    procedimentos:  `${API_BASE}/public/procedimentos`,
    profissionais:  `${API_BASE}/public/profissionais`,
    horarios:       `${API_BASE}/public/horarios`,
    preAgendar:     `/client/appointments`   // ROTA WEB (auth:client)
  };


  /* ============================================================
     SAFE JSON
     ============================================================ */
  async function safeJson(response) {
    if (response.status === 401) {
      Swal.fire({
        icon: "error",
        title: "Sessão expirada",
        text: "Faça login novamente."
      }).then(() => window.location.href = "/login");
      throw new Error("401");
    }

    const text = await response.text();

    if (text.startsWith("<!DOCTYPE") || text.includes("<html")) {
      Swal.fire({
        icon: "error",
        title: "Sessão expirada",
        text: "Faça login novamente."
      }).then(() => window.location.href = "/login");
      throw new Error("HTML");
    }

    try {
      return JSON.parse(text);
    } catch {
      console.warn("JSON inválido:", text);
      Swal.fire({
        icon: "error",
        title: "Erro inesperado",
        text: "O servidor retornou dados inválidos."
      });
      throw new Error("Invalid JSON");
    }
  }


  /* ============================================================
     COMPONENTE ALPINE
     ============================================================ */
  return {

    uf: '',
    cidade: '',
    especialidade: '',
    procedimento: '',
    profissional: '',
    dataSelecionada: '',
    horarioSelecionado: '',

    ufs: [],
    cidades: [],
    especialidades: [],
    procedimentos: [],
    profissionais: [],
    horarios: [],
    profissionalSelecionado: null,

    minDate: '',
    clientName: preset.clientName,
    clientEmail: preset.clientEmail,


    /* ------------------------------------------------------------
       INIT
       ------------------------------------------------------------ */
    init() {
      this.minDate = new Date().toISOString().slice(0, 10);
      this.dataSelecionada = this.minDate;
      this.loadUFs();
    },


    /* ------------------------------------------------------------
       FORMATAR DATA (usado no HTML)
       ------------------------------------------------------------ */
    formatarData(dt) {
      if (!dt) return '-';
      const d = new Date(dt);
      return d.toLocaleDateString('pt-BR');
    },


    /* ------------------------------------------------------------
       UF / CIDADE
       ------------------------------------------------------------ */
    async loadUFs() {
      const r = await fetch(routes.estados);
      this.ufs = await safeJson(r);
    },

    async changeUf() {
      this.resetFilters();
      if (!this.uf) return;

      const r = await fetch(`${routes.cidades}?state=${this.uf}`);
      this.cidades = await safeJson(r);
    },

    async changeCidade() {
      this.resetFilters();

      await this.loadEspecialidades();
      await this.loadProcedimentos();
      await this.loadProfissionais();
    },


    /* ------------------------------------------------------------
       FILTROS PRINCIPAIS (Funções chamadas no HTML!)
       ------------------------------------------------------------ */

    async changeEspecialidade() {
      await this.loadProcedimentos();
      await this.loadProfissionais();
    },

    async changeProcedimento() {
      await this.loadProfissionais();
    },

    async changeProfissional() {
      this.profissionalSelecionado =
        this.profissionais.find(p => p.id == this.profissional) || null;
    },


    /* ------------------------------------------------------------
       LOADS REAIS
       ------------------------------------------------------------ */
    async loadEspecialidades() {
      const r = await fetch(
        `${routes.especialidades}?state=${this.uf}&city=${this.cidade}`
      );
      this.especialidades = await safeJson(r);
    },

    async loadProcedimentos() {
      let url = `${routes.procedimentos}?state=${this.uf}&city=${this.cidade}`;
      if (this.especialidade) url += `&specialty=${this.especialidade}`;
      const r = await fetch(url);
      this.procedimentos = await safeJson(r);
    },

    async loadProfissionais() {
      let url = `${routes.profissionais}?state=${this.uf}&city=${this.cidade}`;

      if (this.especialidade) url += `&specialty=${this.especialidade}`;
      if (this.procedimento)  url += `&procedure=${this.procedimento}`;

      const r = await fetch(url);
      this.profissionais = await safeJson(r);

      this.profissionalSelecionado =
        this.profissionais.find(p => p.id == this.profissional) || null;
    },


    /* ------------------------------------------------------------
       HORÁRIOS
       ------------------------------------------------------------ */
    async carregarHorarios() {

      if (!this.profissional || !this.procedimento || !this.dataSelecionada) {
        Swal.fire({
          icon: "warning",
          title: "Campos obrigatórios",
          text: "Selecione profissional, procedimento e data."
        });
        return;
      }

      const r = await fetch(
        `${routes.horarios}/${this.profissional}?date=${this.dataSelecionada}`
      );

      const data = await safeJson(r);

      if (!data.success) {
        Swal.fire({
          icon: "warning",
          title: "Indisponível",
          text: data.message
        });
        this.horarios = [];
        return;
      }

      this.horarios = data.slots;
    },


    selecionarHorario(h) {
      this.horarioSelecionado = h;
    },


    /* ------------------------------------------------------------
       CONFIRMAR AGENDAMENTO (SESSÃO WEB)
       ------------------------------------------------------------ */
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
        procedure:       this.procedimento,
        date:            this.dataSelecionada,
        time:            this.horarioSelecionado,
      };

      const res = await fetch(routes.preAgendar, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'include',
        body: JSON.stringify(payload)
      });

      const data = await safeJson(res);

      if (!data.success) {
        Swal.fire({
          icon: "error",
          title: "Erro",
          text: data.message
        });
        return;
      }

      Swal.fire({
        icon: "success",
        title: "Pré-agendamento enviado!",
        text: "Aguarde a confirmação por e-mail."
      });

      this.horarios = [];
      this.horarioSelecionado = '';
    },


    /* ------------------------------------------------------------
       RESET
       ------------------------------------------------------------ */
    resetFilters() {
      this.profissional = '';
      this.especialidade = '';
      this.procedimento  = '';
      this.profissionais = [];
      this.especialidades = [];
      this.procedimentos = [];
      this.horarios = [];
      this.horarioSelecionado = '';
      this.profissionalSelecionado = null;
    }
  };
}
</script>
