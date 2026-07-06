<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { login, register } from '@/routes';
import { redirect as googleRedirect } from '@/routes/auth/google';

// Menu responsive state
const isMenuOpen = ref(false);

// Auth helpers — when Google OAuth is enabled (server-side flag shared via Inertia),
// the primary CTAs jump straight to the OAuth round-trip; otherwise they fall back
// to the standard email signup. Mirrors components/auth/SocialLogin.vue.
const page = usePage();
const googleAuthEnabled = computed(() => Boolean(page.props.googleAuthEnabled));
const signupHref = computed(() => (googleAuthEnabled.value ? googleRedirect.url() : register().url));

// Calendar days data
const days = [
  { dayStr: 'Seg', dateStr: '8', items: [
    { bg: 'bg-pink-100', platform: 'Instagram', themeColor: 'text-rose-600', time: '09:00', text: 'Nova coleção no ar 🎉' },
    { bg: 'bg-blue-100', platform: 'LinkedIn', themeColor: 'text-blue-700', time: '14:00', text: 'Bastidores da empresa' },
  ]},
  { dayStr: 'Ter', dateStr: '9', items: [
    { bg: 'bg-sky-100', platform: 'Facebook', themeColor: 'text-blue-600', time: '11:30', text: 'Compartilhando a novidade' },
    { bg: 'bg-emerald-100', platform: 'Instagram', themeColor: 'text-emerald-700', time: '17:00', text: 'Reels sobre consistência' },
  ]},
  { dayStr: 'Qua', dateStr: '10', items: [
    { bg: 'bg-fuchsia-100', platform: 'TikTok', themeColor: 'text-fuchsia-600', time: '10:00', text: 'Dicas práticas no TikTok 📅' },
    { bg: 'bg-red-100', platform: 'YouTube', themeColor: 'text-red-600', time: '18:00', text: 'Vlog dos bastidores' },
  ]}
];

const extraDays = [
  { dayStr: 'Qui', dateStr: '11' },
  { dayStr: 'Sex', dateStr: '12' },
  { dayStr: 'Sáb', dateStr: '13' }
];

// Features grid lists
const features = [
  { num: '01', title: 'Calendário visual', desc: 'Veja todo post agendado, em todas as redes, num único calendário com drag-and-drop. Reordene arrastando, nunca mais fique sem saber o que entra na sexta.', cols: 'md:col-span-4', bg: 'bg-violet-50', icon: 'calendar' },
  { num: '02', title: 'Publicação automática', desc: 'Define o horário e segue a vida. A TryPost publica via API oficial e te avisa só quando algo falha.', cols: 'md:col-span-2 md:row-span-2', bg: 'bg-amber-50', icon: 'rocket' },
  { num: '03', title: 'Todas as redes, um editor', desc: 'Instagram, Facebook, LinkedIn, TikTok, YouTube e Threads, tudo no mesmo composer.', cols: 'md:col-span-2', bg: 'bg-rose-50', icon: 'users' },
  { num: '04', title: 'Fluxo de time', desc: 'Presença em tempo real mostra quem está editando. Aprovação trava erro no dia do lançamento.', cols: 'md:col-span-2', bg: 'bg-sky-50', icon: 'users-round' },
  { num: '05', title: 'Biblioteca de mídia', desc: 'Um lugar pra cada imagem. Reaproveite criativos sem re-upload.', cols: 'md:col-span-3', bg: 'bg-emerald-50', icon: 'image' },
  { num: '06', title: 'Preview por rede', desc: 'Veja seu post exatamente como cada rede vai renderizar. Pega o detalhe antes de publicar.', cols: 'md:col-span-3', bg: 'bg-fuchsia-50', icon: 'eye' },
];

// Use cases marquee list
const cases = [
  { type: 'Criador solo', title: 'Criadores', desc: 'Bateladas de uma semana de conteúdo no domingo à tarde, fila de posts no Instagram e TikTok num único calendário, e desliga.', bg: 'bg-white', icon: 'user' },
  { type: 'Dono de agência', title: 'Agências', desc: 'Roda 15 clientes em 15 workspaces separados, com revisores por cliente. Aprovações saem de planilhas e viram comentários.', bg: 'bg-violet-50', icon: 'briefcase' },
  { type: 'Dono de pequeno negócio', title: 'Pequenos negócios', desc: 'Dono de padaria agenda uma semana de posts de doces. Vê o preview em todas as redes antes de publicar.', bg: 'bg-amber-50', icon: 'store' },
  { type: 'Blogueira de lifestyle', title: 'Blogueiros', desc: 'Planeja um mês de conteúdo por pilares de tema. Ferramenta de melhor horário mostra as janelas ativas.', bg: 'bg-sky-50', icon: 'edit' },
];

// Duplicate for visual marquee scrolling infinity effect
const repeatedCases = [...cases, ...cases, ...cases];

// Why choose us section list
const whyPoints = [
  { num: '01', title: 'Fácil e Intuitivo', desc: 'Interface simples e direto ao ponto para qualquer pessoa agendar e gerenciar redes sociais sem complicações.', bg: 'bg-violet-100', icon: 'zap', star: true },
  { num: '02', title: 'Tudo em um só lugar', desc: 'Gerencie todas as suas contas, interações e agendamentos a partir de um único painel central.', bg: 'bg-amber-100', icon: 'layout', star: false },
  { num: '03', title: 'Privacidade em Primeiro Lugar', desc: 'Seus dados são seus. Sem rastreamento, sem venda das suas informações. Total transparência em como lidamos com os dados.', bg: 'bg-rose-100', icon: 'shield', star: false },
];
</script>

<template>
  <div class="flex flex-col min-h-screen overflow-hidden bg-white text-slate-900 font-sans selection:bg-violet-500 selection:text-white">
    
    <!-- HEADER -->
    <header class="sticky top-0 z-40 border-b-2 border-slate-900 bg-white/90 backdrop-blur-md transition-shadow">
      <nav class="mx-auto flex max-w-7xl items-center justify-between gap-x-6 px-6 py-4">
        <div class="flex lg:flex-1">
          <a href="#" class="inline-flex items-center text-xl font-black tracking-tighter">
            <span class="text-violet-600 mr-1">◆</span> TryPost
          </a>
        </div>
        
        <div class="hidden lg:flex gap-1 items-center justify-center">
          <a href="#" class="px-4 py-2 text-sm font-semibold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Blog</a>
          <a href="#" class="px-4 py-2 text-sm font-semibold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Preços</a>
          <button class="flex items-center gap-1 px-4 py-2 text-sm font-semibold hover:bg-slate-100 rounded-md transition-colors group text-slate-800">
            Redes Sociais
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 transition duration-300 group-hover:rotate-180"><path d="m6 9 6 6 6-6"/></svg>
          </button>
        </div>

        <div class="hidden lg:flex flex-1 items-center justify-end gap-x-3">
          <a :href="login().url" class="text-sm font-semibold px-4 py-2 hover:bg-slate-100 rounded-md transition-colors text-slate-800">Entrar</a>
          <a :href="signupHref" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-900 bg-violet-600 text-white pl-1 pr-4 py-1.5 text-sm font-bold shadow-[2px_2px_0px_0px_rgba(15,23,42,1)] hover:shadow-[4px_4px_0px_0px_rgba(15,23,42,1)] hover:-translate-y-0.5 transition-all">
            <span class="inline-flex w-7 h-7 items-center justify-center rounded-full bg-white text-slate-900">
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            </span>
            Comece grátis
          </a>
        </div>

        <!-- Mobile menu toggle button -->
        <div class="flex lg:hidden">
          <button @click="isMenuOpen = !isMenuOpen" class="w-10 h-10 flex items-center justify-center border-2 border-slate-900 bg-white hover:bg-slate-100 rounded-md transition-colors">
            <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
          </button>
        </div>
      </nav>

      <!-- Mobile drawer menu -->
      <div v-if="isMenuOpen" class="lg:hidden border-t-2 border-slate-900 bg-white px-6 py-5 space-y-4 shadow-lg animate-fadeIn">
        <div class="flex flex-col gap-2">
          <a href="#" class="px-4 py-2.5 text-base font-bold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Blog</a>
          <a href="#" class="px-4 py-2.5 text-base font-bold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Preços</a>
          <a href="#" class="px-4 py-2.5 text-base font-bold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Redes Sociais</a>
          <a :href="login().url" class="px-4 py-2.5 text-base font-bold hover:bg-slate-100 rounded-md transition-colors text-slate-800">Entrar</a>
        </div>
        <div class="pt-4 border-t border-slate-200">
          <a :href="signupHref" class="flex items-center justify-center gap-2 rounded-full border-2 border-slate-900 bg-violet-600 text-white py-3 text-base font-bold shadow-[2px_2px_0px_0px_rgba(15,23,42,1)]">
            <span class="inline-flex w-7 h-7 items-center justify-center rounded-full bg-white text-slate-900">
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            </span>
            Comece grátis
          </a>
        </div>
      </div>
    </header>

    <main class="grow">

      <!-- HERO SECTION -->
      <section class="relative overflow-hidden bg-white py-16 lg:py-24">
        <!-- Radial Dot Grid Background Pattern -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(circle, #0a0a0a 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="absolute -top-20 right-0 w-[520px] h-[520px] rounded-full bg-violet-200/50 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 -left-32 w-[420px] h-[420px] rounded-full bg-fuchsia-200/40 blur-3xl pointer-events-none"></div>
        
        <div class="relative max-w-7xl mx-auto px-6">
          <div class="grid lg:grid-cols-[1.05fr_1fr] gap-12 lg:gap-16 items-center">
            
            <!-- Left Info Content -->
            <div class="text-center lg:text-left max-w-xl mx-auto lg:mx-0">
              <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 mb-5 text-balance tracking-tight leading-tight">
                Coloque suas redes sociais<br/>
                <span class="relative inline-block mt-1">
                  <span class="relative z-10 text-violet-600">no piloto automático.</span>
                  <svg class="absolute -bottom-2 left-0 right-0 h-2.5 w-full text-violet-400" viewBox="0 0 200 12" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M 5 6 Q 25 0, 50 6 T 100 6 T 150 6 T 195 6" /></svg>
                </span>
              </h1>
              
              <p class="text-base sm:text-xl text-slate-600 mb-8 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                Planeje, gere e agende posts pra todas as principais redes sociais. Revise tudo num calendário visual. Legendas com IA e aprovação de time inclusas.
              </p>
              
              <!-- Double Primary Call to Action buttons -->
              <div class="flex flex-col sm:flex-row items-center lg:items-start lg:justify-start justify-center gap-3 mb-6">
                <a :href="signupHref" class="relative inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full border-2 border-slate-900 bg-violet-600 text-white pl-12 sm:pl-1.5 pr-5 py-3 sm:py-1.5 text-base font-bold shadow-[2px_2px_0px_0px_rgba(15,23,42,1)] hover:shadow-[4px_4px_0px_0px_rgba(15,23,42,1)] transition-all">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 sm:static sm:translate-y-0 inline-flex w-9 h-9 items-center justify-center rounded-full bg-white text-slate-900">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                  </span>
                  Comece grátis
                </a>
                <a :href="register().url" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full border-2 border-slate-900 bg-white text-slate-900 px-6 py-3 text-base font-bold shadow-[2px_2px_0px_0px_rgba(15,23,42,1)] hover:shadow-[4px_4px_0px_0px_rgba(15,23,42,1)] hover:-translate-y-0.5 transition-all">
                  Testar demonstrativo
                </a>
              </div>
              
              <!-- Star feedback and active creators badge -->
              <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs sm:text-sm justify-center lg:justify-start mt-4">
                <div class="flex -space-x-2">
                  <div v-for="i in 5" :key="i" class="w-9 h-9 rounded-full border-2 border-slate-900 bg-violet-200 shadow-2xs flex items-center justify-center overflow-hidden">
                    <img :src="`https://i.pravatar.cc/100?img=${i + 14}`" alt="Avatar" class="w-full h-full object-cover" />
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <div class="flex gap-0.5 text-amber-400">
                    <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21a.75.75 0 0 1 1.424 0l2.082 5.006 5.4 1.181a.75.75 0 0 1 .416 1.279l-3.9 3.841 1.17 5.359a.75.75 0 0 1-1.097.798L12 18.156l-4.837 2.504a.75.75 0 0 1-1.097-.798l1.17-5.36-3.9-3.842a.75.75 0 0 1 .417-1.278l5.4-1.18 2.082-5.007Z" clip-rule="evenodd" /></svg>
                  </div>
                </div>
                <span class="font-bold text-slate-600">Usado por 500+ criadores e agências</span>
              </div>
            </div>
            
            <!-- Right Mockup Design -->
            <div class="relative max-w-md mx-auto lg:mx-0 lg:ml-auto w-full lg:rotate-1 mt-10 lg:mt-0">
              <div class="hidden lg:block absolute inset-0 -bottom-4 -right-4 rounded-xl border-2 border-slate-900 bg-violet-100 -rotate-3 -z-10"></div>
              
              <!-- Badges -->
              <div class="absolute -top-7 -left-4 sm:-left-8 z-20 -rotate-12 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-violet-300 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
                <span>✦ Automático</span>
              </div>
              
              <div class="absolute -top-5 -right-2 sm:-right-6 z-20 rotate-6 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-amber-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
                7 dias grátis
              </div>
              
              <!-- Live Post Composer Card Mockup -->
              <div class="relative rounded-xl border-2 border-slate-900 bg-white shadow-xl overflow-hidden">
                <!-- Browser top header -->
                <div class="flex items-center gap-3 border-b-2 border-slate-900 bg-slate-50 px-4 py-3">
                  <div class="flex gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-900 bg-rose-400"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-900 bg-amber-300"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-900 bg-emerald-400"></span>
                  </div>
                  <div class="ml-2 text-[11px] font-black uppercase tracking-widest text-slate-500">trypost.it · novo post</div>
                  <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md border-2 border-slate-900 bg-slate-900 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                    <span class="relative inline-flex w-1.5 h-1.5">
                      <span class="absolute inset-0 inline-flex rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                      <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    </span>
                    Ao vivo
                  </span>
                </div>
                
                <!-- Composer Editor -->
                <div class="p-5 space-y-3 border-b-2 border-slate-900/10">
                  <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Criar post</div>
                  <p class="text-sm font-bold text-slate-900">Gere engajamento de verdade 🚀</p>
                  <p class="text-sm text-slate-600 leading-relaxed">Programe seus posts, organize suas tarefas e cresça sua audiência no piloto automático.</p>
                  
                  <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-2">
                    <span class="text-[10px] font-bold text-slate-400 tabular-nums">104 / 280</span>
                    <span class="inline-flex items-center gap-1 rounded-md border-2 border-slate-900 bg-violet-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
                      ✨ Legendas com IA
                    </span>
                  </div>
                </div>
                
                <!-- Platforms checklist -->
                <div class="p-5 space-y-2.5 border-b-2 border-slate-900/10 bg-slate-50/50">
                  <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Publicar em</div>
                  
                  <div class="flex flex-wrap gap-2">
                    <!-- Instagram -->
                    <div class="relative inline-flex w-10 h-10 items-center justify-center rounded-md border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="w-5.5 h-5.5 text-rose-600"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                      <span class="absolute -top-1.5 -right-1.5 inline-flex w-4 h-4 items-center justify-center rounded-full border border-slate-900 bg-slate-900 text-white font-extrabold text-[8px]"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                    </div>
                    <!-- Twitter / X -->
                    <div class="relative inline-flex w-10 h-10 items-center justify-center rounded-md border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5 text-slate-900"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg>
                      <span class="absolute -top-1.5 -right-1.5 inline-flex w-4 h-4 items-center justify-center rounded-full border border-slate-900 bg-slate-900 text-white font-extrabold text-[8px]"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                    </div>
                    <!-- YouTube -->
                    <div class="relative inline-flex w-10 h-10 items-center justify-center rounded-md border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="w-5.5 h-5.5 text-red-600"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                      <span class="absolute -top-1.5 -right-1.5 inline-flex w-4 h-4 items-center justify-center rounded-full border border-slate-900 bg-slate-900 text-white font-extrabold text-[8px]"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                    </div>
                    <!-- TikTok -->
                    <div class="relative inline-flex w-10 h-10 items-center justify-center rounded-md border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-teal-600"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>
                      <span class="absolute -top-1.5 -right-1.5 inline-flex w-4 h-4 items-center justify-center rounded-full border border-slate-900 bg-slate-900 text-white font-extrabold text-[8px]"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                    </div>
                    
                    <div class="inline-flex w-10 h-10 items-center justify-center rounded-md border-2 border-dashed border-slate-300 bg-white">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    </div>
                  </div>
                </div>
                
                <!-- Schedule Controls footer -->
                <div class="p-5 space-y-3">
                  <div class="flex items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 rounded-md border-2 border-slate-900 bg-white px-3 py-1.5 shadow-[1px_1px_0px_0px_rgba(15,23,42,1)]">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-700"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                      <span class="text-xs font-bold text-slate-800 tabular-nums">Seg · 10:00</span>
                    </div>
                    <button class="inline-flex items-center justify-center gap-1.5 rounded-full border-2 border-slate-900 bg-slate-900 text-white px-5 py-2 text-xs font-black uppercase tracking-widest shadow-[1px_1px_0px_0px_rgba(15,23,42,1)] hover:shadow-[3px_3px_0px_0px_rgba(15,23,42,1)] transition-all">
                      Agendar
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><line x1="5" x2="19" y1="12" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </section>

      <!-- CALENDAR SECTION -->
      <section class="relative bg-white border-t-2 border-slate-900 py-20 sm:py-28">
        <div class="absolute top-1/3 -left-32 w-[440px] h-[440px] rounded-full bg-amber-200/30 blur-3xl pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="max-w-3xl mx-auto text-center mb-20 space-y-4">
            <span class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest border-2 border-slate-900 bg-amber-200 px-3.5 py-1.5 rounded-md shadow-sm -rotate-1">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
              Calendário de conteúdo
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
              Planeje um mês de redes sociais em 5 minutos
            </h2>
            <svg class="mx-auto h-3 w-40 text-violet-400" viewBox="0 0 200 12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
              <path d="M 5 6 Q 25 0, 50 6 T 100 6 T 150 6 T 195 6" />
            </svg>
            <p class="text-slate-600 text-lg leading-relaxed max-w-xl mx-auto">
              Arraste qualquer post pra um novo horário, filtre por rede ou workspace, e veja todo o seu calendário de redes sociais numa só tela.
            </p>
          </div>

          <!-- Calendar Display Grid wrapper -->
          <div class="relative mx-auto w-full max-w-5xl">
            <span class="absolute -top-8 left-4 sm:left-10 z-20 -rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-violet-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              📅 Abr 2026 · 18 agendados
            </span>
            <span class="absolute -bottom-6 right-8 sm:right-10 z-20 rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-emerald-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              ✨ Publicação automática
            </span>
            
            <div class="relative rounded-xl border-2 border-slate-900 bg-white shadow-xl overflow-hidden">
              <!-- Top browser tab -->
              <div class="flex items-center justify-between gap-3 border-b-2 border-slate-900 bg-slate-50 px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-rose-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-amber-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-emerald-300"></span>
                  </div>
                  <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400">trypost.it · esta semana</div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 rounded bg-slate-900 text-[10px] font-black uppercase tracking-widest text-white px-2 py-0.5 shadow-sm">
                  ● Ao vivo
                </span>
              </div>
              
              <!-- Content columns scroll for small viewports -->
              <div class="p-4 sm:p-6 lg:p-8 overflow-x-auto no-scrollbar">
                <div class="grid grid-cols-6 gap-4 min-w-[780px] lg:min-w-0">
                  
                  <div v-for="(day, idx) in days" :key="idx" class="space-y-3">
                    <div class="text-center pb-2 mb-1 border-b-2 border-slate-100">
                      <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ day.dayStr }}</div>
                      <div class="text-xl font-extrabold text-slate-900 tabular-nums">{{ day.dateStr }}</div>
                    </div>
                    
                    <div class="space-y-3">
                      <div v-for="(item, i) in day.items" :key="i" :class="[item.bg, 'rounded-md border-2 border-slate-900 p-3 shadow-sm flex flex-col gap-2 h-24 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-default']">
                        <div class="flex items-center justify-between">
                          <div class="inline-flex w-6 h-6 items-center justify-center rounded border border-slate-900 bg-white">
                            <!-- Platform custom icons mapping -->
                            <svg v-if="item.platform === 'Instagram'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-rose-600"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path></svg>
                            <svg v-else-if="item.platform === 'LinkedIn'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"></svg>
                            <svg v-else-if="item.platform === 'Facebook'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-blue-600"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                            <svg v-else-if="item.platform === 'TikTok'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5 text-fuchsia-600"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>
                            <svg v-else-if="item.platform === 'YouTube'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-red-600"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path></svg>
                          </div>
                          <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 tabular-nums">{{ item.time }}</span>
                        </div>
                        <p class="text-[10px] sm:text-[11px] leading-tight font-bold text-slate-800 line-clamp-2">{{ item.text }}</p>
                      </div>
                      
                      <!-- Empty scheduler slot -->
                      <div class="rounded-md border-2 border-dashed border-slate-200 bg-slate-50 h-24 flex items-center justify-center hover:border-slate-400 hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Upcoming placeholder calendar days -->
                  <div v-for="item in extraDays" :key="item.dateStr" class="space-y-3">
                    <div class="text-center pb-2 mb-1 border-b-2 border-slate-100">
                      <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ item.dayStr }}</div>
                      <div class="text-xl font-extrabold text-slate-900 tabular-nums">{{ item.dateStr }}</div>
                    </div>
                    <div class="space-y-3">
                      <div class="rounded-md border-2 border-dashed border-slate-200 bg-slate-50 h-24 flex items-center justify-center hover:border-slate-400 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                      </div>
                      <div class="rounded-md border-2 border-dashed border-slate-200 bg-slate-50 h-24 flex items-center justify-center hover:border-slate-400 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ANALYTICS SECTION -->
      <section class="relative bg-white overflow-hidden border-t-2 border-slate-900 py-20 sm:py-28">
        <div class="absolute top-1/3 -right-32 w-[440px] h-[440px] rounded-full bg-violet-200/30 blur-3xl pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mx-auto text-center mb-20 space-y-4">
            <span class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest border-2 border-slate-900 bg-violet-200 px-3.5 py-1.5 rounded-md shadow-sm -rotate-1">
              📊 Analytics de redes sociais
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">Acompanhe o crescimento de seguidores em cada rede</h2>
            <svg class="mx-auto h-3 w-40 text-violet-400" viewBox="0 0 200 12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
              <path d="M 5 6 Q 25 0, 50 6 T 100 6 T 150 6 T 195 6" />
            </svg>
            <p class="text-slate-600 text-lg leading-relaxed max-w-xl mx-auto">
              Seguidores, alcance e engajamento de todas as principais redes sociais num gráfico só. Sem export CSV, sem esperar sync.
            </p>
          </div>
          
          <div class="relative max-w-5xl mx-auto">
            <span class="absolute -top-8 left-4 sm:left-10 z-20 -rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-emerald-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              📈 +47% de crescimento
            </span>
            <span class="absolute -bottom-6 right-4 sm:right-10 z-20 rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-amber-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              📊 Dados em tempo real
            </span>
            
            <div class="rounded-xl border-2 border-slate-900 bg-white shadow-xl overflow-hidden">
              <div class="flex items-center justify-between gap-3 border-b-2 border-slate-900 bg-slate-50 px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-rose-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-amber-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-emerald-300"></span>
                  </div>
                  <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400">trypost.it · analytics</div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-slate-900 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                  <span class="relative inline-flex w-1.5 h-1.5">
                    <span class="absolute inset-0 inline-flex rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                  </span>
                  Ao vivo
                </span>
              </div>
              
              <div class="p-6 sm:p-8 space-y-8">
                <!-- Metrics Grid indicators -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  
                  <div class="bg-violet-100 rounded-xl border-2 border-slate-900 p-5 shadow-sm flex flex-col gap-2 hover:translate-y-[-2px] transition-transform">
                    <div class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5 text-violet-700"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 mt-1">Novos seguidores</span>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900 tabular-nums tracking-tight leading-none">+2,847</div>
                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 tabular-nums select-none">
                      <span>✦ +18%</span>
                      <span class="text-slate-400 font-medium">vs. mês passado</span>
                    </div>
                  </div>
                  
                  <div class="bg-rose-100 rounded-xl border-2 border-slate-900 p-5 shadow-sm flex flex-col gap-2 hover:translate-y-[-2px] transition-transform">
                    <div class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5 text-rose-600"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 mt-1">Engajamento</span>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900 tabular-nums tracking-tight leading-none">6.4%</div>
                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 tabular-nums select-none">
                      <span>✦ +47%</span>
                      <span class="text-slate-400 font-medium">vs. mês passado</span>
                    </div>
                  </div>
                  
                  <div class="bg-amber-100 rounded-xl border-2 border-slate-900 p-5 shadow-sm flex flex-col gap-2 hover:translate-y-[-2px] transition-transform">
                    <div class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5 text-amber-700"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" x2="9" y1="21" y2="9"/><line x1="9" x2="21" y1="9" y2="9"/><line x1="15" x2="15" y1="15" y2="9"/><line x1="15" x2="21" y1="15" y2="15"/></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 mt-1">Alcance total</span>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900 tabular-nums tracking-tight leading-none">128K</div>
                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 tabular-nums select-none">
                      <span>✦ +32%</span>
                      <span class="text-slate-400 font-medium">vs. mês passado</span>
                    </div>
                  </div>
                  
                </div>
                
                <!-- Vector area chart vector styling -->
                <div class="space-y-3">
                  <div class="flex items-baseline justify-between">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Seguidores · Últimos 30 dias</span>
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                      <span class="w-2.5 h-2.5 rounded-full bg-violet-600 inline-block"></span> Seguidores
                    </div>
                  </div>
                  
                  <div class="relative h-56 sm:h-72">
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                      <div class="w-full border-t border-dashed border-slate-100"></div>
                      <div class="w-full border-t border-dashed border-slate-100"></div>
                      <div class="w-full border-t border-dashed border-slate-100"></div>
                      <div class="w-full border-t border-dashed border-slate-100"></div>
                      <div class="w-full border-t-2 border-slate-250"></div>
                    </div>
                    
                    <svg class="absolute inset-0 w-full h-full overflow-visible" viewBox="0 0 1000 250" preserveAspectRatio="none">
                      <defs>
                        <linearGradient id="growthGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.5"></stop>
                          <stop offset="60%" stop-color="#7c3aed" stop-opacity="0.15"></stop>
                          <stop offset="100%" stop-color="#7c3aed" stop-opacity="0"></stop>
                        </linearGradient>
                      </defs>
                      <path d="M 0,225 C 200,222 350,200 500,150 C 650,100 800,50 980,15 L 980,250 L 0,250 Z" fill="url(#growthGradient)"></path>
                      <path d="M 0,225 C 200,222 350,200 500,150 C 650,100 800,50 980,15" fill="none" stroke="#7c3aed" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"></path>
                    </svg>
                    
                    <div class="absolute top-2 right-2 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-white px-3 py-1.5 shadow-sm">
                      <span class="w-2 h-2 rounded-full bg-violet-600"></span>
                      <span class="text-[10px] font-black uppercase tracking-widest text-slate-950">Hoje · 5.400</span>
                    </div>
                  </div>
                  
                  <div class="flex justify-between text-[11px] font-bold uppercase tracking-widest text-slate-400 mt-2">
                    <span>Mar 1</span>
                    <span>Mar 15</span>
                    <span>Hoje</span>
                  </div>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </section>

      <!-- FEATURES GRID SECTION -->
      <section class="relative bg-white border-t-2 border-slate-900 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-6 relative">
          <div class="relative max-w-3xl mx-auto text-center mb-16 space-y-4">
            <span class="bg-violet-200 inline-block text-xs font-black uppercase tracking-widest border-2 border-slate-900 px-3.5 py-1.5 rounded-md shadow-sm -rotate-1">
              Recursos
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
              O que você de fato faz na TryPost
            </h2>
            <p class="text-slate-600 text-lg leading-relaxed max-w-xl mx-auto">
              As seis coisas que você usa toda semana, desenhadas pra parecer familiares desde o primeiro dia.
            </p>
          </div>

          <div class="mx-auto grid grid-cols-1 md:grid-cols-6 gap-6">
            <div v-for="(opt, i) in features" :key="i" :class="[opt.bg, opt.cols, 'group relative rounded-xl border-2 border-slate-900 p-6 flex flex-col gap-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all']">
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Feature {{ opt.num }}</span>
              
              <div class="w-12 h-12 rounded-lg border-2 border-slate-900 bg-white flex items-center justify-center shadow-sm">
                <!-- Custom mapped inline icons -->
                <svg v-if="opt.icon === 'calendar'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <svg v-else-if="opt.icon === 'rocket'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path d="M4.5 16.5c-1.5 1.26-2 3.25-2 3.25s1.99-.5 3.25-2"></path><path d="M12 12c2.5-2.5 5.5-3.5 5.5-3.5s-1 3-3.5 5.5"></path><path d="M20.3 3.7a1 1 0 0 0-1 0l-15 4c-.7.2-.9 1.1-.3 1.5l3.5 2.5 2.5 3.5c.4.6 1.3.4 1.5-.3l4-15a1 1 0 0 0-.2-1.2z"></path></svg>
                <svg v-else-if="opt.icon === 'users'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <svg v-else-if="opt.icon === 'users-round'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path d="M14 19a6 6 0 0 0-12 0"></path><circle cx="8" cy="9" r="4"></circle><path d="M22 19a6 6 0 0 0-6-6 4 4 0 1 0 0-8"></path></svg>
                <svg v-else-if="opt.icon === 'image'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <svg v-else-if="opt.icon === 'eye'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </div>
              
              <div class="flex flex-col gap-1.5 flex-1">
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ opt.title }}</h3>
                <p class="text-sm text-slate-600 leading-relaxed">{{ opt.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TEAM REVIEW ROW -->
      <section class="relative bg-white overflow-hidden border-t-2 border-slate-900 py-20 sm:py-28">
        <div class="absolute -top-20 -left-32 w-[480px] h-[480px] rounded-full bg-sky-200/35 blur-3xl pointer-events-none"></div>
        
        <div class="relative max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mx-auto text-center mb-16 space-y-4">
            <span class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest border-2 border-slate-900 bg-sky-200 px-3.5 py-1.5 rounded-md shadow-sm -rotate-1">
              👥 Times & aprovações
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">Feito pra times que entregam juntos</h2>
            <svg class="mx-auto h-3 w-40 text-violet-400" viewBox="0 0 200 12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
              <path d="M 5 6 Q 25 0, 50 6 T 100 6 T 150 6 T 195 6" />
            </svg>
            <p class="text-slate-600 text-lg leading-relaxed max-w-xl mx-auto">
              Workspaces separados por cliente, fluxo de aprovação em cada post, comentários pra pedir ajustes, e um link compartilhável pro cliente aprovar sem precisar de conta.
            </p>
          </div>
          
          <div class="relative max-w-5xl mx-auto">
            <span class="absolute -top-7 left-4 sm:left-10 z-20 -rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-sky-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              🏢 Acme · Workspace
            </span>
            <span class="absolute -bottom-6 right-4 sm:right-10 z-20 rotate-3 inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-emerald-200 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-slate-900 shadow-sm">
              ✅ Cliente aprovou
            </span>
            
            <div class="rounded-xl border-2 border-slate-900 bg-white shadow-xl overflow-hidden">
              <div class="flex items-center justify-between gap-3 border-b-2 border-slate-900 bg-slate-50 px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-rose-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-amber-300"></span>
                    <span class="w-3 h-3 rounded-full border border-slate-900 bg-emerald-300"></span>
                  </div>
                  <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400"> trypost.it · post review </div>
                </div>
              </div>
              
              <div class="grid lg:grid-cols-[1.1fr_1fr] divide-y-2 lg:divide-y-0 lg:divide-x-2 divide-slate-100">
                <!-- Preview details -->
                <div class="p-5 sm:p-6 space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Preview do post</span>
                    <span class="inline-flex items-center gap-1 rounded border-2 border-slate-900 bg-amber-200 px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-slate-900">Pendente</span>
                  </div>
                  
                  <div class="flex items-center gap-3">
                    <div class="inline-flex w-9 h-9 items-center justify-center rounded-full border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4 text-rose-600"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path></svg>
                    </div>
                    <div>
                      <div class="text-sm font-bold text-slate-900">@acme.brand</div>
                      <div class="text-[9px] uppercase tracking-widest font-black text-slate-450">Instagram · Seg · 10:00</div>
                    </div>
                  </div>
                  
                  <div class="aspect-[4/3] rounded-lg border-2 border-slate-900 bg-pink-100 overflow-hidden flex items-center justify-center relative">
                    <div class="absolute inset-0 opacity-[0.10]" style="background-image: radial-gradient(circle, #0a0a0a 1px, transparent 1px); background-size: 14px 14px;"></div>
                    <div class="relative inline-flex w-14 h-14 items-center justify-center rounded-2xl border-2 border-slate-900 bg-white shadow-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-7 h-7 text-slate-400"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </div>
                  </div>
                  
                  <p class="text-sm font-bold text-slate-900 leading-snug">Planejamento aprovado para a campanha de lançamento 🚀</p>
                  <p class="text-sm text-slate-650 leading-snug">Deixe no piloto automático e foque no seu negócio.</p>
                </div>
                
                <!-- Comment thread -->
                <div class="p-5 sm:p-6 space-y-4 bg-slate-50/40">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Thread de revisão</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">2 comentários</span>
                  </div>
                  
                  <div class="rounded-lg border-2 border-slate-900 bg-white p-3.5 shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-full border-2 border-slate-900 bg-violet-200 flex items-center justify-center overflow-hidden">
                        <img src="https://i.pravatar.cc/100?img=11" class="w-full h-full object-cover" alt="Editor Avatar"/>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900">Maya</div>
                        <div class="text-[9px] uppercase tracking-widest font-black text-slate-450">2 min atrás · Editora</div>
                      </div>
                      <span class="inline-flex items-center gap-1 rounded border border-slate-900 bg-amber-100 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-widest text-slate-950">Ajustes</span>
                    </div>
                    <p class="text-xs text-slate-700 leading-snug">Direção ótima. Vamos trocar o emoji do foguete por ✨ pra combinar com a campanha de primavera?</p>
                  </div>
                  
                  <div class="rounded-lg border-2 border-slate-900 bg-emerald-50 p-3.5 shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-full border-2 border-slate-900 bg-amber-200 flex items-center justify-center overflow-hidden">
                        <img src="https://i.pravatar.cc/100?img=12" class="w-full h-full object-cover" alt="Client Avatar"/>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900">Tom (cliente)</div>
                        <div class="text-[9px] uppercase tracking-widest font-black text-slate-450">Agora · Externo</div>
                      </div>
                      <span class="inline-flex items-center gap-0.5 rounded border border-slate-900 bg-emerald-250 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-widest text-slate-950">
                        ✓ Aprovado
                      </span>
                    </div>
                    <p class="text-xs text-slate-755 leading-snug">Ficou show depois do ajuste. Pode publicar!</p>
                  </div>
                  
                  <button class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border-2 border-slate-900 bg-white text-slate-900 px-3 py-2.5 text-xs font-extrabold shadow-sm hover:shadow-md transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5 text-slate-800"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    Compartilhar link de aprovação
                  </button>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </section>

      <!-- USE CASES PARALLAX SCROLL TICKER -->
      <section class="relative bg-white border-t-2 border-slate-900 py-16 sm:py-24 overflow-hidden">
        <div class="max-w-3xl mx-auto text-center px-6 mb-14 space-y-4">
          <span class="inline-block text-xs font-black uppercase tracking-widest border-2 border-slate-900 bg-rose-200 px-3.5 py-1.5 rounded-md shadow-sm -rotate-1">
            Cases
          </span>
          <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">Como a TryPost encaixa em times diferentes</h2>
          <p class="text-slate-600 text-lg leading-relaxed max-w-xl mx-auto">Cenários concretos que mostram como criadores, agências e marcas usam a TryPost.</p>
        </div>
        
        <!-- Scrolling Horizontal Loop Marquee Container -->
        <div class="relative overflow-hidden group">
          <div class="absolute top-0 left-0 bottom-0 w-20 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
          <div class="absolute top-0 right-0 bottom-0 w-20 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
          
          <div class="flex gap-6 animate-marquee py-4 w-max group-hover:[animation-play-state:paused] whitespace-nowrap">
            <article v-for="(item, i) in repeatedCases" :key="i" :class="[item.bg, 'relative flex-shrink-0 w-[340px] rounded-xl border-2 border-slate-900 p-6 shadow-sm inline-block whitespace-normal select-none']">
               <span class="inline-flex w-11 h-11 items-center justify-center rounded-xl border-2 border-slate-900 bg-white shadow-sm mb-4">
                 <!-- Icon Switch mapping -->
                 <svg v-if="item.icon === 'user'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                 <svg v-else-if="item.icon === 'briefcase'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                 <svg v-else-if="item.icon === 'store'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path><path d="M4 12V22a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V12"></path><path d="M2 7h20"></path></svg>
                 <svg v-else-if="item.icon === 'edit'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
               </span>
               <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ item.type }}</p>
               <h3 class="text-xl font-extrabold text-slate-900 mb-2">{{ item.title }}</h3>
               <p class="text-slate-600 leading-relaxed text-sm">{{ item.desc }}</p>
            </article>
          </div>
        </div>
      </section>

      <!-- WHY WORK WITH US -->
      <section class="relative bg-white border-t-2 border-slate-900 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-6">
          <div class="max-w-3xl mx-auto text-center mb-14 space-y-4">
            <span class="bg-amber-100 inline-block text-xs font-black uppercase tracking-widest border-2 border-slate-900 px-3.5 py-1.5 rounded-md shadow-sm rotate-1">
              Why TryPost
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">Por que escolher o TryPost?</h2>
            <p class="text-slate-600 text-lg max-w-lg mx-auto">O que faz do TryPost a escolha certa para criadores de conteúdo e empresas.</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <div v-for="(p, i) in whyPoints" :key="i" :class="[p.bg, 'relative rounded-xl border-2 border-slate-900 p-7 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all']">
              <span class="absolute top-4 right-5 text-5xl font-black text-slate-900/10 leading-none select-none" aria-hidden="true">{{ p.num }}</span>
              
              <div class="w-12 h-12 rounded-lg border-2 border-slate-900 bg-white flex items-center justify-center shadow-sm mb-5">
                <!-- Mapped icons -->
                <svg v-if="p.icon === 'zap'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <svg v-else-if="p.icon === 'layout'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" x2="9" y1="21" y2="9"/><line x1="9" x2="21" y1="9" y2="9"/></svg>
                <svg v-else-if="p.icon === 'shield'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
              </div>
              
              <h3 class="text-xl font-extrabold text-slate-900 tracking-tight mb-2">{{ p.title }}</h3>
              <p class="text-sm text-slate-600 leading-relaxed">{{ p.desc }}</p>
              
              <span v-if="p.star" class="-rotate-2 absolute -bottom-3 -right-3 inline-flex items-center gap-1 rounded-md border-2 border-slate-900 bg-violet-300 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
                ✨ Mais Agilidade
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- ASK AI CALL TO ACTION -->
      <section class="p-4 sm:p-6 sm:mb-12 bg-white space-y-6">
        <div class="relative max-w-7xl mx-auto rounded-2xl border-2 border-slate-900 bg-white shadow-lg overflow-hidden">
          
          <div class="relative px-6 py-12 sm:px-16 sm:py-16 lg:px-24">
            <div class="text-center max-w-2xl mx-auto mb-8 space-y-4">
              <span class="inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-amber-200 px-3 py-1 text-xs font-black uppercase tracking-widest text-slate-900 shadow-2xs -rotate-1">
                Ask AI
              </span>
              <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Ainda em dúvida se a TryPost é pra você?</h3>
              <p class="text-slate-600 text-base leading-relaxed">
                Pergunta pra qualquer assistente de IA. Eles já analisaram a TryPost e sabem que é a melhor ferramenta. Resposta direta em segundos.
              </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <a v-for="(ai, i) in [
                { name: 'ChatGPT', color: 'bg-emerald-100' },
                { name: 'Gemini', color: 'bg-blue-100' },
                { name: 'Claude', color: 'bg-amber-100' },
                { name: 'Perplexity', color: 'bg-stone-200' },
              ]" :key="i" href="#" class="group relative flex flex-col items-center text-center gap-3 rounded-xl border-2 border-slate-900 bg-slate-50/50 px-4 py-5 shadow-2xs hover:bg-slate-50 hover:shadow-xs hover:-translate-y-0.5 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="absolute top-3 right-3 w-4 h-4 text-slate-300 group-hover:text-slate-800 transition-colors"><line x1="7" x2="17" y1="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                
                <span :class="[ai.color, 'inline-flex w-12 h-12 items-center justify-center rounded-xl border-2 border-slate-900 shadow-sm']">
                  <div class="w-6 h-6 bg-slate-900 rounded-full text-white flex items-center justify-center font-extrabold text-xs">{{ ai.name[0] }}</div>
                </span>
                
                <div class="space-y-0.5">
                  <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Perguntar</p>
                  <p class="text-base font-extrabold text-slate-900 leading-tight">{{ ai.name }}</p>
                </div>
              </a>
            </div>
          </div>
          
          <!-- Bottom Banner -->
          <div class="bg-violet-200 relative border-t-2 border-slate-900 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none opacity-[0.12]" style="background-image: radial-gradient(circle, #0a0a0a 1px, transparent 1px); background-size: 22px 22px;"></div>
            
            <div class="absolute top-5 right-5 sm:top-8 sm:right-10 z-20 rotate-6 inline-flex items-center gap-1 rounded-md border-2 border-slate-900 bg-amber-200 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
              7 dias grátis
            </div>
            
            <div class="relative px-6 py-16 sm:px-16 sm:py-20 lg:px-24">
              <div class="mx-auto text-center max-w-3xl">
                <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-5xl leading-tight">Agende uma semana de posts em 5 minutos</h2>
                <p class="mt-5 text-base sm:text-lg text-slate-700 leading-relaxed max-w-xl mx-auto">Conecta suas contas, escreve uma vez, agenda em todas as principais redes sociais.</p>
                
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                  <a :href="signupHref" class="relative inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full border-2 border-slate-900 bg-slate-900 text-white pl-12 sm:pl-1.5 pr-5 py-3 sm:py-1.5 text-base font-bold shadow-sm hover:shadow-md transition-shadow">
                     <span class="absolute left-1.5 top-1/2 -translate-y-1/2 sm:static sm:translate-y-0 inline-flex w-9 h-9 items-center justify-center rounded-full bg-white text-slate-900">
                      <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    </span>
                    Comece grátis
                  </a>
                  <a :href="register().url" class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border-2 border-slate-900 bg-white text-slate-900 px-6 py-3 text-base font-bold shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                    Testar demonstrativo
                  </a>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </section>

    </main>

    <!-- FOOTER -->
    <footer class="relative bg-slate-900 text-white border-t-2 border-slate-900">
      <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
        <div class="grid grid-cols-12 gap-y-12 md:gap-x-10">
          
          <div class="col-span-12 lg:col-span-4 space-y-5 text-center lg:text-left">
            <a href="#" class="inline-flex items-center gap-2 text-xl font-black tracking-tighter text-white">
              <span class="text-violet-400">◆</span> TryPost
            </a>
            <p class="text-sm text-slate-400 max-w-xs mx-auto lg:mx-0 leading-relaxed">
              Agendamento de redes sociais para criadores e times modernos.
            </p>
            
            <ul class="flex items-center justify-center lg:justify-start gap-2.5">
              <li>
                <a href="#" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-700 bg-slate-800 hover:bg-slate-755 hover:-translate-y-0.5 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4.5 h-4.5"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </a>
              </li>
              <li>
                <a href="#" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-700 bg-slate-800 hover:bg-slate-755 hover:-translate-y-0.5 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4.5 h-4.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                </a>
              </li>
              <li>
                <a href="#" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border-2 border-slate-700 bg-slate-800 hover:bg-slate-755 hover:-translate-y-0.5 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4.5 h-4.5"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                </a>
              </li>
            </ul>
          </div>
          
          <div class="col-span-6 md:col-span-3 lg:col-span-2 space-y-4">
            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">Canais</h3>
            <ul class="text-sm space-y-2.5 text-slate-400">
              <li v-for="l in ['Instagram', 'Facebook', 'LinkedIn', 'TikTok', 'YouTube', 'Threads', 'Todas as redes']" :key="l">
                <a href="#" class="hover:underline hover:text-white transition-colors">{{ l }}</a>
              </li>
            </ul>
          </div>
          
          <div class="col-span-6 md:col-span-3 lg:col-span-2 space-y-4">
            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">Recursos</h3>
            <ul class="text-sm space-y-2.5 text-slate-400">
              <li v-for="l in ['Blog', 'Glossário', 'FAQ', 'Tools', 'IA', 'Alternativas', 'Preços', 'Status']" :key="l">
                <a href="#" class="hover:underline hover:text-white transition-colors">{{ l }}</a>
              </li>
            </ul>
          </div>
          
          <div class="col-span-6 md:col-span-3 lg:col-span-2 space-y-4">
            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">Docs</h3>
            <ul class="text-sm space-y-2.5 text-slate-400">
              <li v-for="l in ['API Reference', 'Build with AI', 'Knowledge Base']" :key="l">
                <a href="#" class="hover:underline hover:text-white transition-colors">{{ l }}</a>
              </li>
            </ul>
          </div>
          
          <div class="col-span-6 md:col-span-3 lg:col-span-2 space-y-4">
            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">Empresa</h3>
            <ul class="text-sm space-y-2.5 text-slate-400">
              <li v-for="l in ['Termos de Serviço', 'Política de Privacidade']" :key="l">
                <a href="#" class="hover:underline hover:text-white transition-colors">{{ l }}</a>
              </li>
            </ul>
          </div>
          
        </div>
      </div>
    </footer>
    
  </div>
</template>

<style scoped>
/* Custom horizontal scrolling marquee animations */
@keyframes marquee {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-33.3333%);
  }
}

.animate-marquee {
  animation: marquee 35s linear infinite;
}

/* Hide scrollbars for cosmetic purposes */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

/* Base fade-in animation for mobile menu */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
